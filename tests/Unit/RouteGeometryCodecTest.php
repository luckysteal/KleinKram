<?php

namespace Tests\Unit;

use App\Services\Sck\RouteGeometryCodec;
use PHPUnit\Framework\TestCase;

class RouteGeometryCodecTest extends TestCase
{
    public function test_it_uses_the_standard_precision_five_polyline_encoding(): void
    {
        $codec = new RouteGeometryCodec();
        $points = [
            ['lat' => 38.5, 'lng' => -120.2],
            ['lat' => 40.7, 'lng' => -120.95],
            ['lat' => 43.252, 'lng' => -126.453],
        ];

        $this->assertSame('p5:_p~iF~ps|U_ulLnnqC_mqNvxq`@', $codec->encode($points));
        $this->assertSame($points, $codec->decode($codec->encode($points)));
    }

    public function test_long_route_geometry_is_compact_and_round_trips_at_map_precision(): void
    {
        $codec = new RouteGeometryCodec();
        $points = [];
        for ($index = 0; $index < 5000; $index++) {
            $points[] = [
                'lat' => 50.643298 + $index * 0.00001,
                'lng' => 9.753112 + sin($index / 50) * 0.001,
            ];
        }

        $encoded = $codec->encode($points);
        $decoded = $codec->decode($encoded);

        $this->assertLessThan(strlen(json_encode($points)) * 0.2, strlen($encoded));
        $this->assertLessThan(65535, strlen($encoded));
        $this->assertCount(count($points), $decoded);
        $this->assertEqualsWithDelta($points[4321]['lat'], $decoded[4321]['lat'], 0.00001);
        $this->assertEqualsWithDelta($points[4321]['lng'], $decoded[4321]['lng'], 0.00001);
    }

    public function test_it_decodes_existing_json_geometry_for_backward_compatibility(): void
    {
        $codec = new RouteGeometryCodec();

        $this->assertSame(
            [['lat' => 50.73, 'lng' => 7.1], ['lat' => 50.74, 'lng' => 7.11]],
            $codec->decode('[{"lat":50.73,"lng":7.1},{"lat":50.74,"lng":7.11}]'),
        );
    }
}
