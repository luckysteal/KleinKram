<?php

namespace App\Services\Sck;

final class RouteXlOptimizationResult
{
    public const OPTIMIZED = 'optimized';
    public const NOT_CONFIGURED = 'not_configured';
    public const INVALID_LOCATIONS = 'invalid_locations';
    public const LIMIT_UNAVAILABLE = 'limit_unavailable';
    public const TOO_MANY_LOCATIONS = 'too_many_locations';
    public const AUTHENTICATION_FAILED = 'authentication_failed';
    public const RATE_LIMITED = 'rate_limited';
    public const INFEASIBLE = 'infeasible';
    public const INVALID_RESPONSE = 'invalid_response';
    public const PROVIDER_ERROR = 'provider_error';

    private function __construct(
        public readonly string $status,
        public readonly array $route = [],
        public readonly ?int $httpStatus = null,
        public readonly ?string $detail = null,
    ) {}

    public static function optimized(array $route): self
    {
        return new self(self::OPTIMIZED, array_values($route), 200);
    }

    public static function failed(string $status, ?int $httpStatus = null, ?string $detail = null): self
    {
        return new self($status, [], $httpStatus, $detail);
    }

    public function successful(): bool
    {
        return $this->status === self::OPTIMIZED;
    }

    public function warning(): ?string
    {
        return match ($this->status) {
            self::OPTIMIZED => null,
            self::NOT_CONFIGURED => 'RouteXL ist nicht konfiguriert.',
            self::INVALID_LOCATIONS => 'RouteXL konnte wegen fehlender oder ungültiger Koordinaten nicht verwendet werden.',
            self::TOO_MANY_LOCATIONS => 'Die Tour enthält mehr Stopps als der RouteXL-Tarif erlaubt.',
            self::AUTHENTICATION_FAILED => 'Die RouteXL-Zugangsdaten wurden abgelehnt.',
            self::RATE_LIMITED => 'RouteXL verarbeitet bereits eine andere Tour. Die Reihenfolge wurde nicht optimiert.',
            self::INFEASIBLE => 'RouteXL konnte keine zulässige Tour für die Vorgaben ermitteln.',
            self::INVALID_RESPONSE => 'RouteXL hat eine unerwartete Antwort geliefert.',
            default => 'RouteXL war nicht erreichbar. Die Reihenfolge wurde nicht optimiert.',
        };
    }
}
