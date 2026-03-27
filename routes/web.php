<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MexiCalculatorController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\FragebogenController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('lang/{lang}', function ($lang) {
    App::setLocale($lang);
    Session::put('locale', $lang);
    return redirect()->back();
})->name('lang');

Route::get('/', [PageController::class, 'info'])->name('info');

Route::get('/mexi-calculator', [MexiCalculatorController::class, 'index'])->name('mexi-calculator.index');
Route::post('/mexi-calculator/calculate', [MexiCalculatorController::class, 'calculate'])->name('mexi-calculator.calculate');
Route::get('/mexi-calculator/results', [MexiCalculatorController::class, 'showResults'])->name('mexi-calculator.show-results');

Route::get('/info', [PageController::class, 'info']);
Route::get('/games', [GameController::class, 'index'])->name('games.index');

Route::get('/tools/word-count', [ToolController::class, 'wordCount'])->name('tools.word-count');
Route::get('/tools/spinning-crown', [ToolController::class, 'spinningCrown'])->name('tools.spinning-crown');
Route::get('/tools/spinning-crown-test', [ToolController::class, 'spinningCrownTest'])->name('tools.spinning-crown-test');
Route::get('/tools/player-selection', [ToolController::class, 'playerSelection'])->name('tools.player-selection');
Route::get('/tools/hi-low', [ToolController::class, 'hiLow'])->name('tools.hi-low');
Route::get('/tools/ticking-bomb', [ToolController::class, 'tickingBomb'])->name('tools.ticking-bomb');
Route::get('/tools/russian-roulette', [ToolController::class, 'russianRoulette'])->name('tools.russian-roulette');
Route::get('/tools/snake-pit', [ToolController::class, 'snakePit'])->name('tools.snake-pit');

Route::post('/tools/players/update', [ToolController::class, 'updatePlayers'])->name('tools.players.update');
Route::post('/tools/save-winner', [ToolController::class, 'saveWinner'])->name('tools.save-winner');
Route::post('/tools/toggle-lms', [ToolController::class, 'toggleLms'])->name('tools.toggle-lms');
Route::post('/tools/reset-lms', [ToolController::class, 'resetLms'])->name('tools.reset-lms');

Route::get('/fragebogen', [FragebogenController::class, 'index'])->name('fragebogen.index');
Route::post('/fragebogen/save', [FragebogenController::class, 'store'])->name('fragebogen.store');
Route::get('/fragebogen/result/{id}', [FragebogenController::class, 'show'])->name('fragebogen.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/apply-admin', [ProfileController::class, 'applyAdmin'])->name('profile.apply-admin');
});

require __DIR__ . '/auth.php';
