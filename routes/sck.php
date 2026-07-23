<?php

use App\Http\Controllers\Sck\SckDashboardController;
use App\Http\Controllers\Sck\SckWarehouseController;
use App\Http\Controllers\Sck\SckAddressSearchController;
use App\Http\Controllers\Sck\SckAddressAdministrationController;
use App\Http\Controllers\Sck\SckCommentController;
use App\Http\Controllers\Sck\SckCustomerController;
use App\Http\Controllers\Sck\SckMediaController;
use App\Http\Controllers\Sck\SckMapController;
use App\Http\Controllers\Sck\SckRouteSettingController;
use App\Http\Controllers\Sck\SckStopTemplateController;
use App\Http\Controllers\Sck\SckTourController;
use App\Http\Controllers\Sck\SckWeeklyPlanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'sck'])->group(function () {
    Route::get('/dashboard', [SckDashboardController::class, 'index'])->name('sck.dashboard');
    Route::post('/set-default-app', [SckDashboardController::class, 'setDefaultApp'])->name('sck.set-default-app');

    Route::get('/suche/adressen', SckAddressSearchController::class)->middleware('throttle:60,1')->name('sck.address-search');

    Route::get('/karte', [SckMapController::class, 'index'])->name('sck.map.index');
    Route::get('/karte/daten', [SckMapController::class, 'data'])->name('sck.map.data');
    Route::put('/karte/ebenen', [SckMapController::class, 'updateLayers'])->name('sck.map.layers.update');
    Route::get('/karte/touren-suche', [SckMapController::class, 'searchTours'])->middleware('throttle:60,1')->name('sck.map.tour-search');
    Route::get('/karte/adresse', [SckMapController::class, 'reverseGeocode'])->middleware('throttle:30,1')->name('sck.map.reverse-geocode');
    Route::post('/karte/punkte', [SckMapController::class, 'storePoint'])->name('sck.map-points.store');
    Route::put('/karte/punkte/{point}', [SckMapController::class, 'updatePoint'])->name('sck.map-points.update');
    Route::delete('/karte/punkte/{point}', [SckMapController::class, 'destroyPoint'])->name('sck.map-points.destroy');

    // Address administration
    Route::get('/administration/adressen', [SckAddressAdministrationController::class, 'index'])->name('sck.administration.addresses.index');
    Route::post('/administration/adressen/{type}/{id}/koordinaten-berechnen', [SckAddressAdministrationController::class, 'calculateCoordinates'])
        ->middleware('throttle:30,1')
        ->whereIn('type', ['customer', 'stop'])
        ->whereNumber('id')
        ->name('sck.administration.addresses.calculate-coordinates');

    Route::post('/kunden/import/datev', [SckCustomerController::class, 'importDatev'])->name('sck.kunden.import-datev');
    Route::put('/kunden/{customer}/reputation', [SckCustomerController::class, 'updateReputation'])->name('sck.kunden.reputation.update');
    Route::resource('/kunden', SckCustomerController::class)->except(['create', 'edit'])->parameters(['kunden' => 'customer'])->names('sck.kunden');
    Route::resource('/stopps', SckStopTemplateController::class)->except(['create', 'edit', 'show'])->parameters(['stopps' => 'stopTemplate'])->names('sck.stopps');

    Route::get('/routen/einstellungen', [SckRouteSettingController::class, 'edit'])->name('sck.routen.settings');
    Route::put('/routen/einstellungen', [SckRouteSettingController::class, 'update'])->name('sck.routen.settings.update');
    Route::post('/routen/einstellungen/datev-snooze', [SckRouteSettingController::class, 'snooze'])->name('sck.routen.settings.snooze');
    Route::resource('/routen', SckTourController::class)->only(['index', 'create', 'store', 'show'])->parameters(['routen' => 'tour'])->names('sck.routen');
    Route::post('/routen/{tour}/status', [SckTourController::class, 'status'])->name('sck.routen.status');
    Route::post('/routen/{tour}/stopps', [SckTourController::class, 'addStop'])->name('sck.routen.stopps.store');
    Route::post('/routen/{tour}/reorder', [SckTourController::class, 'reorder'])->name('sck.routen.reorder');
    Route::post('/routen/{tour}/optimieren', [SckTourController::class, 'optimize'])->name('sck.routen.optimize');
    Route::post('/routen/draft-stopps', [SckTourController::class, 'storeDraftStop'])->name('sck.routen.draft-stopps.store');
    Route::put('/routen/draft-stopps/{draftId}', [SckTourController::class, 'updateDraftStop'])->name('sck.routen.draft-stopps.update');
    Route::post('/routen/stopps/{stop}/vorlage', [SckTourController::class, 'saveStopAsTemplate'])->name('sck.routen.stopps.template');
    Route::post('/routen/stopps/{stop}/kunde', [SckTourController::class, 'linkStopCustomer'])->name('sck.routen.stopps.customer');
    Route::put('/routen/stopps/{stop}', [SckTourController::class, 'updateStop'])->name('sck.routen.stopps.update');
    Route::delete('/routen/stopps/{stop}', [SckTourController::class, 'removeStop'])->name('sck.routen.stopps.destroy');
    Route::post('/routen/stopps/{stop}/artikel', [SckTourController::class, 'addItems'])->name('sck.routen.stopps.items');
    Route::delete('/routen/stopps/artikel/{item}', [SckTourController::class, 'removeItem'])->name('sck.routen.stopps.items.destroy');
    Route::post('/routen/stopps/{stop}/rechnung/parse', [SckTourController::class, 'parseInvoice'])->name('sck.routen.stopps.invoice.parse');
    Route::post('/routen/stopps/{stop}/rechnung/artikel', [SckTourController::class, 'createInvoiceItem'])->name('sck.routen.stopps.invoice.items.store');
    Route::post('/routen/stopps/{stop}/rechnung/commit', [SckTourController::class, 'commitInvoice'])->name('sck.routen.stopps.invoice.commit');
    Route::get('/routen/{tour}/export/csv', [SckTourController::class, 'exportCsv'])->name('sck.routen.export.csv');
    Route::get('/routen/{tour}/export/gpx', [SckTourController::class, 'exportGpx'])->name('sck.routen.export.gpx');
    Route::get('/routen/{tour}/export/pdf', [SckTourController::class, 'exportPdf'])->name('sck.routen.export.pdf');
    Route::get('/routen/{tour}/export/datev', [SckTourController::class, 'exportDatev'])->name('sck.routen.export.datev');
    Route::get('/routen/{tour}/drucken', [SckTourController::class, 'print'])->name('sck.routen.print');

    Route::resource('/wochenplanung', SckWeeklyPlanController::class)->only(['index', 'create', 'store', 'show', 'update'])->parameters(['wochenplanung' => 'weeklyPlan'])->names('sck.wochenplanung');
    Route::put('/wochenplanung/{weeklyPlan}/stopps/{stop}', [SckWeeklyPlanController::class, 'updateStop'])->name('sck.wochenplanung.stopps.update');
    Route::post('/wochenplanung/{weeklyPlan}/berechnen', [SckWeeklyPlanController::class, 'generate'])->name('sck.wochenplanung.generate');
    Route::put('/wochenplanung/{weeklyPlan}/kandidaten/{candidate}', [SckWeeklyPlanController::class, 'reorder'])->name('sck.wochenplanung.candidates.reorder');
    Route::post('/wochenplanung/{weeklyPlan}/kandidaten/{candidate}/neu-berechnen', [SckWeeklyPlanController::class, 'recalculate'])->name('sck.wochenplanung.candidates.recalculate');
    Route::post('/wochenplanung/{weeklyPlan}/kandidaten/{candidate}/uebernehmen', [SckWeeklyPlanController::class, 'materialize'])->name('sck.wochenplanung.candidates.materialize');
    Route::get('/wochenplanung/{weeklyPlan}/export/csv', [SckWeeklyPlanController::class, 'exportCsv'])->name('sck.wochenplanung.export.csv');
    Route::get('/wochenplanung/{weeklyPlan}/export/pdf', [SckWeeklyPlanController::class, 'exportPdf'])->name('sck.wochenplanung.export.pdf');
    Route::get('/wochenplanung/{weeklyPlan}/export/datev', [SckWeeklyPlanController::class, 'exportDatev'])->name('sck.wochenplanung.export.datev');

    Route::post('/routen/stopps/{stop}/fotos', [SckMediaController::class, 'store'])->name('sck.media.store');
    Route::get('/routen/fotos/{photo}', [SckMediaController::class, 'show'])->name('sck.media.show');
    Route::get('/routen/fotos/{photo}/thumbnail', [SckMediaController::class, 'thumbnail'])->name('sck.media.thumbnail');
    Route::delete('/routen/fotos/{photo}', [SckMediaController::class, 'destroy'])->name('sck.media.destroy');
    Route::post('/routen/fotos/{photo}/restore', [SckMediaController::class, 'restore'])->name('sck.media.restore');
    Route::post('/kommentare', [SckCommentController::class, 'store'])->name('sck.comments.store');
    Route::put('/kommentare/{comment}', [SckCommentController::class, 'update'])->name('sck.comments.update');
    Route::delete('/kommentare/{comment}', [SckCommentController::class, 'destroy'])->name('sck.comments.destroy');
    Route::post('/kommentare/{comment}/restore', [SckCommentController::class, 'restore'])->name('sck.comments.restore');

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
    Route::post('/lager/scan/clear-logs', [SckWarehouseController::class, 'clearLogs'])->name('sck.lager.scan.clear_logs');
    
    // Article Detail Route (landing page for scanning outside the app)
    Route::get('/lager/artikel/{neue_artikelnummer}', [SckWarehouseController::class, 'show'])->name('sck.lager.artikel');

    // Bulk actions
    Route::post('/lager/bulk-selection', [SckWarehouseController::class, 'storeBulkSelection'])->name('sck.lager.bulk-selection');
    Route::post('/lager/bulk-destroy', [SckWarehouseController::class, 'bulkDestroy'])->name('sck.lager.bulk-destroy');
    Route::post('/lager/bulk-export', [SckWarehouseController::class, 'bulkExport'])->name('sck.lager.bulk-export');
    Route::post('/lager/bulk-export-datev', [SckWarehouseController::class, 'bulkExportDatev'])->name('sck.lager.bulk-export-datev');
    Route::post('/lager/bulk-update-stock', [SckWarehouseController::class, 'bulkUpdateStock'])->name('sck.lager.bulk-update-stock');
    Route::post('/lager/toggle-datev-exported/{id}', [SckWarehouseController::class, 'toggleDatevExported'])->name('sck.lager.toggle-datev-exported');
    Route::post('/lager/bulk-toggle-datev-exported', [SckWarehouseController::class, 'bulkToggleDatevExported'])->name('sck.lager.bulk-toggle-datev-exported');
    Route::post('/lager/toggle-datev-status-session', [SckWarehouseController::class, 'storeDatevStatusToggle'])->name('sck.lager.toggle-datev-status-session');

    // Invoice upload & deduction
    Route::post('/lager/parse-invoice', [SckWarehouseController::class, 'parseInvoice'])->name('sck.lager.parse-invoice');
    Route::post('/lager/process-invoice-deduction', [SckWarehouseController::class, 'processInvoiceDeduction'])->name('sck.lager.process-invoice-deduction');
});
