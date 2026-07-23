<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;

class SckRouteSetting extends Model
{
    protected $guarded = [];
    protected $casts = [
        'home_latitude' => 'float', 'home_longitude' => 'float',
        'travel_base_fee' => 'decimal:2', 'travel_per_km' => 'decimal:2',
        'travel_per_minute' => 'decimal:2', 'travel_minimum_fee' => 'decimal:2',
        'internal_per_km' => 'decimal:2', 'internal_per_minute' => 'decimal:2',
        'datev_verified' => 'boolean', 'datev_reminder_snoozed_until' => 'datetime',
    ];

    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId]);
    }

    public function homeSnapshot(): array
    {
        return ['name' => $this->home_name, 'address' => $this->home_address, 'lat' => $this->home_latitude, 'lng' => $this->home_longitude];
    }
}
