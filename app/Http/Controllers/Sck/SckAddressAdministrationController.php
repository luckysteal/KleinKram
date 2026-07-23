<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckCustomer;
use App\Models\Sck\SckStopTemplate;
use App\Services\Sck\TomTomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class SckAddressAdministrationController extends Controller
{
    public function index()
    {
        $missingCoordinates = SckCustomer::query()
            ->where(fn ($query) => $query->whereNull('latitude')->orWhereNull('longitude'))
            ->orderBy('name')
            ->get()
            ->map(fn (SckCustomer $customer) => $this->addressEntry('customer', $customer, $customer->name))
            ->concat(
                SckStopTemplate::query()
                    ->where(fn ($query) => $query->whereNull('latitude')->orWhereNull('longitude'))
                    ->orderBy('title')
                    ->get()
                    ->map(fn (SckStopTemplate $stop) => $this->addressEntry('stop', $stop, $stop->title))
            )
            ->sortBy(['type_label', 'title'])
            ->values();

        return view('sck.administration.addresses.index', compact('missingCoordinates'));
    }

    public function calculateCoordinates(Request $request, string $type, int $id, TomTomService $tomTom): RedirectResponse
    {
        $record = $type === 'customer'
            ? SckCustomer::findOrFail($id)
            : SckStopTemplate::findOrFail($id);

        if ($record->latitude !== null && $record->longitude !== null) {
            return back()->with('success', 'Für diese Adresse sind bereits Koordinaten hinterlegt.');
        }

        if (!$tomTom->configured()) {
            return back()->with('error', 'TomTom ist nicht konfiguriert. Bitte TOMTOM_API_KEY in der Server-Umgebung hinterlegen.');
        }

        $address = $record->full_address;
        if (mb_strlen($address) < 3) {
            return back()->with('error', 'Die Adresse ist unvollständig und kann nicht berechnet werden.');
        }

        try {
            $match = collect($tomTom->search($address, 1))->first();
        } catch (Throwable) {
            return back()->with('error', 'Die Koordinaten konnten gerade nicht ermittelt werden. Bitte später erneut versuchen.');
        }

        if (!$match || !isset($match['lat'], $match['lng'])) {
            return back()->with('error', 'Die Adresse konnte über TomTom nicht gefunden werden. Bitte die Adresse prüfen.');
        }

        $record->update(['latitude' => $match['lat'], 'longitude' => $match['lng']]);

        return back()->with('success', "Koordinaten für \"{$record->full_address}\" wurden berechnet.");
    }

    private function addressEntry(string $type, SckCustomer|SckStopTemplate $record, string $title): array
    {
        return [
            'type' => $type,
            'type_label' => $type === 'customer' ? 'Kunde' : 'Stopp',
            'id' => $record->id,
            'title' => $title,
            'address' => $record->full_address,
        ];
    }
}
