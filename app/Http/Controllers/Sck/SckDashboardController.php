<?php

namespace App\Http\Controllers\Sck;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SckDashboardController extends Controller
{
    /**
     * Render the SCK Main Dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // If the user configured a default sub-app, redirect there,
        // unless they explicitly request to see the dashboard menu (?no_redirect=1)
        if ($user->sck_default_app && !$request->has('no_redirect')) {
            $routes = ['lager' => 'sck.lager.index', 'kunden' => 'sck.kunden.index', 'routen' => 'sck.routen.index', 'wochenplanung' => 'sck.wochenplanung.index', 'adressverwaltung' => 'sck.administration.addresses.index', 'karte' => 'sck.map.index'];
            if (isset($routes[$user->sck_default_app])) return redirect()->route($routes[$user->sck_default_app]);
        }

        return view('sck.dashboard');
    }

    /**
     * Update the default sub-app settings for the user.
     */
    public function setDefaultApp(Request $request)
    {
        $request->validate([
            'default_app' => 'nullable|string|in:lager,kunden,routen,wochenplanung,adressverwaltung,karte',
        ]);

        $user = $request->user();
        $user->sck_default_app = $request->default_app;
        $user->save();

        $message = $request->default_app 
            ? 'Standard-App erfolgreich festgelegt.' 
            : 'Standard-App zurückgesetzt (Hauptmenü wird nun geladen).';

        return redirect()->back()->with('success', $message);
    }
}
