<?php

use App\Http\Controllers\Sck\SckDashboardController;
use App\Http\Controllers\Sck\SckWarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'sck'])->group(function () {
    Route::get('/dashboard', [SckDashboardController::class, 'index'])->name('sck.dashboard');
    Route::post('/set-default-app', [SckDashboardController::class, 'setDefaultApp'])->name('sck.set-default-app');

    // Lagersystem (Warehouse Sub-app)
    Route::get('/lager', [SckWarehouseController::class, 'index'])->name('sck.lager.index');
    Route::post('/lager/store', [SckWarehouseController::class, 'store'])->name('sck.lager.store');
    Route::post('/lager/update', [SckWarehouseController::class, 'update'])->name('sck.lager.update');
    Route::post('/lager/update-stock', [SckWarehouseController::class, 'updateStock'])->name('sck.lager.update-stock');
    Route::get('/lager/export', [SckWarehouseController::class, 'export'])->name('sck.lager.export');
    Route::get('/lager/export-datev', [SckWarehouseController::class, 'exportDatev'])->name('sck.lager.export-datev');
    Route::get('/lager/search-json', [SckWarehouseController::class, 'searchJson'])->name('sck.lager.search-json');
    Route::get('/lager/generate-number', [SckWarehouseController::class, 'generateNumber'])->name('sck.lager.generate-number');
    
    // Scanner routes
    Route::get('/lager/scan', [SckWarehouseController::class, 'scanPage'])->name('sck.lager.scan');
    Route::post('/lager/scan/action', [SckWarehouseController::class, 'processScan'])->name('sck.lager.scan.action');
    
    // Article Detail Route (landing page for scanning outside the app)
    Route::get('/lager/artikel/{neue_artikelnummer}', [SckWarehouseController::class, 'show'])->name('sck.lager.artikel');
});
