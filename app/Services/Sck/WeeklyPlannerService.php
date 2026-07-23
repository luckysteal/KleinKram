<?php

namespace App\Services\Sck;

use App\Models\Sck\SckPlanCandidate;
use App\Models\Sck\SckRouteSetting;
use App\Models\Sck\SckWeeklyPlan;
use App\Models\Sck\SckWeeklyPlanStop;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeeklyPlannerService
{
    public function __construct(private TomTomService $tomTom, private RouteXlService $routeXl) {}

    public function generate(SckWeeklyPlan $plan): Collection
    {
        $plan->load('stops');
        $settings = SckRouteSetting::forUser($plan->user_id);
        $params = $this->parameters($plan);
        $points = [['lat' => (float)$settings->home_latitude, 'lng' => (float)$settings->home_longitude]];
        foreach ($plan->stops as $stop) $points[] = ['lat' => (float)$stop->latitude, 'lng' => (float)$stop->longitude];
        $matrix = $this->tomTom->matrix($points);

        $definitions = [
            'efficient' => 'Effizienteste Fahrzeit',
            'balanced' => 'Ausgeglichen',
            'regions' => 'Klare Gebiete',
        ];
        $candidates = collect();
        foreach ($definitions as $strategy => $name) {
            $result = $this->buildCandidate($plan, $settings, $params, $matrix, $strategy);
            $candidates->push(SckPlanCandidate::updateOrCreate(
                ['weekly_plan_id' => $plan->id, 'strategy' => $strategy],
                ['name' => $name, 'score' => $result['score'], 'feasible' => empty($result['unassigned']), 'metrics' => $result['metrics'], 'tours' => $result['tours'], 'unassigned' => $result['unassigned']]
            ));
        }
        $plan->update(['status' => 'calculated']);
        return $candidates;
    }

    public function recalculateCandidate(SckPlanCandidate $candidate, string $mode): SckPlanCandidate
    {
        if ($mode === 'full') {
            $originalTours = collect($candidate->tours)->values();
            $regenerated = $this->generate($candidate->plan)->firstWhere('strategy', $candidate->strategy);
            $newTours = collect($regenerated->tours)->values();
            foreach ($originalTours as $tourIndex => $tour) {
                if (!empty($tour['slot']['locked'])) { $newTours[$tourIndex] = $tour; continue; }
                foreach (collect($tour['stops'] ?? [])->filter(fn ($stop) => !empty($stop['locked']) || !empty($stop['fixed_position'])) as $position => $lockedStop) {
                    $newTours = $newTours->map(function ($newTour) use ($lockedStop) { $newTour['stops'] = collect($newTour['stops'] ?? [])->reject(fn ($stop) => $stop['id'] === $lockedStop['id'])->values()->all(); return $newTour; });
                    $target = $newTours[$tourIndex]; $targetStops = $target['stops'] ?? [];
                    array_splice($targetStops, min((int)$position, count($targetStops)), 0, [$lockedStop]); $target['stops'] = $targetStops; $newTours[$tourIndex] = $target;
                }
            }
            $regenerated->update(['tours' => $newTours->all()]);
            return $this->recalculateCandidate($regenerated->fresh(), 'metrics');
        }
        $settings = SckRouteSetting::forUser($candidate->plan->user_id);
        $home = ['name' => $settings->home_name, 'address' => $settings->home_address, 'lat' => (float)$settings->home_latitude, 'lng' => (float)$settings->home_longitude];
        $tours = [];
        foreach ($candidate->tours as $source) {
            $routeXlResult = null;
            $stops = collect($source['stops'] ?? [])->values();
            if ($mode === 'order' && $stops->isNotEmpty()) {
                $routeStart = Carbon::parse($source['slot']['date'].' '.$source['slot']['start_time']);
                $locations = [['address' => 'home:start', 'lat' => $home['lat'], 'lng' => $home['lng']]];
                foreach ($stops as $stop) $locations[] = $this->routeLocation('stop:'.$stop['id'], $stop, $routeStart);
                $locations[] = ['address' => 'home:end', 'lat' => $home['lat'], 'lng' => $home['lng']];
                $routeXlResult = $this->routeXl->optimize($locations);
                if ($routeXlResult->successful()) {
                    $byId = $stops->keyBy('id');
                    $ordered = collect($routeXlResult->route)->map(function ($point) use ($byId) { preg_match('/stop:(\d+)/', $point['name'] ?? '', $m); return isset($m[1]) ? $byId->get((int)$m[1]) : null; })->filter()->values();
                    if ($ordered->count() === $stops->count()) $stops = collect($this->applyLockedPositions($ordered->all(), $stops->all()));
                }
            }
            $points = array_merge([$home], $stops->map(fn ($s) => ['lat' => $s['lat'], 'lng' => $s['lng']])->all(), [$home]);
            try { $route = $this->tomTom->route($points); } catch (\Throwable) { $route = ['km' => 0, 'minutes' => 0, 'points' => $points, 'fallback' => true]; }
            $service = (int)$stops->sum('service_minutes');
            $source['stops'] = $stops->all(); $source['km'] = round($route['km'], 2); $source['drive_minutes'] = $route['minutes'];
            $source['service_minutes'] = $service; $source['total_minutes'] = $service + $route['minutes']; $source['polyline'] = $route['points'];
            $source['fee'] = round(max((float)$settings->travel_minimum_fee, (float)$settings->travel_base_fee + $route['km']*(float)$settings->travel_per_km + $route['minutes']*(float)$settings->travel_per_minute), 2);
            $source['optimized'] = $mode === 'order' && $routeXlResult?->successful(); $source['provider'] = $source['optimized'] ? 'RouteXL + TomTom' : 'TomTom/manuell';
            $source['warnings'] = $source['optimized'] ? [] : [$routeXlResult?->warning() ?? 'Nicht durch RouteXL optimiert.'];
            $tours[] = $source;
        }
        $metrics = $this->aggregate($tours);
        $candidate->update(['tours' => $tours, 'metrics' => $metrics, 'score' => $metrics['total_minutes'] + $metrics['total_km'], 'feasible' => empty($candidate->unassigned) && collect($tours)->flatMap(fn ($tour) => $tour['warnings'] ?? [])->filter(fn ($warning) => str_contains($warning, 'überschritten'))->isEmpty()]);
        return $candidate->fresh();
    }

    private function buildCandidate(SckWeeklyPlan $plan, SckRouteSetting $settings, array $params, array $matrix, string $strategy): array
    {
        $slots = $this->slots($plan, $params);
        $home = ['name' => $settings->home_name, 'address' => $settings->home_address, 'lat' => (float)$settings->home_latitude, 'lng' => (float)$settings->home_longitude];
        $stops = $plan->stops->sortByDesc('priority')->values();
        if ($strategy === 'regions') {
            $stops = $stops->sortBy(fn ($s) => atan2((float)$s->longitude - $home['lng'], (float)$s->latitude - $home['lat']))->values();
        } elseif ($strategy === 'efficient') {
            $stops = $stops->sortBy(fn ($s) => $this->homeDistance($s, $plan->stops, $matrix))->values();
        }

        $assigned = array_fill(0, count($slots), []);
        $work = array_fill(0, count($slots), 0.0);
        $unassigned = [];
        foreach ($stops as $stop) {
            $eligible = collect(array_keys($slots))->filter(fn ($i) => $this->eligible($stop, $slots[$i], count($assigned[$i]), $work[$i], $params, $home));
            if ($stop->fixed_tour_index) $eligible = $eligible->filter(fn ($i) => $i === $stop->fixed_tour_index - 1);
            if ($eligible->isEmpty()) {
                $unassigned[] = ['id' => $stop->id, 'title' => $stop->title, 'reason' => $this->unassignedReason($stop, $slots, $assigned, $work, $params, $home)];
                continue;
            }
            $slotIndex = $eligible->sortBy(function ($i) use ($strategy, $stop, $assigned, $work, $slots, $home) {
                $directionPenalty = $this->directionPenalty($stop, $slots[$i], $home);
                if ($strategy === 'balanced') return $work[$i] + $directionPenalty * 10;
                if ($strategy === 'regions') return $directionPenalty * 1000 + count($assigned[$i]) * 10;
                $last = end($assigned[$i]) ?: null;
                return ($last ? $this->haversine($last, $stop) : $this->haversine($home, $stop)) + $directionPenalty * 5;
            })->first();
            $assigned[$slotIndex][] = $stop;
            $work[$slotIndex] += $stop->service_minutes + $this->haversine($home, $stop) / 55 * 60;
        }

        $tourResults = [];
        foreach ($slots as $i => $slot) {
            $tourResults[] = $this->routeTour($assigned[$i], $slot, $home, $settings);
        }
        $metrics = $this->aggregate($tourResults);
        $score = $strategy === 'balanced'
            ? $metrics['total_minutes'] + $metrics['workload_variance'] * 8
            : ($strategy === 'regions' ? $metrics['total_km'] + $metrics['direction_penalty'] * 40 : $metrics['total_minutes'] + $metrics['total_km']);
        $score += count($unassigned) * 100000;
        return compact('score', 'metrics', 'unassigned') + ['tours' => $tourResults];
    }

    private function routeTour(array $stops, array $slot, array $home, SckRouteSetting $settings): array
    {
        $ordered = array_values($stops);
        $locations = [['address' => 'home:start', 'lat' => $home['lat'], 'lng' => $home['lng']]];
        foreach ($ordered as $stop) {
            $locations[] = $this->routeLocation('stop:'.$stop->id, $stop, Carbon::parse($slot['date'].' '.$slot['start_time']));
        }
        $locations[] = ['address' => 'home:end', 'lat' => $home['lat'], 'lng' => $home['lng']];
        $routeXlResult = count($ordered) ? $this->routeXl->optimize($locations) : null;
        if ($routeXlResult?->successful()) {
            $byId = collect($ordered)->keyBy('id');
            $routeStops = collect($routeXlResult->route)->map(function ($point) use ($byId) {
                if (!preg_match('/stop:(\d+)/', $point['name'] ?? '', $match)) return null;
                $stop = $byId->get((int)$match[1]);
                return $stop ? $this->stopPayload($stop) + ['arrival_minutes' => $point['arrival'] ?? null, 'cumulative_km' => $point['distance'] ?? null] : null;
            })->filter()->values()->all();
            if (count($routeStops) === count($ordered)) $orderedPayload = $this->applyFixedPositions($routeStops);
            else $orderedPayload = collect($ordered)->map(fn ($s) => $this->stopPayload($s))->all();
        } else {
            $orderedPayload = collect($ordered)->map(fn ($s) => $this->stopPayload($s))->all();
        }
        $routePoints = array_merge([$home], collect($orderedPayload)->map(fn ($s) => ['lat' => $s['lat'], 'lng' => $s['lng']])->all(), [$home]);
        try { $route = $this->tomTom->route($routePoints); } catch (\Throwable) { $route = ['km' => 0, 'minutes' => 0, 'points' => $routePoints, 'fallback' => true]; }
        $service = (int)collect($orderedPayload)->sum('service_minutes');
        $fee = max((float)$settings->travel_minimum_fee, (float)$settings->travel_base_fee + $route['km'] * (float)$settings->travel_per_km + $route['minutes'] * (float)$settings->travel_per_minute);
        $warnings = $routeXlResult?->successful() ? [] : [$routeXlResult?->warning() ?? 'Nicht durch RouteXL optimiert.'];
        if ($route['minutes'] + $service > (int)$slot['max_minutes']) $warnings[] = 'Maximale Tourdauer überschritten.';
        if ($slot['max_km'] !== null && $route['km'] > (float)$slot['max_km']) $warnings[] = 'Maximale Tourdistanz überschritten.';
        return [
            'slot' => $slot, 'stops' => $orderedPayload, 'km' => round($route['km'], 2), 'drive_minutes' => $route['minutes'],
            'service_minutes' => $service, 'total_minutes' => $route['minutes'] + $service, 'fee' => round($fee, 2),
            'optimized' => $routeXlResult?->successful() ?? false, 'provider' => $routeXlResult?->successful() ? 'RouteXL + TomTom' : 'TomTom/manuell',
            'polyline' => $route['points'], 'warnings' => $warnings,
            'direction_penalty' => collect($ordered)->sum(fn ($s) => $this->directionPenalty($s, $slot, $home)),
        ];
    }

    private function parameters(SckWeeklyPlan $plan): array
    {
        return array_replace(['enabled_days' => [1,2,3,4,5], 'default_start' => '08:00', 'max_stops' => 8, 'max_minutes' => 600, 'max_km' => null, 'equal_share' => true, 'allow_multiple_per_day' => false, 'slots' => []], $plan->parameters ?? []);
    }

    private function slots(SckWeeklyPlan $plan, array $params): array
    {
        $days = collect($params['enabled_days'])->values();
        $slots = [];
        for ($i = 0; $i < $plan->tour_count; $i++) {
            $custom = $params['slots'][$i] ?? [];
            $weekday = (int)($custom['weekday'] ?? $days[$i % max(1, $days->count())] ?? 1);
            $date = $plan->week_start->copy()->addDays(max(0, $weekday - 1))->toDateString();
            $slots[] = ['index' => $i + 1, 'date' => $date, 'weekday' => $weekday, 'start_time' => $custom['start_time'] ?? $params['default_start'], 'direction' => $custom['direction'] ?? null, 'direction_hard' => (bool)($custom['direction_hard'] ?? false), 'locked' => (bool)($custom['locked'] ?? false), 'max_stops' => (int)($custom['max_stops'] ?? $params['max_stops']), 'max_minutes' => (int)($custom['max_minutes'] ?? $params['max_minutes']), 'max_km' => isset($custom['max_km']) && $custom['max_km'] !== '' ? (float)$custom['max_km'] : ($params['max_km'] !== null ? (float)$params['max_km'] : null)];
        }
        return $slots;
    }

    private function eligible(SckWeeklyPlanStop $stop, array $slot, int $count, float $currentWork, array $params, array $home): bool
    {
        if ($count >= $slot['max_stops']) return false;
        if ($stop->required_date && $stop->required_date->toDateString() !== $slot['date']) return false;
        if ($stop->allowed_weekdays && !in_array($slot['weekday'], array_map('intval', $stop->allowed_weekdays), true)) return false;
        if ($currentWork + $stop->service_minutes + $this->haversine($home, $stop) / 55 * 120 > $slot['max_minutes']) return false;
        if ($slot['max_km'] !== null && $this->haversine($home, $stop) * 2 > $slot['max_km']) return false;
        if ($slot['direction_hard'] && $this->directionPenalty($stop, $slot, $home) > .5) return false;
        return true;
    }

    private function unassignedReason(SckWeeklyPlanStop $stop, array $slots, array $assigned, array $work, array $params, array $home): string
    {
        if ($stop->fixed_tour_index && !isset($slots[$stop->fixed_tour_index - 1])) return 'Die fest zugewiesene Tour existiert nicht.';
        if ($stop->required_date && !collect($slots)->contains(fn ($slot) => $slot['date'] === $stop->required_date->toDateString())) return 'Am erforderlichen Datum existiert kein Tour-Slot.';
        if ($stop->allowed_weekdays && !collect($slots)->contains(fn ($slot) => in_array($slot['weekday'], array_map('intval', $stop->allowed_weekdays), true))) return 'An keinem erlaubten Wochentag existiert ein Tour-Slot.';
        if (collect($slots)->every(fn ($slot) => $slot['direction_hard'] && $this->directionPenalty($stop, $slot, $home) > .5)) return 'Der Stopp liegt außerhalb aller harten Richtungssektoren.';
        if (collect($slots)->every(fn ($slot, $i) => count($assigned[$i]) >= $slot['max_stops'])) return 'Alle Touren haben ihr Stopplimit erreicht.';
        return 'Kein Tour-Slot erfüllt Dauer-, Distanz- oder Festzuweisungsgrenzen.';
    }

    private function applyFixedPositions(array $stops): array
    {
        $fixed = collect($stops)->filter(fn ($stop) => !empty($stop['fixed_position']))->sortBy('fixed_position');
        $result = collect($stops)->reject(fn ($stop) => !empty($stop['fixed_position']))->values()->all();
        foreach ($fixed as $stop) array_splice($result, max(0, min(count($result), (int)$stop['fixed_position'] - 1)), 0, [$stop]);
        return array_values($result);
    }

    private function applyLockedPositions(array $ordered, array $original): array
    {
        $locked = collect($original)->filter(fn ($stop) => !empty($stop['locked']) || !empty($stop['fixed_position']));
        $result = collect($ordered)->reject(fn ($stop) => $locked->contains('id', $stop['id']))->values()->all();
        foreach ($locked as $position => $stop) array_splice($result, min((int)$position, count($result)), 0, [$stop]);
        return array_values($result);
    }

    private function stopPayload(SckWeeklyPlanStop $stop): array
    {
        return ['id' => $stop->id, 'template_id' => $stop->stop_template_id, 'customer_id' => $stop->customer_id, 'title' => $stop->title, 'address' => $stop->address, 'lat' => (float)$stop->latitude, 'lng' => (float)$stop->longitude, 'service_minutes' => $stop->service_minutes, 'window_start' => $stop->window_start, 'window_end' => $stop->window_end, 'priority' => $stop->priority, 'fixed_position' => $stop->fixed_position, 'notes' => $stop->notes];
    }

    /** RouteXL expects time-window limits as minutes after the tour starts. */
    private function routeLocation(string $address, array|SckWeeklyPlanStop $stop, Carbon $routeStart): array
    {
        $location = [
            'address' => $address,
            'lat' => (float) (is_array($stop) ? $stop['lat'] : $stop->latitude),
            'lng' => (float) (is_array($stop) ? $stop['lng'] : $stop->longitude),
            'servicetime' => (int) (is_array($stop) ? $stop['service_minutes'] : $stop->service_minutes),
        ];
        $windowStart = is_array($stop) ? ($stop['window_start'] ?? null) : $stop->window_start;
        $windowEnd = is_array($stop) ? ($stop['window_end'] ?? null) : $stop->window_end;
        $restrictions = [];
        if ($windowStart) $restrictions['ready'] = max(0, $routeStart->diffInMinutes(Carbon::parse($routeStart->toDateString().' '.$windowStart), false));
        if ($windowEnd) $restrictions['due'] = max(0, $routeStart->diffInMinutes(Carbon::parse($routeStart->toDateString().' '.$windowEnd), false));
        if ($restrictions) $location['restrictions'] = $restrictions;
        return $location;
    }

    private function aggregate(array $tours): array
    {
        $work = collect($tours)->pluck('total_minutes');
        $avg = $work->avg() ?: 0;
        return ['total_km' => round(collect($tours)->sum('km'), 2), 'total_minutes' => (int)collect($tours)->sum('total_minutes'), 'total_fee' => round(collect($tours)->sum('fee'), 2), 'workload_variance' => round($work->avg(fn ($v) => abs($v - $avg)) ?: 0, 2), 'direction_penalty' => round(collect($tours)->sum('direction_penalty'), 2)];
    }

    private function homeDistance(SckWeeklyPlanStop $stop, Collection $stops, array $matrix): float
    {
        $index = $stops->search(fn ($item) => $item->id === $stop->id);
        return $matrix[0][$index + 1]['km'] ?? 0;
    }

    private function directionPenalty($stop, array $slot, array $home): float
    {
        if (empty($slot['direction'])) return 0;
        $angles = ['N' => 0, 'NE' => 45, 'E' => 90, 'SE' => 135, 'S' => 180, 'SW' => 225, 'W' => 270, 'NW' => 315];
        $wanted = $angles[$slot['direction']] ?? 0;
        $bearing = fmod(rad2deg(atan2((float)$stop->longitude - $home['lng'], (float)$stop->latitude - $home['lat'])) + 360, 360);
        $diff = abs($bearing - $wanted); $diff = min($diff, 360 - $diff);
        return $diff / 180;
    }

    private function haversine($from, $to): float
    {
        $aLat = (float)(is_array($from) ? $from['lat'] : $from->latitude); $aLng = (float)(is_array($from) ? $from['lng'] : $from->longitude);
        $bLat = (float)(is_array($to) ? $to['lat'] : $to->latitude); $bLng = (float)(is_array($to) ? $to['lng'] : $to->longitude);
        $lat = deg2rad($bLat - $aLat); $lng = deg2rad($bLng - $aLng);
        $x = sin($lat/2)**2 + cos(deg2rad($aLat))*cos(deg2rad($bLat))*sin($lng/2)**2;
        return 6371 * 2 * atan2(sqrt($x), sqrt(max(0, 1-$x)));
    }
}
