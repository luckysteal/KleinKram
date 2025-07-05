<?php

use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\BarController;
use App\Http\Controllers\Admin\DrinkController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/page', [PageController::class, 'edit'])->name('page.edit');
    Route::put('/page', [PageController::class, 'update'])->name('page.update');

    Route::resource('bars', BarController::class);
    Route::resource('drinks', DrinkController::class);
});
