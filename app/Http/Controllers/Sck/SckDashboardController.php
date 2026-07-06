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
            if ($user->sck_default_app === 'lager') {
                return redirect()->route('sck.lager.index');
            }
        }

        return view('sck.dashboard');
    }

    /**
     * Update the default sub-app settings for the user.
     */
    public function setDefaultApp(Request $request)
    {
        $request->validate([
            'default_app' => 'nullable|string|in:lager',
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
