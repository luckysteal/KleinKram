<?php

namespace App\Services\Sck;

use InvalidArgumentException;
use JsonException;

class RouteGeometryCodec
{
    private const PREFIX = 'p5:';
    private const PRECISION = 5;

    public function encode(array $points): string
    {
        $factor = 10 ** self::PRECISION;
        $lastLatitude = 0;
        $lastLongitude = 0;
        $encoded = '';

        foreach ($points as $point) {
            if (!is_array($point)
                || !isset($point['lat'], $point['lng'])
                || !is_numeric($point['lat'])
                || !is_numeric($point['lng'])) {
                throw new InvalidArgumentException('Route points must contain numeric lat and lng values.');
            }

            $latitude = (int) round((float) $point['lat'] * $factor);
            $longitude = (int) round((float) $point['lng'] * $factor);
            $encoded .= $this->encodeValue($latitude - $lastLatitude);
            $encoded .= $this->encodeValue($longitude - $lastLongitude);
            $lastLatitude = $latitude;
            $lastLongitude = $longitude;
        }

        return self::PREFIX.$encoded;
    }

    public function decode(?string $geometry): array
    {
        if ($geometry === null || $geometry === '') {
            return [];
        }

        if (!str_starts_with($geometry, self::PREFIX)) {
            return $this->decodeLegacyJson($geometry);
        }

        $encoded = substr($geometry, strlen(self::PREFIX));
        $factor = 10 ** self::PRECISION;
        $latitude = 0;
        $longitude = 0;
        $index = 0;
        $points = [];
        $length = strlen($encoded);

        try {
            while ($index < $length) {
                $latitude += $this->decodeValue($encoded, $index);
                $longitude += $this->decodeValue($encoded, $index);
                $points[] = [
                    'lat' => $latitude / $factor,
                    'lng' => $longitude / $factor,
                ];
            }
        } catch (InvalidArgumentException) {
            return [];
        }

        return $points;
    }

    private function encodeValue(int $value): string
    {
        $value = $value < 0 ? ~($value << 1) : $value << 1;
        $encoded = '';

        while ($value >= 0x20) {
            $encoded .= chr((0x20 | ($value & 0x1f)) + 63);
            $value >>= 5;
        }

        return $encoded.chr($value + 63);
    }

    private function decodeValue(string $encoded, int &$index): int
    {
        $result = 0;
        $shift = 0;
        $length = strlen($encoded);

        do {
            if ($index >= $length) {
                throw new InvalidArgumentException('The encoded route geometry is truncated.');
            }

            $byte = ord($encoded[$index++]) - 63;
            $result |= ($byte & 0x1f) << $shift;
            $shift += 5;
        } while ($byte >= 0x20);

        return ($result & 1) ? ~($result >> 1) : $result >> 1;
    }

    private function decodeLegacyJson(string $geometry): array
    {
        try {
            $points = json_decode($geometry, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!is_array($points)) {
            return [];
        }

        return collect($points)->filter(fn ($point) => is_array($point)
            && isset($point['lat'], $point['lng'])
            && is_numeric($point['lat'])
            && is_numeric($point['lng']))
            ->map(fn ($point) => ['lat' => (float) $point['lat'], 'lng' => (float) $point['lng']])
            ->values()->all();
    }
}
