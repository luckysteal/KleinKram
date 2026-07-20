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
