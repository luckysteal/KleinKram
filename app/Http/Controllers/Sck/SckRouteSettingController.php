<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use App\Models\Sck\SckRouteSetting;
use App\Services\Sck\TomTomService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SckRouteSettingController extends Controller
{
    public function edit(Request $request) { return view('sck.routes.settings', ['settings' => SckRouteSetting::forUser($request->user()->id)]); }

    public function update(Request $request, TomTomService $tomTom)
    {
        $data = $request->validate([
            'home_name' => 'required|string|max:255', 'home_address' => 'required|string|max:500',
            'travel_base_fee' => 'required|numeric|min:0', 'travel_per_km' => 'required|numeric|min:0', 'travel_per_minute' => 'required|numeric|min:0', 'travel_minimum_fee' => 'required|numeric|min:0',
            'internal_per_km' => 'required|numeric|min:0', 'internal_per_minute' => 'required|numeric|min:0', 'datev_consultant_number' => 'required|digits_between:4,7',
            'datev_client_number' => 'required|digits_between:1,5', 'datev_chart' => 'required|digits:2', 'datev_revenue_19' => 'required|string|max:8',
            'datev_revenue_7' => 'required|string|max:8', 'datev_debtor_account' => 'required|string|max:8',
        ]);

        if (!$tomTom->configured()) {
            throw ValidationException::withMessages(['home_address' => 'Die Home-Adresse kann nicht ermittelt werden, weil TomTom nicht konfiguriert ist. Bitte TOMTOM_API_KEY hinterlegen.']);
        }

        try {
            $match = collect($tomTom->search($data['home_address'], 1))->first();
        } catch (\Throwable) {
            throw ValidationException::withMessages(['home_address' => 'Die Home-Adresse konnte derzeit nicht ermittelt werden. Bitte Verbindung prüfen und erneut versuchen.']);
        }

        if (!$match || !isset($match['lat'], $match['lng'])) {
            throw ValidationException::withMessages(['home_address' => 'Die Home-Adresse konnte nicht ermittelt werden. Bitte die vollständige Adresse prüfen und später erneut versuchen.']);
        }

        $data['home_latitude'] = $match['lat'];
        $data['home_longitude'] = $match['lng'];
        $data['datev_verified'] = $request->boolean('datev_verified');
        SckRouteSetting::forUser($request->user()->id)->update($data);
        return back()->with('success', 'Routen-Einstellungen wurden gespeichert.');
    }

    public function snooze(Request $request)
    {
        SckRouteSetting::forUser($request->user()->id)->update(['datev_reminder_snoozed_until' => now()->addDays(7)]);
        return back()->with('success', 'DATEV-Erinnerung wurde für sieben Tage ausgeblendet.');
    }
}
