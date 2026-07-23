<?php

namespace App\Services\Sck;

use App\Models\Sck\SckPlanCandidate;
use App\Models\Sck\SckRouteSetting;
use App\Models\Sck\SckTour;
use Illuminate\Support\Facades\DB;

class TourMaterializerService
{
    public function __construct(private TomTomService $tomTom, private RouteGeometryCodec $geometryCodec) {}

    public function materialize(SckPlanCandidate $candidate): array
    {
        return DB::transaction(function () use ($candidate) {
            $plan = $candidate->plan;
            $settings = SckRouteSetting::forUser($plan->user_id);
            $created = [];
            foreach ($candidate->tours as $index => $source) {
                if (empty($source['stops'])) continue;
                $tour = SckTour::create([
                    'user_id' => $plan->user_id, 'weekly_plan_id' => $plan->id,
                    'number' => 'SCK-'.now()->format('Ymd').'-'.str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT).'-'.strtoupper(substr(sha1(uniqid()), 0, 4)),
                    'title' => $plan->name.' – Tour '.($index + 1), 'tour_date' => $source['slot']['date'], 'departure_time' => $source['slot']['start_time'],
                    'status' => 'planned', 'start_snapshot' => $settings->homeSnapshot(), 'end_snapshot' => $settings->homeSnapshot(),
                    'route_provider' => $source['provider'], 'route_optimized' => $source['optimized'], 'encoded_polyline' => $this->geometryCodec->encode($source['polyline']),
                    'planned_km' => $source['km'], 'planned_drive_minutes' => $source['drive_minutes'], 'planned_service_minutes' => $source['service_minutes'],
                    'travel_fee_pool' => $source['fee'], 'internal_travel_cost' => round($source['km']*(float)$settings->internal_per_km + $source['drive_minutes']*(float)$settings->internal_per_minute, 2),
                    'pricing_snapshot' => $settings->only(['travel_base_fee','travel_per_km','travel_per_minute','travel_minimum_fee','internal_per_km','internal_per_minute']),
                    'route_warnings' => $source['warnings'],
                ]);
                foreach ($source['stops'] as $position => $stop) {
                    $tour->stops()->create([
                        'stop_template_id' => $stop['template_id'] ?? null, 'customer_id' => $stop['customer_id'] ?? null, 'position' => $position + 1,
                        'title' => $stop['title'], 'address_snapshot' => ['formatted' => $stop['address']], 'latitude' => $stop['lat'], 'longitude' => $stop['lng'],
                        'service_minutes' => $stop['service_minutes'], 'window_start' => $stop['window_start'], 'window_end' => $stop['window_end'],
                        'priority' => $stop['priority'], 'position_locked' => !empty($stop['fixed_position']), 'arrival_minutes' => $stop['arrival_minutes'] ?? null,
                        'cumulative_km' => $stop['cumulative_km'] ?? null, 'notes' => $stop['notes'],
                    ]);
                }
                $this->allocateFees($tour);
                $created[] = $tour;
            }
            $plan->update(['selected_candidate_id' => $candidate->id, 'status' => 'materialized']);
            return $created;
        });
    }

    public function allocateFees(SckTour $tour): void
    {
        $customerStops = $tour->stops()->whereNotNull('customer_id')->get()->groupBy('customer_id');
        if ($customerStops->isEmpty()) return;
        $start = SckRouteSetting::forUser($tour->user_id)->homeSnapshot();
        $representatives = $customerStops->map->first()->values();
        $points = array_merge([['lat' => $start['lat'], 'lng' => $start['lng']]], $representatives->map(fn ($stop) => ['lat' => $stop->latitude, 'lng' => $stop->longitude])->all());
        $matrix = $this->tomTom->matrix($points);
        $weights = $customerStops->keys()->values()->mapWithKeys(fn ($customerId, $index) => [$customerId => max(0.1, (float)($matrix[0][$index + 1]['km'] ?? 0.1))]);
        $totalWeight = $weights->sum(); $remaining = (float)$tour->travel_fee_pool;
        $tour->stops()->update(['allocated_travel_fee' => 0]);
        foreach ($customerStops as $customerId => $stops) {
            $share = $customerId === $customerStops->keys()->last() ? $remaining : round((float)$tour->travel_fee_pool * $weights[$customerId] / $totalWeight, 2);
            $stops->first()->update(['allocated_travel_fee' => $share]);
            $remaining -= $share;
        }
    }
}
