<?php

namespace App\Services\Sck;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TomTomService
{
    private ?string $key;
    private string $baseUrl;

    public function __construct()
    {
        $this->key = config('services.tomtom.key');
        $this->baseUrl = rtrim(config('services.tomtom.base_url', 'https://api.tomtom.com'), '/');
    }

    public function configured(): bool { return filled($this->key); }

    public function search(string $query, int $limit = 6): array
    {
        if (!$this->configured() || mb_strlen(trim($query)) < 2) return [];
        return Cache::remember('sck:tomtom:search:'.$limit.':'.sha1(mb_strtolower(trim($query))), now()->addDays(14), function () use ($query, $limit) {
            $response = Http::timeout(8)->retry(2, 200)->get("{$this->baseUrl}/search/2/search/".rawurlencode($query).'.json', [
                'key' => $this->key, 'typeahead' => 'true', 'limit' => $limit, 'countrySet' => 'DE', 'language' => 'de-DE',
            ]);
            if (!$response->successful()) return [];
            return collect($response->json('results', []))->map(fn ($item) => [
                'source' => 'tomtom',
                'id' => $item['id'] ?? null,
                'label' => $item['address']['freeformAddress'] ?? ($item['poi']['name'] ?? $query),
                'formatted_address' => $item['address']['freeformAddress'] ?? null,
                'street' => $item['address']['streetName'] ?? null,
                'house_number' => $item['address']['streetNumber'] ?? null,
                'postal_code' => $item['address']['postalCode'] ?? null,
                'city' => $item['address']['municipality'] ?? ($item['address']['localName'] ?? null),
                'country_code' => $item['address']['countryCode'] ?? 'DE',
                'lat' => $item['position']['lat'] ?? null,
                'lng' => $item['position']['lon'] ?? null,
            ])->filter(fn ($item) => $item['lat'] !== null)->values()->all();
        });
    }

    public function reverse(float $latitude, float $longitude): ?array
    {
        if (!$this->configured()) return null;
        $cacheKey = 'sck:tomtom:reverse:'.round($latitude, 6).':'.round($longitude, 6);
        return Cache::remember($cacheKey, now()->addDays(30), function () use ($latitude, $longitude) {
            $response = Http::timeout(8)->retry(2, 200)->get("{$this->baseUrl}/search/2/reverseGeocode/{$latitude},{$longitude}.json", [
                'key' => $this->key, 'language' => 'de-DE', 'returnSpeedLimit' => 'false',
            ]);
            if (!$response->successful()) return null;
            $item = $response->json('addresses.0');
            if (!$item) return null;
            $address = $item['address'] ?? [];
            $position = $item['position'] ?? [];
            if (is_string($position) && str_contains($position, ',')) {
                [$positionLatitude, $positionLongitude] = array_map('floatval', explode(',', $position, 2));
                $position = ['lat' => $positionLatitude, 'lon' => $positionLongitude];
            }
            return [
                'source' => 'tomtom', 'id' => null,
                'label' => $address['freeformAddress'] ?? "{$latitude}, {$longitude}",
                'formatted_address' => $address['freeformAddress'] ?? null,
                'street' => $address['streetName'] ?? null, 'house_number' => $address['streetNumber'] ?? null,
                'postal_code' => $address['postalCode'] ?? null,
                'city' => $address['municipality'] ?? ($address['localName'] ?? null),
                'country_code' => $address['countryCode'] ?? 'DE',
                'lat' => is_array($position) && isset($position['lat']) ? (float) $position['lat'] : $latitude,
                'lng' => is_array($position) && isset($position['lon']) ? (float) $position['lon'] : $longitude,
            ];
        });
    }

