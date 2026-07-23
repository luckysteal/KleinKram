<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckCustomer;
use App\Models\Sck\SckStopTemplate;
use App\Models\Sck\SckWarehouseItem;
use App\Services\Sck\TomTomService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SckStopTemplateController extends Controller
{
    public function index(Request $request)
    {
        $stops = SckStopTemplate::with('customer')->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%")->orWhere('city', 'like', "%{$s}%"))->orderBy('title')->paginate(20);
        return view('sck.stops.index', ['stops' => $stops, 'customers' => SckCustomer::orderBy('name')->get(), 'items' => SckWarehouseItem::orderBy('bezeichnung')->get()]);
    }

    public function store(Request $request, TomTomService $tomTom)
    {
        $data = $this->validated($request);
        if (!$tomTom->configured()) {
            throw ValidationException::withMessages(['street' => 'TomTom ist nicht konfiguriert. Bitte TOMTOM_API_KEY in der Server-Umgebung hinterlegen.']);
        }
        $address = trim(implode(', ', array_filter([
            trim(($data['street'] ?? '').' '.($data['house_number'] ?? '')),
            trim(($data['postal_code'] ?? '').' '.($data['city'] ?? '')),
            $data['country_code'] ?? 'DE',
        ])));
        $match = collect($tomTom->search($address, 1))->first();
        if (!$match) {
            throw ValidationException::withMessages(['street' => 'Die Adresse konnte nicht über TomTom gefunden werden. Bitte Adresse prüfen und erneut suchen.']);
        }
        $data['latitude'] = $match['lat'];
        $data['longitude'] = $match['lng'];
        $stop = SckStopTemplate::create($data);
        $this->syncItems($stop, $request);
        return back()->with('success', 'Haltestellen-Vorlage wurde gespeichert.');
    }

    public function update(Request $request, SckStopTemplate $stopTemplate)
    {
        $stopTemplate->update($this->validated($request));
        $this->syncItems($stopTemplate, $request);
        return back()->with('success', 'Haltestellen-Vorlage wurde aktualisiert.');
    }

    public function destroy(SckStopTemplate $stopTemplate)
    {
        $stopTemplate->delete();
        return back()->with('success', 'Haltestellen-Vorlage wurde archiviert.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'nullable|exists:sck_customers,id', 'title' => 'required|string|max:255', 'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:40', 'postal_code' => 'nullable|string|max:20', 'city' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|size:2',
            'service_minutes' => 'required|integer|between:0,1440', 'window_start' => 'nullable|date_format:H:i', 'window_end' => 'nullable|date_format:H:i|after:window_start',
            'priority' => 'required|integer|between:1,5', 'recurrence' => 'nullable|string|max:255', 'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255', 'access_notes' => 'nullable|string|max:5000', 'parking_notes' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:10000', 'active' => 'nullable|boolean',
        ]) + ['active' => $request->boolean('active')];
    }

    private function syncItems(SckStopTemplate $stop, Request $request): void
    {
        $ids = $request->validate(['item_ids' => 'nullable|array', 'item_ids.*' => 'exists:sck_warehouse_items,id'])['item_ids'] ?? [];
        $stop->items()->sync(collect($ids)->mapWithKeys(fn ($id) => [$id => ['suggested_quantity' => 1]])->all());
    }
}
