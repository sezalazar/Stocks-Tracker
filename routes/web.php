<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OptionsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');


    // Stock Details
    Route::get('/stock-data/{symbol}', [StockController::class, 'show'])->name('stock.show');

    //Options
    Route::post('/options/process', [OptionsController::class, 'processMarketData'])->name('options.process');
});

require __DIR__.'/auth.php';
