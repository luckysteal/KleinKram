<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckProcessedInvoice;
use App\Models\Sck\SckRouteSetting;
use App\Models\Sck\SckStopTemplate;
use App\Models\Sck\SckTour;
use App\Models\Sck\SckTourStop;
use App\Models\Sck\SckTourStopItem;
use App\Models\Sck\SckWarehouseItem;
use App\Models\Sck\SckWarehouseLog;
use App\Models\Sck\SckCustomer;
use App\Services\DatevInvoiceParserService;
use App\Services\Sck\RouteGeometryCodec;
use App\Services\Sck\RouteXlService;
use App\Services\Sck\TomTomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class SckTourController extends Controller
{
    public function index(Request $request)
    {
        $selectedWeek = $this->selectedWeek($request);
        $tours = SckTour::with(['weeklyPlan:id,name'])->withCount('stops')
            ->where('user_id', $request->user()->id)
            ->whereBetween('tour_date', [$selectedWeek, $selectedWeek->copy()->endOfWeek()])
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('number', 'like', "%{$s}%")->orWhere('title', 'like', "%{$s}%")))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('tour_date')->paginate(20)->withQueryString();
        return view('sck.routes.index', compact('tours', 'selectedWeek'));
    }

    public function create()
    {
        return view('sck.routes.create', [
            'templates' => SckStopTemplate::with('customer')->withCount('tourStops')->where('active', true)
                ->orderByDesc('tour_stops_count')->orderBy('title')->get(),
            'draftStops' => collect(session('sck.route_draft_stops', [])),
            'customers' => SckCustomer::orderBy('name')->get(),
            'items' => SckWarehouseItem::orderBy('bezeichnung')->get(),
        ]);
    }

    public function store(Request $request, TomTomService $tomTom, RouteXlService $routeXl, RouteGeometryCodec $geometryCodec)
    {
        $data = $request->validate(['title' => 'required|string|max:255', 'tour_date' => 'nullable|date', 'departure_time' => 'nullable|date_format:H:i', 'template_ids' => 'nullable|array', 'template_ids.*' => 'exists:sck_stop_templates,id', 'draft_stop_ids' => 'nullable|array', 'draft_stop_ids.*' => 'string', 'notes' => 'nullable|string|max:10000', 'start_address' => 'nullable|string|max:500', 'start_latitude' => 'nullable|required_with:start_address|numeric|between:-90,90', 'start_longitude' => 'nullable|required_with:start_address|numeric|between:-180,180', 'end_address' => 'nullable|string|max:500', 'end_latitude' => 'nullable|required_with:end_address|numeric|between:-90,90', 'end_longitude' => 'nullable|required_with:end_address|numeric|between:-180,180']);
        $settings = SckRouteSetting::forUser($request->user()->id);
        abort_if($settings->home_latitude === null || $settings->home_longitude === null, 422, 'Bitte zuerst die Home-Adresse konfigurieren.');
        $templates = SckStopTemplate::with('customer')->whereIn('id', $data['template_ids'] ?? [])->get();
        $drafts = collect(session('sck.route_draft_stops', []))->keyBy('id')->only($data['draft_stop_ids'] ?? [])->values();
        abort_if($templates->isEmpty() && $drafts->isEmpty(), 422, 'Bitte mindestens einen Stopp auswählen.');
        $stops = $templates->map(fn ($template) => ['template' => $template, 'title' => $template->title, 'customer_id' => $template->customer_id, 'customer' => $template->customer, 'address' => $template->full_address, 'lat' => $template->latitude, 'lng' => $template->longitude, 'service_minutes' => $template->service_minutes, 'window_start' => $template->window_start, 'window_end' => $template->window_end, 'priority' => $template->priority, 'type' => 'service', 'position_locked' => false, 'notes' => $template->notes])->concat($drafts->map(fn ($draft) => ['template' => null, 'title' => $draft['title'], 'customer_id' => $draft['customer_id'] ?? null, 'customer' => filled($draft['customer_id'] ?? null) ? SckCustomer::find($draft['customer_id']) : null, 'address' => $draft['address'], 'lat' => $draft['latitude'], 'lng' => $draft['longitude'], 'service_minutes' => $draft['service_minutes'], 'window_start' => $draft['window_start'] ?? null, 'window_end' => $draft['window_end'] ?? null, 'priority' => $draft['priority'] ?? 3, 'type' => $draft['type'] ?? 'service', 'position_locked' => $draft['position_locked'] ?? false, 'notes' => $draft['notes'] ?? null]))->values();
        $home = $settings->homeSnapshot();
        $start = filled($data['start_address'] ?? null) ? ['name' => 'Start', 'address' => $data['start_address'], 'lat' => (float)$data['start_latitude'], 'lng' => (float)$data['start_longitude']] : $home;
        $end = filled($data['end_address'] ?? null) ? ['name' => 'Ziel', 'address' => $data['end_address'], 'lat' => (float)$data['end_latitude'], 'lng' => (float)$data['end_longitude']] : $home;
        $routeStart = $this->routeStart($data['tour_date'] ?? null, $data['departure_time'] ?? null);
        $locations = [['address' => 'route:start', 'lat' => $start['lat'], 'lng' => $start['lng']]];
        foreach ($stops as $index => &$stop) {
            $stop['route_key'] = $stop['template']?->id ? (string) $stop['template']->id : 'draft-'.$index;
            $locations[] = $this->routeLocation('stop:'.$stop['route_key'], $stop, $routeStart);
        }
        unset($stop);
        $locations[] = ['address' => 'route:end', 'lat' => $end['lat'], 'lng' => $end['lng']];
        $routeXlResult = $routeXl->optimize($locations);
        $routeMetrics = [];
        if ($routeXlResult->successful()) {
            $byId = $stops->keyBy('route_key');
            $ordered = collect($routeXlResult->route)->map(function ($point) use ($byId, &$routeMetrics) {
                if (!preg_match('/^stop:(.+)$/', $point['name'], $matches)) return null;
                $key = $matches[1];
                $routeMetrics[$key] = ['arrival' => (int) $point['arrival'], 'distance' => (float) $point['distance']];
                return $byId->get($key);
            })->filter()->values();
            if ($ordered->count() === $stops->count()) $stops = $ordered;
        }
        $routePoints = array_merge([['lat' => $start['lat'], 'lng' => $start['lng']]], $stops->map(fn ($t) => ['lat' => $t['lat'], 'lng' => $t['lng']])->all(), [['lat' => $end['lat'], 'lng' => $end['lng']]]);
        try { $route = $tomTom->route($routePoints); } catch (\Throwable) { $route = ['km' => 0, 'minutes' => 0, 'points' => $routePoints, 'fallback' => true]; }
        $fee = max((float)$settings->travel_minimum_fee, (float)$settings->travel_base_fee + $route['km']*(float)$settings->travel_per_km + $route['minutes']*(float)$settings->travel_per_minute);
        $encodedGeometry = $geometryCodec->encode($route['points']);
        $tour = DB::transaction(function () use ($request, $data, $settings, $start, $end, $stops, $route, $fee, $routeXlResult, $routeMetrics, $encodedGeometry) {
            $tour = SckTour::create([
                'user_id' => $request->user()->id, 'number' => 'SCK-'.now()->format('Ymd-His').'-'.strtoupper(substr(sha1(uniqid()), 0, 4)),
                'title' => $data['title'], 'tour_date' => $data['tour_date'] ?? null, 'departure_time' => $data['departure_time'] ?? null,
                'status' => 'planned', 'start_snapshot' => $start, 'end_snapshot' => $end, 'route_provider' => $routeXlResult->successful() ? 'RouteXL + TomTom' : 'TomTom/manuell',
                'route_optimized' => $routeXlResult->successful(), 'encoded_polyline' => $encodedGeometry, 'planned_km' => $route['km'], 'planned_drive_minutes' => $route['minutes'],
                'planned_service_minutes' => $stops->sum('service_minutes'), 'travel_fee_pool' => round($fee, 2),
                'internal_travel_cost' => round($route['km']*(float)$settings->internal_per_km + $route['minutes']*(float)$settings->internal_per_minute, 2),
                'pricing_snapshot' => $settings->only(['travel_base_fee','travel_per_km','travel_per_minute','travel_minimum_fee','internal_per_km','internal_per_minute']),
                'route_warnings' => $routeXlResult->successful() ? [] : [$routeXlResult->warning()], 'notes' => $data['notes'] ?? null,
            ]);
            foreach ($stops as $position => $stop) {
                $template = $stop['template'];
                $tour->stops()->create(['stop_template_id' => $template?->id, 'customer_id' => $stop['customer_id'], 'position' => $position + 1, 'title' => $stop['title'],
                    'address_snapshot' => ['formatted' => $stop['address']], 'customer_snapshot' => $stop['customer']?->only(['id','name','phone','email']), 'latitude' => $stop['lat'], 'longitude' => $stop['lng'],
                'type' => $stop['type'], 'service_minutes' => $stop['service_minutes'], 'window_start' => $stop['window_start'], 'window_end' => $stop['window_end'], 'priority' => $stop['priority'], 'position_locked' => $stop['position_locked'],
                    'arrival_minutes' => $routeMetrics[$stop['route_key']]['arrival'] ?? null, 'cumulative_km' => $routeMetrics[$stop['route_key']]['distance'] ?? null, 'notes' => $stop['notes']]);
            }
            app(\App\Services\Sck\TourMaterializerService::class)->allocateFees($tour);
            return $tour;
        });
        $request->session()->forget('sck.route_draft_stops');
        return redirect()->route('sck.routen.show', $tour)->with('success', 'Tour wurde geplant.');
    }

    public function storeDraftStop(Request $request, TomTomService $tomTom)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255', 'address' => 'required|string|max:500', 'customer_id' => 'nullable|exists:sck_customers,id',
            'type' => 'required|in:service,delivery,pickup,inspection,other', 'service_minutes' => 'required|integer|between:0,1440',
            'priority' => 'required|integer|between:1,5', 'window_start' => 'nullable|date_format:H:i', 'window_end' => 'nullable|date_format:H:i|after:window_start',
            'contact_name' => 'nullable|string|max:255', 'contact_phone' => 'nullable|string|max:255',
            'access_notes' => 'nullable|string|max:5000', 'parking_notes' => 'nullable|string|max:5000', 'notes' => 'nullable|string|max:10000', 'position_locked' => 'nullable|boolean',
        ]);
        $customer = filled($data['customer_id'] ?? null) ? SckCustomer::find($data['customer_id']) : null;
        $data['title'] = filled($data['title'] ?? null) ? $data['title'] : ($customer?->name ?: $data['address']);
        $match = collect($tomTom->search($data['address'], 1))->first();
        if (!$match) return response()->json(['message' => 'Adresse konnte nicht gefunden werden.'], 422);
        $drafts = session('sck.route_draft_stops', []);
        $details = collect([
            'Kontakt' => trim(($data['contact_name'] ?? '').(($data['contact_name'] ?? null) && ($data['contact_phone'] ?? null) ? ' · ' : '').($data['contact_phone'] ?? '')),
            'Zugang' => $data['access_notes'] ?? null,
            'Parken' => $data['parking_notes'] ?? null,
        ])->filter()->map(fn ($value, $label) => $label.': '.$value)->implode("\n");
        $data['notes'] = trim(implode("\n\n", array_filter([$data['notes'] ?? null, $details])));
        $data['position_locked'] = $request->boolean('position_locked');
        $draft = $data + ['id' => (string) str()->uuid(), 'latitude' => $match['lat'], 'longitude' => $match['lng']];
        $drafts[] = $draft; session(['sck.route_draft_stops' => $drafts]);
        return response()->json(['stop' => $draft]);
    }

    public function updateDraftStop(Request $request, string $draftId, TomTomService $tomTom)
    {
        $drafts = collect(session('sck.route_draft_stops', []));
        $existing = $drafts->firstWhere('id', $draftId);
        abort_unless($existing, 404);

        $data = $request->validate([
            'title' => 'nullable|string|max:255', 'address' => 'required|string|max:500', 'customer_id' => 'nullable|exists:sck_customers,id',
            'type' => 'required|in:service,delivery,pickup,inspection,other', 'service_minutes' => 'required|integer|between:0,1440',
            'priority' => 'required|integer|between:1,5', 'window_start' => 'nullable|date_format:H:i', 'window_end' => 'nullable|date_format:H:i|after:window_start',
            'contact_name' => 'nullable|string|max:255', 'contact_phone' => 'nullable|string|max:255',
            'access_notes' => 'nullable|string|max:5000', 'parking_notes' => 'nullable|string|max:5000', 'notes' => 'nullable|string|max:10000', 'position_locked' => 'nullable|boolean',
        ]);
        $customer = filled($data['customer_id'] ?? null) ? SckCustomer::find($data['customer_id']) : null;
        $data['title'] = filled($data['title'] ?? null) ? $data['title'] : ($customer?->name ?: $data['address']);
        $match = collect($tomTom->search($data['address'], 1))->first();
        if (! $match) return response()->json(['message' => 'Adresse konnte nicht gefunden werden.'], 422);
        $details = collect([
            'Kontakt' => trim(($data['contact_name'] ?? '').(($data['contact_name'] ?? null) && ($data['contact_phone'] ?? null) ? ' · ' : '').($data['contact_phone'] ?? '')),
            'Zugang' => $data['access_notes'] ?? null, 'Parken' => $data['parking_notes'] ?? null,
        ])->filter()->map(fn ($value, $label) => $label.': '.$value)->implode("\n");
        $draft = $data + ['id' => $draftId, 'latitude' => $match['lat'], 'longitude' => $match['lng'], 'position_locked' => $request->boolean('position_locked')];
        $draft['notes'] = trim(implode("\n\n", array_filter([$data['notes'] ?? null, $details])));
        $drafts = $drafts->map(fn ($item) => $item['id'] === $draftId ? $draft : $item)->values()->all();
        session(['sck.route_draft_stops' => $drafts]);
        return response()->json(['stop' => $draft]);
    }

    public function saveStopAsTemplate(Request $request, SckTourStop $stop)
    {
        $data = $request->validate(['title' => 'required|string|max:255', 'include_customer' => 'nullable|boolean', 'include_duration' => 'nullable|boolean', 'include_notes' => 'nullable|boolean']);
        $address = $stop->address_snapshot ?: [];
        SckStopTemplate::create(['customer_id' => $request->boolean('include_customer') ? $stop->customer_id : null, 'title' => $data['title'], 'service_minutes' => $request->boolean('include_duration') ? $stop->service_minutes : 30, 'notes' => $request->boolean('include_notes') ? $stop->notes : null, 'street' => $address['street'] ?? null, 'house_number' => $address['house_number'] ?? null, 'postal_code' => $address['postal_code'] ?? null, 'city' => $address['city'] ?? null, 'country_code' => $address['country_code'] ?? 'DE', 'latitude' => $stop->latitude, 'longitude' => $stop->longitude, 'priority' => $stop->priority]);
        return back()->with('success', 'Stopp als Vorlage gespeichert.');
    }

    public function linkStopCustomer(Request $request, SckTourStop $stop) { $data = $request->validate(['customer_id' => 'nullable|exists:sck_customers,id']); $customer = filled($data['customer_id']) ? SckCustomer::find($data['customer_id']) : null; $stop->update(['customer_id' => $customer?->id, 'customer_snapshot' => $customer?->only(['id','name','phone','email'])]); return back()->with('success', 'Kundenzuordnung aktualisiert.'); }
    public function updateStop(Request $request, SckTourStop $stop, TomTomService $tomTom, RouteGeometryCodec $geometryCodec)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255', 'show_stop_address' => 'required|string|max:500',
            'customer_id' => 'nullable|exists:sck_customers,id', 'type' => 'required|in:service,delivery,pickup,inspection,other',
            'service_minutes' => 'required|integer|between:0,1440', 'priority' => 'required|integer|between:1,5',
            'window_start' => 'nullable|date_format:H:i', 'window_end' => 'nullable|date_format:H:i|after:window_start',
            'contact_name' => 'nullable|string|max:255', 'contact_phone' => 'nullable|string|max:255',
            'access_notes' => 'nullable|string|max:5000', 'parking_notes' => 'nullable|string|max:5000', 'notes' => 'nullable|string|max:10000',
        ]);

        $customer = filled($data['customer_id'] ?? null) ? SckCustomer::find($data['customer_id']) : null;
        $address = $data['show_stop_address'];
        $currentAddress = $stop->address_snapshot['formatted'] ?? '';
        $coordinates = ['latitude' => $stop->latitude, 'longitude' => $stop->longitude];
        if (trim($address) !== trim($currentAddress)) {
            $match = collect($tomTom->search($address, 1))->first();
            if (! $match) return back()->withErrors(['show_stop_address' => 'Die Adresse konnte nicht gefunden werden.'])->withInput();
            $coordinates = ['latitude' => $match['lat'], 'longitude' => $match['lng']];
        }
        $details = collect([
            'Kontakt' => trim(($data['contact_name'] ?? '').(($data['contact_name'] ?? null) && ($data['contact_phone'] ?? null) ? ' · ' : '').($data['contact_phone'] ?? '')),
            'Zugang' => $data['access_notes'] ?? null, 'Parken' => $data['parking_notes'] ?? null,
        ])->filter()->map(fn ($value, $label) => $label.': '.$value)->implode("\n");
        $notes = trim(implode("\n\n", array_filter([$data['notes'] ?? null, $details])));

        $stop->update([
            'customer_id' => $customer?->id, 'title' => filled($data['title'] ?? null) ? $data['title'] : ($customer?->name ?: $address),
            'address_snapshot' => ['formatted' => $address], 'customer_snapshot' => $customer?->only(['id', 'name', 'phone', 'email']),
            ...$coordinates, 'type' => $data['type'], 'service_minutes' => $data['service_minutes'], 'priority' => $data['priority'],
            'window_start' => $data['window_start'] ?? null, 'window_end' => $data['window_end'] ?? null, 'notes' => $notes ?: null,
        ]);
        $this->refreshTourRoute($stop->tour, $tomTom, $geometryCodec);

        return redirect()->route('sck.routen.show', $stop->tour)->with('success', 'Stopp wurde aktualisiert.');
    }
    public function removeStop(SckTourStop $stop) { $tour = $stop->tour; $stop->delete(); $tour->stops()->orderBy('position')->get()->each(fn ($s, $i) => $s->update(['position' => $i + 1])); return back()->with('success', 'Stopp wurde von der Tour entfernt.'); }
    public function removeItem(SckTourStopItem $item) { $item->delete(); return back()->with('success', 'Artikel/Leistung wurde entfernt.'); }

    public function show(Request $request, SckTour $tour)
    {
        $tour->load(['stops.customer', 'stops.items', 'stops.comments.user', 'stops.photos.comments.user']);
        $settings = SckRouteSetting::forUser($request->user()->id);
        $items = SckWarehouseItem::orderBy('bezeichnung')->get();
        $customers = SckCustomer::orderBy('name')->get(['id', 'name', 'street', 'house_number', 'postal_code', 'city', 'country_code', 'phone']);
        return view('sck.routes.show', compact('tour', 'settings', 'items', 'customers'));
    }

    public function status(Request $request, SckTour $tour)
    {
        $status = $request->validate(['status' => 'required|in:draft,planned,in_progress,completed,cancelled'])['status'];
        $tour->update(['status' => $status]);
        return back()->with('success', 'Tourstatus wurde geändert.');
    }

    public function addStop(Request $request, SckTour $tour, TomTomService $tomTom, RouteGeometryCodec $geometryCodec)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255', 'show_stop_address' => 'required|string|max:500',
            'customer_id' => 'nullable|exists:sck_customers,id', 'type' => 'required|in:service,delivery,pickup,inspection,other',
            'service_minutes' => 'required|integer|between:0,1440', 'priority' => 'required|integer|between:1,5',
            'window_start' => 'nullable|date_format:H:i', 'window_end' => 'nullable|date_format:H:i|after:window_start',
            'contact_name' => 'nullable|string|max:255', 'contact_phone' => 'nullable|string|max:255',
            'access_notes' => 'nullable|string|max:5000', 'parking_notes' => 'nullable|string|max:5000', 'notes' => 'nullable|string|max:10000',
        ]);

        $customer = filled($data['customer_id'] ?? null) ? SckCustomer::find($data['customer_id']) : null;
        $address = $data['show_stop_address'];
        $match = collect($tomTom->search($address, 1))->first();
        if (! $match) return back()->withErrors(['show_stop_address' => 'Die Adresse konnte nicht gefunden werden.'])->withInput();
        $details = collect([
            'Kontakt' => trim(($data['contact_name'] ?? '').(($data['contact_name'] ?? null) && ($data['contact_phone'] ?? null) ? ' · ' : '').($data['contact_phone'] ?? '')),
            'Zugang' => $data['access_notes'] ?? null, 'Parken' => $data['parking_notes'] ?? null,
        ])->filter()->map(fn ($value, $label) => $label.': '.$value)->implode("\n");
        $notes = trim(implode("\n\n", array_filter([$data['notes'] ?? null, $details])));

        $tour->stops()->create([
            'customer_id' => $customer?->id, 'position' => $tour->stops()->max('position') + 1,
            'title' => filled($data['title'] ?? null) ? $data['title'] : ($customer?->name ?: $address),
            'address_snapshot' => ['formatted' => $address], 'customer_snapshot' => $customer?->only(['id', 'name', 'phone', 'email']),
            'latitude' => $match['lat'], 'longitude' => $match['lng'], 'type' => $data['type'],
            'service_minutes' => $data['service_minutes'], 'priority' => $data['priority'],
            'window_start' => $data['window_start'] ?? null, 'window_end' => $data['window_end'] ?? null, 'notes' => $notes ?: null,
        ]);

        $this->refreshTourRoute($tour, $tomTom, $geometryCodec);

        return redirect()->route('sck.routen.show', $tour)->with('success', 'Weiterer Stopp wurde zur Tour hinzugefügt.');
    }

    public function reorder(Request $request, SckTour $tour, TomTomService $tomTom, RouteGeometryCodec $geometryCodec)
    {
        $ids = $request->validate(['stop_ids' => 'required|array', 'stop_ids.*' => 'integer'])['stop_ids'];
        abort_unless($tour->stops()->whereIn('id', $ids)->count() === count($ids) && $tour->stops()->count() === count($ids), 422);
        DB::transaction(fn () => collect($ids)->each(fn ($id, $index) => $tour->stops()->whereKey($id)->update(['position' => $index + 1])));
        $tour->load('stops');
        $points = array_merge([['lat' => $tour->start_snapshot['lat'], 'lng' => $tour->start_snapshot['lng']]], $tour->stops->map(fn ($s) => ['lat' => $s->latitude, 'lng' => $s->longitude])->all(), [['lat' => $tour->end_snapshot['lat'], 'lng' => $tour->end_snapshot['lng']]]);
        $route = $tomTom->route($points);
        $pricing = $tour->pricing_snapshot ?: [];
        $fee = max((float)($pricing['travel_minimum_fee'] ?? 0), (float)($pricing['travel_base_fee'] ?? 0) + $route['km'] * (float)($pricing['travel_per_km'] ?? 0) + $route['minutes'] * (float)($pricing['travel_per_minute'] ?? 0));
        $internal = $route['km'] * (float)($pricing['internal_per_km'] ?? 0) + $route['minutes'] * (float)($pricing['internal_per_minute'] ?? 0);
        $tour->update(['planned_km' => $route['km'], 'planned_drive_minutes' => $route['minutes'], 'travel_fee_pool' => round($fee, 2), 'internal_travel_cost' => round($internal, 2), 'encoded_polyline' => $geometryCodec->encode($route['points']), 'route_provider' => 'TomTom/manuell', 'route_optimized' => false, 'route_warnings' => ['Reihenfolge manuell geändert; nicht durch RouteXL optimiert.']]);
        $tour->stops()->update(['arrival_minutes' => null, 'cumulative_km' => null]);
        app(\App\Services\Sck\TourMaterializerService::class)->allocateFees($tour);
        return response()->json(['success' => true, 'km' => $route['km'], 'minutes' => $route['minutes']]);
    }

    public function optimize(SckTour $tour, RouteXlService $routeXl, TomTomService $tomTom, RouteGeometryCodec $geometryCodec)
    {
        $tour->load('stops');
        if ($tour->stops->isEmpty()) return back()->with('error', 'Für die Optimierung wird mindestens ein Stopp benötigt.');

        $routeStart = $this->routeStart(optional($tour->tour_date)->toDateString(), $tour->departure_time);
        $locations = [['address' => 'route:start', 'lat' => $tour->start_snapshot['lat'], 'lng' => $tour->start_snapshot['lng']]];
        foreach ($tour->stops as $stop) $locations[] = $this->routeLocation('stop:'.$stop->id, $stop, $routeStart);
        $locations[] = ['address' => 'route:end', 'lat' => $tour->end_snapshot['lat'], 'lng' => $tour->end_snapshot['lng']];
        $result = $routeXl->optimize($locations);
        if (! $result->successful()) return back()->with('error', $result->warning());

        $stopsById = $tour->stops->keyBy('id');
        $metrics = [];
        $orderedIds = collect($result->route)->map(function ($point) use (&$metrics) {
            if (! preg_match('/stop:(\d+)/', $point['name'], $matches)) return null;
            $id = (int) $matches[1];
            $metrics[$id] = ['arrival' => (int) $point['arrival'], 'distance' => (float) $point['distance']];
            return $id;
        })->filter(fn ($id) => $stopsById->has($id))->values();
        if ($orderedIds->count() !== $tour->stops->count()) return back()->with('error', 'RouteXL hat keine vollständige Stopp-Reihenfolge geliefert.');

        DB::transaction(function () use ($tour, $orderedIds, $metrics) {
            $orderedIds->each(function ($id, $index) use ($tour, $metrics) {
                $tour->stops()->whereKey($id)->update(['position' => $index + 1, 'arrival_minutes' => $metrics[$id]['arrival'] ?? null, 'cumulative_km' => $metrics[$id]['distance'] ?? null]);
            });
        });
        $tour->load('stops');
        $points = array_merge([['lat' => $tour->start_snapshot['lat'], 'lng' => $tour->start_snapshot['lng']]], $tour->stops->map(fn ($stop) => ['lat' => $stop->latitude, 'lng' => $stop->longitude])->all(), [['lat' => $tour->end_snapshot['lat'], 'lng' => $tour->end_snapshot['lng']]]);
        try { $route = $tomTom->route($points); } catch (\Throwable) { $route = ['km' => 0, 'minutes' => 0, 'points' => $points]; }
        $pricing = $tour->pricing_snapshot ?: [];
        $fee = max((float) ($pricing['travel_minimum_fee'] ?? 0), (float) ($pricing['travel_base_fee'] ?? 0) + $route['km'] * (float) ($pricing['travel_per_km'] ?? 0) + $route['minutes'] * (float) ($pricing['travel_per_minute'] ?? 0));
        $internal = $route['km'] * (float) ($pricing['internal_per_km'] ?? 0) + $route['minutes'] * (float) ($pricing['internal_per_minute'] ?? 0);
        $tour->update(['planned_km' => $route['km'], 'planned_drive_minutes' => $route['minutes'], 'planned_service_minutes' => $tour->stops->sum('service_minutes'), 'travel_fee_pool' => round($fee, 2), 'internal_travel_cost' => round($internal, 2), 'encoded_polyline' => $geometryCodec->encode($route['points']), 'route_provider' => 'RouteXL + TomTom', 'route_optimized' => true, 'route_warnings' => []]);
        app(\App\Services\Sck\TourMaterializerService::class)->allocateFees($tour);

        return redirect()->route('sck.routen.show', $tour)->with('success', 'Tour wurde mit RouteXL optimiert.');
    }

    private function refreshTourRoute(SckTour $tour, TomTomService $tomTom, RouteGeometryCodec $geometryCodec): void
    {
        $tour->load('stops');
        $points = array_merge([['lat' => $tour->start_snapshot['lat'], 'lng' => $tour->start_snapshot['lng']]], $tour->stops->map(fn ($s) => ['lat' => $s->latitude, 'lng' => $s->longitude])->all(), [['lat' => $tour->end_snapshot['lat'], 'lng' => $tour->end_snapshot['lng']]]);
        try { $route = $tomTom->route($points); } catch (\Throwable) { $route = ['km' => 0, 'minutes' => 0, 'points' => $points]; }
        $pricing = $tour->pricing_snapshot ?: [];
        $fee = max((float) ($pricing['travel_minimum_fee'] ?? 0), (float) ($pricing['travel_base_fee'] ?? 0) + $route['km'] * (float) ($pricing['travel_per_km'] ?? 0) + $route['minutes'] * (float) ($pricing['travel_per_minute'] ?? 0));
        $internal = $route['km'] * (float) ($pricing['internal_per_km'] ?? 0) + $route['minutes'] * (float) ($pricing['internal_per_minute'] ?? 0);
        $tour->update(['planned_km' => $route['km'], 'planned_drive_minutes' => $route['minutes'], 'planned_service_minutes' => $tour->stops->sum('service_minutes'), 'travel_fee_pool' => round($fee, 2), 'internal_travel_cost' => round($internal, 2), 'encoded_polyline' => $geometryCodec->encode($route['points']), 'route_provider' => 'TomTom/manuell', 'route_optimized' => false, 'route_warnings' => ['Stopp hinzugefügt; Route bitte bei Bedarf neu sortieren.']]);
        $tour->stops()->update(['arrival_minutes' => null, 'cumulative_km' => null]);
        app(\App\Services\Sck\TourMaterializerService::class)->allocateFees($tour);
    }

    /** Build RouteXL's minute-based arrival restrictions from the tour's 24-hour time window. */
    private function routeLocation(string $address, array|SckTourStop $stop, Carbon $routeStart): array
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

    private function routeStart(?string $date, ?string $time): Carbon
    {
        return Carbon::parse(($date ?: today()->toDateString()).' '.($time ?: '08:00'));
    }

    public function addItems(Request $request, SckTourStop $stop)
    {
        $data = $request->validate(['items' => 'required|array|min:1', 'items.*.item_id' => 'required|exists:sck_warehouse_items,id', 'items.*.quantity' => 'required|integer|min:1', 'items.*.actual_net_price' => 'required|numeric|min:0']);
        $warnings = DB::transaction(function () use ($data, $stop, $request) {
            $warnings = [];
            foreach ($data['items'] as $entry) {
                $item = SckWarehouseItem::lockForUpdate()->findOrFail($entry['item_id']);
                $quantity = (int)$entry['quantity']; $oldStock = $item->stueckzahl; $deducted = 0;
                if (!$item->is_dienstleistung) {
                    $deducted = min($quantity, $oldStock); $item->stueckzahl = max(0, $oldStock - $quantity); $item->save();
                    if ($quantity > $oldStock) $warnings[] = "{$item->bezeichnung}: {$quantity} benötigt, nur {$oldStock} auf Lager.";
                }
                $stop->items()->create(['warehouse_item_id' => $item->id, 'item_name' => $item->bezeichnung, 'article_number' => $item->neue_artikelnummer, 'unit' => $item->einheit ?: 'Stück', 'quantity' => $quantity, 'ek_snapshot' => $item->ek_ohne_st, 'vk_snapshot' => $item->vk_ohne_st, 'actual_net_price' => $entry['actual_net_price'], 'tax_rate' => $item->steuersatz ?: 19, 'source' => 'manual', 'stock_deducted' => $deducted]);
                SckWarehouseLog::create(['user_id' => $request->user()->id, 'item_id' => $item->id, 'tour_id' => $stop->tour_id, 'tour_stop_id' => $stop->id, 'quantity' => $quantity, 'success' => true, 'action' => 'remove', 'type' => 'tour', 'message' => "Tourverbrauch: {$quantity}x {$item->bezeichnung} ({$oldStock} → {$item->stueckzahl})"]);
            }
            return $warnings;
        });
        return back()->with($warnings ? ['error' => implode(' ', $warnings)] : ['success' => 'Artikel wurden dem Stopp zugeordnet und ausgebucht.']);
    }

    public function parseInvoice(Request $request, SckTourStop $stop, DatevInvoiceParserService $parser)
    {
        $request->validate(['invoice_file' => 'required|file|mimes:pdf|max:10240']);
        $file = $request->file('invoice_file'); $analysis = $parser->parsePdf($file->getRealPath());
        $analysis['file_hash'] = hash_file('sha256', $file->getRealPath()); $analysis['file_name'] = $file->getClientOriginalName();
        $analysis['duplicate'] = SckProcessedInvoice::where('file_hash', $analysis['file_hash'])->exists();
        return response()->json($analysis);
    }

    public function createInvoiceItem(Request $request, SckTourStop $stop)
    {
        $data = $request->validate([
            'bezeichnung' => 'required|string|max:255',
            'artikelgruppe' => 'required|in:Material,Dienstleistung',
            'einheit' => 'nullable|string|max:255',
            'steuersatz' => 'nullable|numeric|min:0|max:100',
            'ek_ohne_st' => 'required|numeric|min:0',
            'vk_ohne_st' => 'required|numeric|min:0',
            'neue_artikelnummer' => 'nullable|string|digits:5|unique:sck_warehouse_items,neue_artikelnummer',
        ]);
        $item = SckWarehouseItem::create($data + [
            'geraet' => 'Tour-PDF',
            'lieferant' => 'PDF-Rechnung',
            'einheit' => $data['einheit'] ?: 'Stück',
            'steuersatz' => $data['steuersatz'] ?? 19,
            'stueckzahl' => $data['artikelgruppe'] === 'Dienstleistung' ? 0 : 0,
        ]);

        return response()->json(['item' => [
            'id' => $item->id, 'bezeichnung' => $item->bezeichnung, 'stueckzahl' => $item->stueckzahl,
            'vk_ohne_st' => (float) $item->vk_ohne_st, 'ek_ohne_st' => (float) $item->ek_ohne_st,
        ]], 201);
    }

    public function commitInvoice(Request $request, SckTourStop $stop)
    {
        $data = $request->validate(['file_hash' => 'required|string|size:64|unique:sck_processed_invoices,file_hash', 'file_name' => 'required|string|max:255', 'invoice_number' => 'nullable|string|max:255', 'invoice_date' => 'nullable|date', 'items' => 'required|array|min:1', 'items.*.item_id' => 'required|exists:sck_warehouse_items,id', 'items.*.quantity' => 'required|integer|min:1', 'items.*.actual_net_price' => 'required|numeric|min:0']);
        DB::transaction(function () use ($data, $stop, $request) {
            $invoice = SckProcessedInvoice::create(['tour_stop_id' => $stop->id, 'user_id' => $request->user()->id, 'file_hash' => $data['file_hash'], 'file_name' => $data['file_name'], 'invoice_number' => $data['invoice_number'] ?? null, 'invoice_date' => $data['invoice_date'] ?? null]);
            foreach ($data['items'] as $entry) {
                $item = SckWarehouseItem::lockForUpdate()->findOrFail($entry['item_id']); $quantity = (int)$entry['quantity']; $old = $item->stueckzahl; $deducted = 0;
                if (!$item->is_dienstleistung) { $deducted = min($quantity, $old); $item->stueckzahl = max(0, $old - $quantity); $item->save(); }
                $stop->items()->create(['warehouse_item_id' => $item->id, 'processed_invoice_id' => $invoice->id, 'item_name' => $item->bezeichnung, 'article_number' => $item->neue_artikelnummer, 'unit' => $item->einheit ?: 'Stück', 'quantity' => $quantity, 'ek_snapshot' => $item->ek_ohne_st, 'vk_snapshot' => $item->vk_ohne_st, 'actual_net_price' => $entry['actual_net_price'], 'tax_rate' => $item->steuersatz ?: 19, 'source' => 'invoice', 'stock_deducted' => $deducted]);
                SckWarehouseLog::create(['user_id' => $request->user()->id, 'item_id' => $item->id, 'tour_id' => $stop->tour_id, 'tour_stop_id' => $stop->id, 'quantity' => $quantity, 'invoice_hash' => $data['file_hash'], 'success' => true, 'action' => 'remove', 'type' => 'invoice', 'message' => "Tour-Rechnung: {$quantity}x {$item->bezeichnung}"]);
            }
        });
        return response()->json(['success' => true]);
    }

    public function exportCsv(SckTour $tour): StreamedResponse
    {
        $tour->load(['stops.customer', 'stops.items']);
        return response()->streamDownload(function () use ($tour) {
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tour','Datum','Stopp','Kunde','Adresse','Artikel','Menge','EK netto','Verkauf netto','Anfahrtskosten','Plan-km','Plan-Minuten'], ';');
            foreach ($tour->stops as $stop) {
                if ($stop->items->isEmpty()) fputcsv($out, [$tour->number, optional($tour->tour_date)->format('d.m.Y'), $stop->title, $stop->customer?->name, $stop->address_snapshot['formatted'] ?? '', '', '', '', '', $stop->allocated_travel_fee, $tour->planned_km, $tour->planned_drive_minutes], ';');
                foreach ($stop->items as $item) fputcsv($out, [$tour->number, optional($tour->tour_date)->format('d.m.Y'), $stop->title, $stop->customer?->name, $stop->address_snapshot['formatted'] ?? '', $item->item_name, $item->quantity, $item->ek_snapshot, $item->actual_net_price, $stop->allocated_travel_fee, $tour->planned_km, $tour->planned_drive_minutes], ';');
            }
            fclose($out);
        }, $tour->number.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportGpx(SckTour $tour)
    {
        $tour->load('stops');
        $xml = view('sck.routes.exports.gpx', compact('tour'))->render();
        return response($xml, 200, ['Content-Type' => 'application/gpx+xml', 'Content-Disposition' => 'attachment; filename="'.$tour->number.'.gpx"']);
    }

    public function print(SckTour $tour)
    {
        $tour->load(['stops.customer', 'stops.items', 'stops.comments.user', 'stops.photos']);
        return view('sck.routes.exports.print', compact('tour'));
    }

    public function exportPdf(Request $request, SckTour $tour)
    {
        $tour->load(['stops.customer', 'stops.items', 'stops.comments.user', 'stops.photos']);
        $includePhotos = $request->boolean('photos');
        $photoData = [];
        if ($includePhotos) {
            foreach ($tour->stops as $stop) foreach ($stop->photos as $photo) {
                if (Storage::disk('sck_private')->exists($photo->path) && in_array($photo->mime_type, ['image/jpeg','image/png','image/webp'])) {
                    $photoData[$photo->id] = 'data:'.$photo->mime_type.';base64,'.base64_encode(Storage::disk('sck_private')->get($photo->path));
                }
            }
        }
        return Pdf::loadView('sck.routes.exports.pdf', compact('tour', 'includePhotos', 'photoData'))->setPaper('a4')->download($tour->number.'.pdf');
    }

    public function exportDatev(Request $request, SckTour $tour)
    {
        $tour->load(['stops.customer', 'stops.items']);
        $settings = SckRouteSetting::forUser($request->user()->id);
        $date = $tour->tour_date ?: now(); $yearStart = $date->copy()->startOfYear();
        $header = ['EXTF',700,21,'Buchungsstapel',13,now()->format('YmdHis').'000','','RE','','',$settings->datev_consultant_number,$settings->datev_client_number,$yearStart->format('Ymd'),4,$date->copy()->startOfMonth()->format('Ymd'),$date->copy()->endOfMonth()->format('Ymd'),'SCK '.$tour->number,'SK',1,0,0,'EUR','','','','',$settings->datev_chart,'','','',''];
        return response()->streamDownload(function () use ($tour, $settings, $date, $header) {
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF"); fputcsv($out, $header, ';');
            fputcsv($out, ['Umsatz (ohne Soll/Haben-Kz)','Soll/Haben-Kennzeichen','WKZ Umsatz','Kurs','Basis-Umsatz','WKZ Basis-Umsatz','Konto','Gegenkonto (ohne BU-Schlüssel)','BU-Schlüssel','Belegdatum','Belegfeld 1','Belegfeld 2','Skonto','Buchungstext'], ';');
            foreach ($tour->stops as $stop) {
                foreach ($stop->items as $item) {
                    $gross = (float)$item->actual_net_price * (float)$item->quantity * (1 + (float)$item->tax_rate / 100);
                    if ($gross <= 0) continue;
                    $revenue = (float)$item->tax_rate === 7.0 ? $settings->datev_revenue_7 : $settings->datev_revenue_19;
                    fputcsv($out, [number_format($gross,2,',',''),'S','EUR','','','',$settings->datev_debtor_account,$revenue,'',$date->format('dm'),$tour->number,'','','SCK '.mb_substr($item->item_name,0,50)], ';');
                }
                if ((float)$stop->allocated_travel_fee > 0) fputcsv($out, [number_format((float)$stop->allocated_travel_fee*1.19,2,',',''),'S','EUR','','','',$settings->datev_debtor_account,$settings->datev_revenue_19,'',$date->format('dm'),$tour->number,'','','Anfahrt '.mb_substr($stop->customer?->name ?? $stop->title,0,45)], ';');
            }
            fclose($out);
        }, 'EXTF_Buchungsstapel_'.$tour->number.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function selectedWeek(Request $request): Carbon
    {
        $value = (string) $request->input('week', now()->format('o-\\WW'));
        if (preg_match('/^(\d{4})-W(\d{2})$/', $value, $match)) {
            return Carbon::now()->setISODate((int) $match[1], (int) $match[2])->startOfWeek();
        }
        return now()->startOfWeek();
    }
}
