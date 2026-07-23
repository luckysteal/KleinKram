<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckCustomer;
use App\Models\Sck\SckRouteSetting;
use App\Services\Sck\DatevCustomerImportService;
use App\Services\Sck\TomTomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class SckCustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = SckCustomer::query()
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('city', 'like', "%{$search}%")->orWhere('postal_code', 'like', "%{$search}%")->orWhere('datev_account', 'like', "%{$search}%")))
            ->withCount(['tourStops as completed_visits_count' => fn ($q) => $q->whereHas('tour', fn ($q) => $q->where('status', 'completed'))])
            ->orderBy('name')->paginate(20)->withQueryString();
        return view('sck.customers.index', compact('customers'));
    }

    public function store(Request $request, TomTomService $tomTom)
    {
        $customer = SckCustomer::create($this->validated($request));
        $this->createCoordinatesAfterSave($customer, $tomTom);
        return redirect()->route('sck.kunden.show', $customer)->with('success', 'Kunde wurde angelegt.');
    }

    public function importDatev(Request $request, DatevCustomerImportService $importer)
    {
        Validator::make($request->all(), [
            'datev_file' => 'required|file|max:10240|mimes:csv,txt',
        ], [
            'datev_file.required' => 'Bitte eine DATEV-CSV-Datei auswählen.',
            'datev_file.mimes' => 'Die DATEV-Datei muss eine CSV- oder TXT-Datei sein.',
            'datev_file.max' => 'Die DATEV-Datei darf höchstens 10 MB groß sein.',
        ])->validateWithBag('datevImport');

        try {
            $result = $importer->import($request->file('datev_file')->getRealPath());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['datev_file' => $exception->getMessage()], 'datevImport');
        }

        return redirect()->route('sck.kunden.index')->with(
            'success',
            "DATEV-Import abgeschlossen: {$result['created']} neu, {$result['updated']} aktualisiert, {$result['unchanged']} unverändert, {$result['skipped_creditors']} Kreditoren und {$result['skipped_invalid']} ungültige Zeilen übersprungen."
        );
    }

    public function show(Request $request, SckCustomer $customer)
    {
        $customer->load(['changes.user', 'photos.comments.user', 'tourStops' => fn ($q) => $q->with(['tour', 'items'])->latest()]);
        $completed = $customer->tourStops->filter(fn ($stop) => $stop->tour?->status === 'completed');
        $sales = $completed->sum(fn ($stop) => $stop->items->sum(fn ($item) => (float)$item->actual_net_price * (float)$item->quantity));
        $ek = $completed->sum(fn ($stop) => $stop->items->sum(fn ($item) => (float)$item->ek_snapshot * (float)$item->quantity));
        $fees = $completed->sum('allocated_travel_fee');
        $internal = $completed->groupBy('tour_id')->sum(function ($stops) { $tour = $stops->first()->tour; return $tour ? (float)$tour->internal_travel_cost * $stops->count() / max(1, $tour->stops()->count()) : 0; });
        $margin = $sales + $fees - $ek - $internal;
        $settings = SckRouteSetting::query()->where('user_id', $request->user()->id)->first();
        $distanceFromHome = $customer->latitude !== null && $customer->longitude !== null && $settings?->home_latitude !== null && $settings?->home_longitude !== null
            ? $this->distanceInKilometers($settings->home_latitude, $settings->home_longitude, $customer->latitude, $customer->longitude)
            : null;
        $stats = ['visits' => $completed->count(), 'sales' => $sales, 'ek' => $ek, 'fees' => $fees, 'internal' => $internal, 'margin' => $margin, 'average_margin' => $completed->count() ? $margin / $completed->count() : 0, 'items' => $completed->sum(fn ($stop) => $stop->items->sum('quantity')), 'last_visit' => $completed->max(fn ($stop) => $stop->tour?->tour_date), 'distance_from_home' => $distanceFromHome];
        return view('sck.customers.show', compact('customer', 'stats'));
    }

    public function update(Request $request, SckCustomer $customer, TomTomService $tomTom)
    {
        $data = $this->validated($request);
        $addressChanged = collect(['street', 'house_number', 'postal_code', 'city', 'country_code'])
            ->contains(fn (string $field) => (string) $customer->{$field} !== (string) ($data[$field] ?? null));
        $customer->update($data);
        if ($addressChanged) $this->createCoordinatesAfterSave($customer, $tomTom);
        return back()->with('success', 'Kundendaten wurden aktualisiert.');
    }

    public function updateReputation(Request $request, SckCustomer $customer)
    {
        $data = $request->validate([
            'reputation_rating' => 'nullable|integer|between:1,5',
            'reputation_note' => 'nullable|string|max:5000',
        ]);

        if (array_key_exists('reputation_rating', $data) && $data['reputation_rating'] !== null) {
            $data['reputation_reviewed_at'] = now()->toDateString();
        }

        $customer->update($data);

        return back()->with('success', 'Reputation wurde aktualisiert.');
    }

    public function destroy(SckCustomer $customer)
    {
        $customer->delete();
        return redirect()->route('sck.kunden.index')->with('success', 'Kunde wurde archiviert.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255', 'street' => 'nullable|string|max:255', 'house_number' => 'nullable|string|max:40',
            'postal_code' => 'nullable|string|max:20', 'city' => 'nullable|string|max:255', 'country_code' => 'nullable|string|size:2',
            'phone' => 'nullable|string|max:255', 'email' => 'nullable|email|max:255', 'status' => 'required|in:active,inactive,blocked',
            'tags' => 'nullable', 'notes' => 'nullable|string|max:10000', 'reputation_rating' => 'nullable|integer|between:1,5',
            'reputation_note' => 'nullable|string|max:5000', 'reputation_reviewed_at' => 'nullable|date',
        ]);
        if (is_string($data['tags'] ?? null)) $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $data['tags']))));
        return $data;
    }

    private function createCoordinatesAfterSave(SckCustomer $customer, TomTomService $tomTom): void
    {
        $address = trim(implode(', ', array_filter([
            trim($customer->street.' '.$customer->house_number),
            trim($customer->postal_code.' '.$customer->city),
            $customer->country_code ?: 'DE',
        ])));

        if (!$tomTom->configured() || mb_strlen($address) < 3) return;

        $match = collect($tomTom->search($address, 1))->first();
        $customer->update([
            'latitude' => $match['lat'] ?? null,
            'longitude' => $match['lng'] ?? null,
        ]);
    }

    private function distanceInKilometers(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): float
    {
        $earthRadius = 6371;
        $latitudeDelta = deg2rad($toLatitude - $fromLatitude);
        $longitudeDelta = deg2rad($toLongitude - $fromLongitude);
        $a = sin($latitudeDelta / 2) ** 2 + cos(deg2rad($fromLatitude)) * cos(deg2rad($toLatitude)) * sin($longitudeDelta / 2) ** 2;

        return round($earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }
}
