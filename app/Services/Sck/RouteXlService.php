<?php

namespace App\Services\Sck;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RouteXlService
{
    private string $baseUrl;
    private ?string $username;
    private ?string $password;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.routexl.base_url', 'https://api.routexl.com'), '/');
        $this->username = config('services.routexl.username');
        $this->password = config('services.routexl.password');
    }

    public function configured(): bool
    {
        return filled($this->username) && filled($this->password);
    }

    public function maxLocations(): int
    {
        return $this->maximumLocations()['maximum'];
    }

    public function optimize(array $locations, bool $skipOptimization = false): RouteXlOptimizationResult
    {
        if (!$this->configured()) {
            return RouteXlOptimizationResult::failed(RouteXlOptimizationResult::NOT_CONFIGURED);
        }

        if (count($locations) < 2 || !$this->validLocations($locations)) {
            return $this->failure(RouteXlOptimizationResult::INVALID_LOCATIONS, null, count($locations));
        }

        $limit = $this->maximumLocations();
        if ($limit['failure'] !== null) {
            return $limit['failure'];
        }
        if (count($locations) > $limit['maximum']) {
            return $this->failure(RouteXlOptimizationResult::TOO_MANY_LOCATIONS, 403, count($locations));
        }

        $key = 'sck:routexl:tour:v2:'.sha1(json_encode([$locations, $skipOptimization]));
        if (is_array($cached = Cache::get($key))) {
            return RouteXlOptimizationResult::optimized($cached);
        }

        try {
            return Cache::lock('sck:routexl:active', 90)->block(45, function () use ($locations, $skipOptimization, $key) {
                if (is_array($cached = Cache::get($key))) {
                    return RouteXlOptimizationResult::optimized($cached);
                }

                $payload = [
                    'locations' => json_encode(array_values($locations), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                ];
                if ($skipOptimization) {
                    $payload['skipOptimisation'] = 'true';
                }

                $response = Http::withBasicAuth($this->username, $this->password)
                    ->acceptJson()
                    ->asForm()
                    ->timeout(40)
                    ->retry(2, 600)
                    ->post("{$this->baseUrl}/tour/", $payload);

                if (!$response->successful()) {
                    return $this->responseFailure($response, count($locations));
                }
                if (!$response->json('feasible', false)) {
                    return $this->failure(
                        RouteXlOptimizationResult::INFEASIBLE,
                        $response->status(),
                        count($locations),
                        $this->safeDetail($response->json('remarks')),
                    );
                }

                $route = $response->json('route');
                if (!$this->validRoute($route, $locations)) {
                    return $this->failure(RouteXlOptimizationResult::INVALID_RESPONSE, $response->status(), count($locations));
                }

                $route = array_values($route);
                Cache::put($key, $route, now()->addDays(7));

                return RouteXlOptimizationResult::optimized($route);
            });
        } catch (Throwable $exception) {
            return $this->failure(
                RouteXlOptimizationResult::PROVIDER_ERROR,
                null,
                count($locations),
                $exception::class,
            );
        }
    }

    private function maximumLocations(): array
    {
        if (!$this->configured()) {
            return [
                'maximum' => 0,
                'failure' => RouteXlOptimizationResult::failed(RouteXlOptimizationResult::NOT_CONFIGURED),
            ];
        }

        if (($cached = Cache::get('sck:routexl:max')) !== null) {
            return ['maximum' => (int) $cached, 'failure' => null];
        }

        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->acceptJson()
                ->timeout(8)
                ->retry(2, 200)
                ->get("{$this->baseUrl}/status/sck");

            if (!$response->successful()) {
                return ['maximum' => 0, 'failure' => $this->responseFailure($response, 0)];
            }

            $maximum = (int) $response->json('max_locations', 0);
            if ($maximum < 1) {
                return [
                    'maximum' => 0,
                    'failure' => $this->failure(RouteXlOptimizationResult::INVALID_RESPONSE, $response->status(), 0),
                ];
            }

            Cache::put('sck:routexl:max', $maximum, now()->addHour());

            return ['maximum' => $maximum, 'failure' => null];
        } catch (Throwable $exception) {
            return [
                'maximum' => 0,
                'failure' => $this->failure(
                    RouteXlOptimizationResult::LIMIT_UNAVAILABLE,
                    null,
                    0,
                    $exception::class,
                ),
            ];
        }
    }

    private function responseFailure(Response $response, int $locationCount): RouteXlOptimizationResult
    {
        $status = match ($response->status()) {
            401 => RouteXlOptimizationResult::AUTHENTICATION_FAILED,
            403 => RouteXlOptimizationResult::TOO_MANY_LOCATIONS,
            429 => RouteXlOptimizationResult::RATE_LIMITED,
            default => RouteXlOptimizationResult::PROVIDER_ERROR,
        };

        return $this->failure($status, $response->status(), $locationCount);
    }

    private function failure(
        string $status,
        ?int $httpStatus,
        int $locationCount,
        ?string $detail = null,
    ): RouteXlOptimizationResult {
        Log::warning('RouteXL optimization unavailable.', array_filter([
            'status' => $status,
            'http_status' => $httpStatus,
            'location_count' => $locationCount,
            'detail' => $detail,
        ], fn ($value) => $value !== null));

        return RouteXlOptimizationResult::failed($status, $httpStatus, $detail);
    }

    private function validLocations(array $locations): bool
    {
        foreach ($locations as $location) {
            if (!is_array($location)
                || !isset($location['address'], $location['lat'], $location['lng'])
                || !is_string($location['address'])
                || $location['address'] === ''
                || !is_numeric($location['lat'])
                || !is_numeric($location['lng'])
                || (float) $location['lat'] < -90
                || (float) $location['lat'] > 90
                || (float) $location['lng'] < -180
                || (float) $location['lng'] > 180) {
                return false;
            }
        }

        return true;
    }

    private function validRoute(mixed $route, array $locations): bool
    {
        if (!is_array($route) || count($route) !== count($locations)) {
            return false;
        }

        $returnedNames = [];
        foreach ($route as $waypoint) {
            if (!is_array($waypoint)
                || !isset($waypoint['name'], $waypoint['arrival'], $waypoint['distance'])
                || !is_string($waypoint['name'])
                || !is_numeric($waypoint['arrival'])
                || !is_numeric($waypoint['distance'])
                || (float) $waypoint['arrival'] < 0
                || (float) $waypoint['distance'] < 0) {
                return false;
            }
            $returnedNames[] = $waypoint['name'];
        }

        $submittedNames = array_column($locations, 'address');
        sort($returnedNames);
        sort($submittedNames);

        return $returnedNames === $submittedNames;
    }

    private function safeDetail(mixed $detail): ?string
    {
        if (is_string($detail)) {
            return mb_substr($detail, 0, 500);
        }

        return null;
    }
}
