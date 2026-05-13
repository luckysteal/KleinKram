<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/conjugate', function (Request $request) {
    $verb = $request->query('verb');
    if (!$verb) {
        return response()->json(['success' => false, 'error' => 'Verb is required'], 400);
    }

    $apiUrl = config('services.german_verbs.url');

    try {
        $response = \Illuminate\Support\Facades\Http::timeout(10)->get($apiUrl, [
            'verb' => $verb
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return response()->json([
            'success' => false,
            'error' => 'External API error',
            'status' => $response->status()
        ], 502);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Could not reach conjugation API',
            'message' => $e->getMessage()
        ], 502);
    }
});
