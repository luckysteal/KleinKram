<?php

namespace Tests\Unit;

use App\Services\Sck\RouteXlService;
use App\Services\Sck\RouteXlOptimizationResult;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RouteXlServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_it_uses_basic_auth_and_form_encoding_for_the_supported_api(): void
    {
        config([
            'services.routexl.username' => 'route-user',
            'services.routexl.password' => 'route-password',
            'services.routexl.base_url' => 'https://api.routexl.test',
        ]);
        Http::fake([
            'https://api.routexl.test/status/sck' => Http::response(['max_locations' => 10]),
            'https://api.routexl.test/tour/' => Http::response([
                'feasible' => true,
                'route' => [
                    ['name' => 'Start', 'arrival' => 0, 'distance' => 0],
                    ['name' => 'End', 'arrival' => 15, 'distance' => 12.5],
                ],
            ]),
        ]);

        $locations = [
            ['address' => 'Start', 'lat' => 50.7, 'lng' => 7.1],
            ['address' => 'End', 'lat' => 50.8, 'lng' => 7.2],
        ];
        $result = (new RouteXlService())->optimize($locations);

        $this->assertTrue($result->successful());
        $this->assertSame('End', $result->route[1]['name']);
        Http::assertSent(function (Request $request) use ($locations) {
            if ($request->url() !== 'https://api.routexl.test/tour/') {
                return false;
            }

            return $request->hasHeader('Authorization', 'Basic '.base64_encode('route-user:route-password'))
                && str_starts_with($request->header('Content-Type')[0] ?? '', 'application/x-www-form-urlencoded')
                && json_decode($request['locations'], true) === $locations;
        });
    }

    public function test_it_does_not_call_routexl_without_credentials(): void
    {
        config([
            'services.routexl.username' => null,
            'services.routexl.password' => null,
        ]);
        Http::fake();

        $result = (new RouteXlService())->optimize([
            ['address' => 'Start', 'lat' => 50.7, 'lng' => 7.1],
            ['address' => 'End', 'lat' => 50.8, 'lng' => 7.2],
        ]);

        $this->assertSame(RouteXlOptimizationResult::NOT_CONFIGURED, $result->status);
        $this->assertFalse($result->successful());
        Http::assertNothingSent();
    }

    public function test_provider_failures_fall_back_without_being_cached_as_an_account_limit(): void
    {
        config([
            'services.routexl.username' => 'route-user',
            'services.routexl.password' => 'route-password',
            'services.routexl.base_url' => 'https://api.routexl.test',
        ]);
        Http::fake([
            'https://api.routexl.test/status/sck' => Http::response([], 503),
        ]);

        $this->assertSame(0, (new RouteXlService())->maxLocations());
        $this->assertNull(Cache::get('sck:routexl:max'));
    }

    public function test_it_rejects_a_successful_but_malformed_response(): void
    {
        config([
            'services.routexl.username' => 'route-user',
            'services.routexl.password' => 'route-password',
            'services.routexl.base_url' => 'https://api.routexl.test',
        ]);
        Http::fake([
            'https://api.routexl.test/status/sck' => Http::response(['max_locations' => 10]),
            'https://api.routexl.test/tour/' => Http::response([
                'feasible' => true,
                'route' => [['unexpected' => 'shape'], ['name' => 'End']],
            ]),
        ]);

        $result = (new RouteXlService())->optimize([
            ['address' => 'Start', 'lat' => 50.7, 'lng' => 7.1],
            ['address' => 'End', 'lat' => 50.8, 'lng' => 7.2],
        ]);

        $this->assertSame(RouteXlOptimizationResult::INVALID_RESPONSE, $result->status);
        $this->assertSame('RouteXL hat eine unerwartete Antwort geliefert.', $result->warning());
    }
}