    public function matrix(array $points): array
    {
        if (count($points) < 2) return [];
        $cacheKey = 'sck:tomtom:matrix:'.sha1(json_encode($points));
        return Cache::remember($cacheKey, now()->addDays(30), function () use ($points) {
            if (!$this->configured()) return $this->fallbackMatrix($points);
            $locations = collect($points)->map(fn ($point) => ['point' => ['latitude' => (float)$point['lat'], 'longitude' => (float)$point['lng']]])->values()->all();
            $response = Http::timeout(25)->retry(2, 300)->post("{$this->baseUrl}/routing/matrix/2?key=".urlencode($this->key), [
                'origins' => $locations, 'destinations' => $locations,
                'options' => ['departAt' => 'any', 'routeType' => 'fastest', 'traffic' => 'historical', 'travelMode' => 'car'],
            ]);
            if (!$response->successful()) return $this->fallbackMatrix($points);
            $matrix = array_fill(0, count($points), array_fill(0, count($points), ['km' => 0.0, 'minutes' => 0]));
            foreach ($response->json('data', []) as $cell) {
                if (!isset($cell['routeSummary'])) continue;
                $matrix[$cell['originIndex']][$cell['destinationIndex']] = [
                    'km' => round(($cell['routeSummary']['lengthInMeters'] ?? 0) / 1000, 2),
                    'minutes' => (int)ceil(($cell['routeSummary']['travelTimeInSeconds'] ?? 0) / 60),
                ];
            }
            return $matrix;
        });
    }

    public function route(array $points, bool $preserveOrder = true): array
    {
        if (count($points) < 2) return ['km' => 0, 'minutes' => 0, 'points' => []];
        if (!$this->configured()) {
            $matrix = $this->fallbackMatrix($points);
            $km = $minutes = 0;
            for ($i = 0; $i < count($points) - 1; $i++) { $km += $matrix[$i][$i + 1]['km']; $minutes += $matrix[$i][$i + 1]['minutes']; }
            return ['km' => round($km, 2), 'minutes' => $minutes, 'points' => $points, 'fallback' => true];
        }
        $coords = collect($points)->map(fn ($p) => $p['lat'].','.$p['lng'])->implode(':');
        $response = Http::timeout(20)->retry(2, 300)->get("{$this->baseUrl}/routing/1/calculateRoute/{$coords}/json", [
            'key' => $this->key, 'routeType' => 'fastest', 'traffic' => 'true', 'travelMode' => 'car', 'computeBestOrder' => $preserveOrder ? 'false' : 'true',
        ]);
        if (!$response->successful()) throw new RuntimeException('TomTom-Routenberechnung fehlgeschlagen.');
        $route = $response->json('routes.0');
        return [
            'km' => round(($route['summary']['lengthInMeters'] ?? 0) / 1000, 2),
            'minutes' => (int)ceil(($route['summary']['travelTimeInSeconds'] ?? 0) / 60),
            'points' => collect($route['legs'] ?? [])->flatMap(fn ($leg) => collect($leg['points'] ?? [])->map(fn ($p) => ['lat' => $p['latitude'], 'lng' => $p['longitude']]))->values()->all(),
            'optimized_waypoints' => $response->json('optimizedWaypoints', []),
            'fallback' => false,
        ];
    }

    private function fallbackMatrix(array $points): array
    {
        $matrix = [];
        foreach ($points as $from) {
            $row = [];
            foreach ($points as $to) {
                $lat = deg2rad(((float)$to['lat']) - ((float)$from['lat']));
                $lng = deg2rad(((float)$to['lng']) - ((float)$from['lng']));
                $a = sin($lat / 2) ** 2 + cos(deg2rad((float)$from['lat'])) * cos(deg2rad((float)$to['lat'])) * sin($lng / 2) ** 2;
                $km = 6371 * 2 * atan2(sqrt($a), sqrt(max(0, 1 - $a))) * 1.25;
                $row[] = ['km' => round($km, 2), 'minutes' => (int)ceil($km / 55 * 60)];
            }
            $matrix[] = $row;
        }
        return $matrix;
    }
}
