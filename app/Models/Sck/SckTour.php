<?php

namespace App\Models\Sck;

use App\Services\Sck\RouteGeometryCodec;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SckTour extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = [
        'tour_date' => 'date', 'start_snapshot' => 'array', 'end_snapshot' => 'array',
        'route_optimized' => 'boolean', 'pricing_snapshot' => 'array', 'route_warnings' => 'array',
        'planned_km' => 'decimal:2', 'travel_fee_pool' => 'decimal:2', 'internal_travel_cost' => 'decimal:2',
    ];
    public function stops() { return $this->hasMany(SckTourStop::class, 'tour_id')->orderBy('position'); }
    public function weeklyPlan() { return $this->belongsTo(SckWeeklyPlan::class, 'weekly_plan_id'); }

    public function routePoints(): array { return app(RouteGeometryCodec::class)->decode($this->encoded_polyline); }

    public function getSalesTotalAttribute(): float { return (float) $this->stops->sum(fn ($s) => $s->items->sum(fn ($i) => $i->actual_net_price * $i->quantity)); }
    public function getEkTotalAttribute(): float { return (float) $this->stops->sum(fn ($s) => $s->items->sum(fn ($i) => $i->ek_snapshot * $i->quantity)); }
    public function getMarginAttribute(): float { return $this->sales_total + (float) $this->travel_fee_pool - $this->ek_total - (float) $this->internal_travel_cost; }
}
