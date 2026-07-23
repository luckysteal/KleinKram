<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckCustomer;
use App\Models\Sck\SckMapPoint;
use App\Models\Sck\SckRouteSetting;
use App\Models\Sck\SckTour;
use App\Services\Sck\TomTomService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SckMapController extends Controller
{
    private const DEFAULT_LAYERS = [
        'home' => true,
        'customers' => false,
        'points' => true,
        'tours' => true,
    ];

    public function index(Request $request)
    {
        return view('sck.map.index', [
            'initialMode' => in_array($request->query('mode'), ['next', 'week', 'tour'], true) ? $request->query('mode') : 'next',
            'initialWeek' => $this->weekStart((string) $request->query('week', now()->format('o-\\WW')))->format('o-\\WW'),
            'initialTourId' => $request->integer('tour_id') ?: null,
            'tomTomConfigured' => filled(config('services.tomtom.key')),
            'initialLayers' => array_merge(self::DEFAULT_LAYERS, $request->session()->get('sck_map_layers', [])),
            'initialLegendOpen' => $request->session()->get('sck_map_legend_open', true),
        ]);
    }

    public function updateLayers(Request $request)
    {
        $layers = $request->validate([
            'home' => ['required', 'boolean'],
            'customers' => ['required', 'boolean'],
            'points' => ['required', 'boolean'],
            'tours' => ['required', 'boolean'],
            'legend_open' => ['sometimes', 'boolean'],
        ]);

        $request->session()->put('sck_map_layers', collect($layers)->only(array_keys(self::DEFAULT_LAYERS))->all());

        if (array_key_exists('legend_open', $layers)) {
            $request->session()->put('sck_map_legend_open', $layers['legend_open']);
        }

        return response()->json([
            'layers' => collect($layers)->only(array_keys(self::DEFAULT_LAYERS))->all(),
            'legendOpen' => $request->session()->get('sck_map_legend_open', true),
        ]);
    }

    public function data(Request $request)
    {
        $validated = $request->validate([
            'mode' => ['nullable', Rule::in(['next', 'week', 'tour'])],
            'week' => ['nullable', 'regex:/^\\d{4}-W\\d{2}$/'],
            'tour_id' => ['nullable', 'integer'],
        ]);
        $mode = $validated['mode'] ?? 'next';
        $query = SckTour::with('stops')->where('user_id', $request->user()->id);

        if ($mode === 'week') {
            $start = $this->weekStart((string) ($validated['week'] ?? now()->format('o-\\WW')));
            $tours = $query->whereBetween('tour_date', [$start, $start->copy()->endOfWeek()])
                ->where('status', '!=', 'cancelled')->orderBy('tour_date')->orderBy('departure_time')->get();
        } elseif ($mode === 'tour') {
            $tourId = $validated['tour_id'] ?? null;
            $tours = $tourId ? collect([$query->findOrFail($tourId)]) : collect();
        } else {
            $running = (clone $query)->where('status', 'in_progress')->orderBy('tour_date')->orderBy('departure_time')->first();
            $next = $running ?: (clone $query)->whereIn('status', ['draft', 'planned'])
                ->whereDate('tour_date', '>=', today())->orderBy('tour_date')->orderBy('departure_time')->first();
            $tours = $next ? collect([$next]) : collect();
        }

        $settings = SckRouteSetting::where('user_id', $request->user()->id)->first();

        return response()->json([
            'home' => $settings && $settings->home_latitude !== null && $settings->home_longitude !== null ? [
                'name' => $settings->home_name, 'address' => $settings->home_address,
                'lat' => (float) $settings->home_latitude, 'lng' => (float) $settings->home_longitude,
            ] : null,
            'customers' => SckCustomer::whereNotNull('latitude')->whereNotNull('longitude')->orderBy('name')->get()->map(fn (SckCustomer $customer) => [
                'id' => $customer->id, 'name' => $customer->name, 'address' => $customer->full_address,
                'lat' => (float) $customer->latitude, 'lng' => (float) $customer->longitude,
                'url' => route('sck.kunden.show', $customer),
            ])->values(),
            'points' => SckMapPoint::with('creator:id,name')->orderBy('name')->get()->map(fn (SckMapPoint $point) => $this->pointPayload($point))->values(),
            'tours' => $tours->map(fn (SckTour $tour) => $this->tourPayload($tour))->values(),
            'selection' => ['mode' => $mode, 'count' => $tours->count()],
        ]);
    }

    public function searchTours(Request $request)
    {
        $query = trim((string) $request->validate(['q' => 'required|string|min:2|max:100'])['q']);
        $tours = SckTour::where('user_id', $request->user()->id)
            ->where(fn ($builder) => $builder->where('number', 'like', "%{$query}%")->orWhere('title', 'like', "%{$query}%"))
            ->orderByDesc('tour_date')->limit(10)->get();

        return response()->json(['results' => $tours->map(fn (SckTour $tour) => [
            'id' => $tour->id, 'number' => $tour->number, 'title' => $tour->title,
            'date' => $tour->tour_date?->format('d.m.Y'), 'status' => $tour->status,
        ])->values()]);
    }

    public function reverseGeocode(Request $request, TomTomService $tomTom)
    {
        $data = $request->validate(['lat' => 'required|numeric|between:-90,90', 'lng' => 'required|numeric|between:-180,180']);
        if (!$tomTom->configured()) {
            return response()->json(['message' => 'TomTom ist nicht konfiguriert.', 'result' => null], 503);
        }

        try {
            $result = $tomTom->reverse((float) $data['lat'], (float) $data['lng']);
        } catch (\Throwable) {
            return response()->json(['message' => 'Die Adresse konnte derzeit nicht ermittelt werden.', 'result' => null], 502);
        }

        return response()->json(['result' => $result]);
    }

    public function storePoint(Request $request)
    {
        $point = SckMapPoint::create($this->pointData($request) + ['created_by' => $request->user()->id]);

        return response()->json(['point' => $this->pointPayload($point->load('creator:id,name'))], 201);
    }

    public function updatePoint(Request $request, SckMapPoint $point)
    {
        $point->update($this->pointData($request));

        return response()->json(['point' => $this->pointPayload($point->fresh('creator:id,name'))]);
    }

    public function destroyPoint(SckMapPoint $point)
    {
        $point->delete();

        return response()->json(['success' => true]);
    }

    private function pointData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255', 'note' => 'nullable|string|max:5000',
            'formatted_address' => 'nullable|string|max:500', 'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:40', 'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255', 'country_code' => 'nullable|string|size:2',
            'latitude' => 'required|numeric|between:-90,90', 'longitude' => 'required|numeric|between:-180,180',
        ]);
    }

    private function pointPayload(SckMapPoint $point): array
    {
        return [
            'id' => $point->id, 'name' => $point->name, 'note' => $point->note,
            'address' => $point->formatted_address, 'formatted_address' => $point->formatted_address,
            'street' => $point->street, 'house_number' => $point->house_number,
            'postal_code' => $point->postal_code, 'city' => $point->city, 'country_code' => $point->country_code,
            'lat' => (float) $point->latitude, 'lng' => (float) $point->longitude,
            'creator' => $point->creator?->name,
        ];
    }

    private function tourPayload(SckTour $tour): array
    {
        $tour->loadMissing('stops');
        $start = $tour->start_snapshot ?: [];
        $end = $tour->end_snapshot ?: [];
        $polyline = $tour->routePoints();
        $approximate = count($polyline) < 2;
        if ($approximate) {
            $polyline = collect([$start])->concat($tour->stops->map(fn ($stop) => ['lat' => $stop->latitude, 'lng' => $stop->longitude]))->push($end)
                ->filter(fn ($point) => isset($point['lat'], $point['lng']))->values()->all();
        }

        return [
            'id' => $tour->id, 'number' => $tour->number, 'title' => $tour->title,
            'date' => $tour->tour_date?->format('d.m.Y'), 'status' => $tour->status,
            'provider' => $tour->route_provider, 'optimized' => (bool) $tour->route_optimized,
            'km' => (float) $tour->planned_km, 'minutes' => (int) $tour->planned_drive_minutes,
            'url' => route('sck.routen.show', $tour), 'start' => $start, 'end' => $end,
            'polyline' => $polyline, 'approximate' => $approximate,
            'stops' => $tour->stops->map(fn ($stop) => [
                'id' => $stop->id, 'position' => $stop->position, 'title' => $stop->title,
                'address' => $stop->address_snapshot['formatted'] ?? '',
                'lat' => $stop->latitude !== null ? (float) $stop->latitude : null,
                'lng' => $stop->longitude !== null ? (float) $stop->longitude : null,
            ])->values(),
        ];
    }

    private function weekStart(string $value): Carbon
    {
        if (preg_match('/^(\\d{4})-W(\\d{2})$/', $value, $match)) {
            return Carbon::now()->setISODate((int) $match[1], (int) $match[2])->startOfWeek();
        }

        throw ValidationException::withMessages(['week' => 'Bitte eine gültige ISO-Woche auswählen.']);
    }
}
