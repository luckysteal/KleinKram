<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckPlanCandidate;
use App\Models\Sck\SckStopTemplate;
use App\Models\Sck\SckWeeklyPlan;
use App\Models\Sck\SckRouteSetting;
use App\Models\Sck\SckCustomer;
use App\Services\Sck\TourMaterializerService;
use App\Services\Sck\WeeklyPlannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SckWeeklyPlanController extends Controller
{
    public function index(Request $request)
    {
        $selectedWeek = $this->selectedWeek($request);
        $plans = SckWeeklyPlan::withCount(['stops', 'tours'])
            ->where('user_id', $request->user()->id)
            ->whereDate('week_start', $selectedWeek)
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();
        $plannedTours = \App\Models\Sck\SckTour::with(['weeklyPlan:id,name'])
            ->withCount('stops')
            ->where('user_id', $request->user()->id)
            ->whereBetween('tour_date', [$selectedWeek, $selectedWeek->copy()->endOfWeek()])
            ->latest('tour_date')
            ->get();

        return view('sck.weekly.index', compact('plans', 'plannedTours', 'selectedWeek'));
    }

    public function create()
    {
        return view('sck.weekly.create', [
            'templates' => SckStopTemplate::with('customer')->withCount('tourStops')->where('active', true)->orderByDesc('tour_stops_count')->orderBy('title')->get(),
            'draftStops' => collect(session('sck.route_draft_stops', [])),
            'customers' => SckCustomer::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255', 'week_start' => 'required|date', 'tour_count' => 'required|integer|between:1,20',
            'template_ids' => 'nullable|array', 'template_ids.*' => 'exists:sck_stop_templates,id',
            'draft_stop_ids' => 'nullable|array', 'draft_stop_ids.*' => 'string',
            'enabled_days' => 'required|array|min:1', 'enabled_days.*' => 'integer|between:1,7',
            'default_start' => 'required|date_format:H:i', 'max_stops' => 'required|integer|between:1,50',
            'max_minutes' => 'required|integer|between:30,1440', 'max_km' => 'nullable|numeric|min:1',
        ]);
        $templates = SckStopTemplate::with('customer')->whereIn('id', $data['template_ids'] ?? [])->get();
        $drafts = collect(session('sck.route_draft_stops', []))->keyBy('id')->only($data['draft_stop_ids'] ?? [])->values();
        abort_if($templates->isEmpty() && $drafts->isEmpty(), 422, 'Bitte mindestens einen Stopp auswählen.');
        $plan = DB::transaction(function () use ($data, $request) {
            $plan = SckWeeklyPlan::create([
                'user_id' => $request->user()->id, 'name' => $data['name'], 'week_start' => $data['week_start'], 'tour_count' => $data['tour_count'],
                'parameters' => ['enabled_days' => array_map('intval', $data['enabled_days']), 'default_start' => $data['default_start'], 'max_stops' => $data['max_stops'], 'max_minutes' => $data['max_minutes'], 'max_km' => $data['max_km'] ?? null, 'equal_share' => $request->boolean('equal_share'), 'allow_multiple_per_day' => $request->boolean('allow_multiple_per_day'), 'slots' => []],
            ]);
            foreach (SckStopTemplate::with('customer')->whereIn('id', $data['template_ids'] ?? [])->get() as $template) {
                if ($template->latitude === null || $template->longitude === null) continue;
                $plan->stops()->create([
                    'stop_template_id' => $template->id, 'customer_id' => $template->customer_id, 'title' => $template->title, 'address' => $template->full_address,
                    'latitude' => $template->latitude, 'longitude' => $template->longitude, 'service_minutes' => $template->service_minutes,
                    'allowed_weekdays' => $data['enabled_days'], 'window_start' => $template->window_start, 'window_end' => $template->window_end,
                    'priority' => $template->priority, 'notes' => $template->notes,
                ]);
            }
            foreach (collect(session('sck.route_draft_stops', []))->keyBy('id')->only($data['draft_stop_ids'] ?? []) as $draft) {
                $plan->stops()->create([
                    'customer_id' => $draft['customer_id'] ?? null, 'title' => $draft['title'], 'address' => $draft['address'],
                    'latitude' => $draft['latitude'], 'longitude' => $draft['longitude'], 'service_minutes' => $draft['service_minutes'],
                    'allowed_weekdays' => $data['enabled_days'], 'window_start' => $draft['window_start'] ?? null, 'window_end' => $draft['window_end'] ?? null,
                    'priority' => $draft['priority'] ?? 3, 'notes' => $draft['notes'] ?? null,
                ]);
            }
            return $plan;
        });
        $request->session()->forget('sck.route_draft_stops');
        return redirect()->route('sck.wochenplanung.show', $plan)->with('success', 'Wochenplanung wurde angelegt.');
    }

    public function show(Request $request, SckWeeklyPlan $weeklyPlan)
    {
        abort_unless($weeklyPlan->user_id === $request->user()->id, 404);
        $weeklyPlan->load(['stops.customer', 'candidates']);
        $plannedTours = \App\Models\Sck\SckTour::withCount('stops')
            ->where('user_id', $request->user()->id)
            ->whereBetween('tour_date', [$weeklyPlan->week_start, $weeklyPlan->week_start->copy()->endOfWeek()])
            ->latest('tour_date')
            ->get();
        return view('sck.weekly.show', compact('weeklyPlan', 'plannedTours'));
    }

    public function update(Request $request, SckWeeklyPlan $weeklyPlan)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255', 'tour_count' => 'required|integer|between:1,20', 'parameters' => 'required|array',
            'parameters.enabled_days' => 'required|array|min:1', 'parameters.enabled_days.*' => 'integer|between:1,7',
            'parameters.default_start' => 'required|date_format:H:i', 'parameters.max_stops' => 'required|integer|between:1,50',
            'parameters.max_minutes' => 'required|integer|between:30,1440', 'parameters.max_km' => 'nullable|numeric|min:1',
            'parameters.slots' => 'nullable|array', 'parameters.slots.*.weekday' => 'required|integer|between:1,7',
            'parameters.slots.*.start_time' => 'required|date_format:H:i', 'parameters.slots.*.max_stops' => 'nullable|integer|between:1,50',
            'parameters.slots.*.max_minutes' => 'nullable|integer|between:30,1440', 'parameters.slots.*.max_km' => 'nullable|numeric|min:1',
            'parameters.slots.*.direction' => 'nullable|in:N,NE,E,SE,S,SW,W,NW', 'parameters.slots.*.direction_hard' => 'nullable|boolean',
        ]);
        $data['parameters']['equal_share'] = $request->boolean('parameters.equal_share');
        $data['parameters']['allow_multiple_per_day'] = $request->boolean('parameters.allow_multiple_per_day');
        foreach ($data['parameters']['slots'] ?? [] as $i => $slot) $data['parameters']['slots'][$i]['direction_hard'] = $request->boolean("parameters.slots.{$i}.direction_hard");
        $weeklyPlan->update($data);
        if ($request->expectsJson()) return response()->json(['success' => true]);
        return back()->with('success', 'Planungsparameter wurden gespeichert.');
    }

    public function updateStop(Request $request, SckWeeklyPlan $weeklyPlan, int $stop)
    {
        $planStop = $weeklyPlan->stops()->findOrFail($stop);
        $planStop->update($request->validate([
            'service_minutes' => 'required|integer|between:0,1440', 'allowed_weekdays' => 'nullable|array', 'allowed_weekdays.*' => 'integer|between:1,7',
            'required_date' => 'nullable|date', 'window_start' => 'nullable|date_format:H:i', 'window_end' => 'nullable|date_format:H:i',
            'priority' => 'required|integer|between:1,5', 'fixed_tour_index' => 'nullable|integer|min:1', 'fixed_position' => 'nullable|integer|min:1',
            'direction' => 'nullable|in:N,NE,E,SE,S,SW,W,NW', 'notes' => 'nullable|string|max:10000',
        ]));
        if ($request->expectsJson()) return response()->json(['success' => true]);
        return back()->with('success', 'Stopp-Vorgaben wurden gespeichert.');
    }

    public function generate(SckWeeklyPlan $weeklyPlan, WeeklyPlannerService $planner)
    {
        abort_unless($weeklyPlan->stops()->exists(), 422, 'Keine Stopps vorhanden.');
        $candidates = $planner->generate($weeklyPlan);
        return response()->json(['success' => true, 'candidates' => $candidates]);
    }

    public function reorder(Request $request, SckWeeklyPlan $weeklyPlan, SckPlanCandidate $candidate)
    {
        abort_unless($candidate->weekly_plan_id === $weeklyPlan->id, 404);
        $data = $request->validate(['tours' => 'required|array', 'tours.*.stops' => 'required|array', 'unassigned' => 'nullable|array']);
        $candidate->update(['tours' => $data['tours'], 'unassigned' => $data['unassigned'] ?? [], 'feasible' => empty($data['unassigned'] ?? [])]);
        return response()->json(['success' => true]);
    }

    public function recalculate(Request $request, SckWeeklyPlan $weeklyPlan, SckPlanCandidate $candidate, WeeklyPlannerService $planner)
    {
        abort_unless($candidate->weekly_plan_id === $weeklyPlan->id, 404);
        $mode = $request->validate(['mode' => 'required|in:metrics,order,full'])['mode'];
        $updated = $planner->recalculateCandidate($candidate, $mode);
        return response()->json(['success' => true, 'candidate' => $updated]);
    }

    public function materialize(SckWeeklyPlan $weeklyPlan, SckPlanCandidate $candidate, TourMaterializerService $service)
    {
        abort_unless($candidate->weekly_plan_id === $weeklyPlan->id, 404);
        abort_if($weeklyPlan->tours()->exists(), 409, 'Aus diesem Wochenplan wurden bereits Touren erzeugt.');
        abort_if(!$candidate->feasible || !empty($candidate->unassigned), 422, 'Nicht zugewiesene Stopps müssen vor der Übernahme geklärt werden.');
        $tours = $service->materialize($candidate);
        return redirect()->route('sck.routen.index', ['week' => $weeklyPlan->week_start->format('o-\\WW')])->with('success', count($tours).' Touren wurden gespeichert.');
    }

    private function selectedWeek(Request $request): Carbon
    {
        $value = (string) $request->input('week', now()->format('o-\\WW'));
        if (preg_match('/^(\d{4})-W(\d{2})$/', $value, $match)) {
            return Carbon::now()->setISODate((int) $match[1], (int) $match[2])->startOfWeek();
        }
        return now()->startOfWeek();
    }

    public function exportCsv(SckWeeklyPlan $weeklyPlan)
    {
        $weeklyPlan->load(['tours.stops.customer', 'tours.stops.items']);
        return response()->streamDownload(function () use ($weeklyPlan) {
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tour','Datum','Stopp','Kunde','Adresse','Plan-km','Fahrzeit','Servicezeit','Anfahrt','Umsatz netto','EK','Marge'], ';');
            foreach ($weeklyPlan->tours as $tour) foreach ($tour->stops as $stop) {
                $sales = $stop->items->sum(fn ($i) => (float)$i->actual_net_price*(float)$i->quantity); $ek = $stop->items->sum(fn ($i) => (float)$i->ek_snapshot*(float)$i->quantity);
                fputcsv($out, [$tour->number,optional($tour->tour_date)->format('d.m.Y'),$stop->title,$stop->customer?->name,$stop->address_snapshot['formatted']??'', $tour->planned_km,$tour->planned_drive_minutes,$stop->service_minutes,$stop->allocated_travel_fee,$sales,$ek,$sales+(float)$stop->allocated_travel_fee-$ek], ';');
            }
            fclose($out);
        }, 'SCK_Woche_'.$weeklyPlan->week_start->format('Y-m-d').'.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    public function exportPdf(SckWeeklyPlan $weeklyPlan)
    {
        $weeklyPlan->load(['tours.stops.customer', 'tours.stops.items']);
        return Pdf::loadView('sck.weekly.pdf', compact('weeklyPlan'))->setPaper('a4')->download('SCK_Woche_'.$weeklyPlan->week_start->format('Y-m-d').'.pdf');
    }

    public function exportDatev(Request $request, SckWeeklyPlan $weeklyPlan)
    {
        $weeklyPlan->load(['tours.stops.customer', 'tours.stops.items']);
        $settings = SckRouteSetting::forUser($request->user()->id); $date = $weeklyPlan->week_start;
        $header = ['EXTF',700,21,'Buchungsstapel',13,now()->format('YmdHis').'000','','RE','','',$settings->datev_consultant_number,$settings->datev_client_number,$date->copy()->startOfYear()->format('Ymd'),4,$date->copy()->startOfMonth()->format('Ymd'),$date->copy()->endOfMonth()->format('Ymd'),'SCK Woche '.$date->format('Y-m-d'),'SK',1,0,0,'EUR','','','','',$settings->datev_chart,'','','',''];
        return response()->streamDownload(function () use ($weeklyPlan, $settings, $header) {
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF"); fputcsv($out, $header, ';');
            fputcsv($out, ['Umsatz (ohne Soll/Haben-Kz)','Soll/Haben-Kennzeichen','WKZ Umsatz','Kurs','Basis-Umsatz','WKZ Basis-Umsatz','Konto','Gegenkonto (ohne BU-Schlüssel)','BU-Schlüssel','Belegdatum','Belegfeld 1','Belegfeld 2','Skonto','Buchungstext'], ';');
            foreach ($weeklyPlan->tours as $tour) foreach ($tour->stops as $stop) {
                foreach ($stop->items as $item) {
                    $gross = (float)$item->actual_net_price * (float)$item->quantity * (1 + (float)$item->tax_rate / 100); if ($gross <= 0) continue;
                    $revenue = (float)$item->tax_rate === 7.0 ? $settings->datev_revenue_7 : $settings->datev_revenue_19;
                    fputcsv($out, [number_format($gross,2,',',''),'S','EUR','','','',$settings->datev_debtor_account,$revenue,'',optional($tour->tour_date)->format('dm'),$tour->number,'','','SCK '.mb_substr($item->item_name,0,50)], ';');
                }
                if ((float)$stop->allocated_travel_fee > 0) fputcsv($out, [number_format((float)$stop->allocated_travel_fee*1.19,2,',',''),'S','EUR','','','',$settings->datev_debtor_account,$settings->datev_revenue_19,'',optional($tour->tour_date)->format('dm'),$tour->number,'','','Anfahrt '.mb_substr($stop->customer?->name ?? $stop->title,0,45)], ';');
            }
            fclose($out);
        }, 'EXTF_SCK_Woche_'.$weeklyPlan->week_start->format('Y-m-d').'.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
