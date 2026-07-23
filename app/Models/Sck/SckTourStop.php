<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;

class SckTourStop extends Model
{
    protected $guarded = [];
    protected $casts = [
        'address_snapshot' => 'array', 'customer_snapshot' => 'array', 'position_locked' => 'boolean',
        'latitude' => 'float', 'longitude' => 'float', 'arrival_minutes' => 'integer', 'cumulative_km' => 'decimal:2',
    ];
    public function tour() { return $this->belongsTo(SckTour::class, 'tour_id'); }
    public function customer() { return $this->belongsTo(SckCustomer::class, 'customer_id')->withTrashed(); }
    public function items() { return $this->hasMany(SckTourStopItem::class, 'tour_stop_id'); }
    public function photos() { return $this->hasMany(SckStopPhoto::class, 'tour_stop_id')->latest(); }
    public function comments() { return $this->morphMany(SckComment::class, 'commentable')->latest(); }
}
