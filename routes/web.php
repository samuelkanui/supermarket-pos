<?php

use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (app()->bound('tenant')) {
        return redirect()->route('dashboard');
    }
    return inertia('Welcome');
})->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::inertia('dashboard', 'Dashboard')->name('dashboard');

        // Inventory and Catalog management
        Route::resource('products', \App\Http\Controllers\Inventory\ProductController::class);
        Route::resource('categories', \App\Http\Controllers\Inventory\CategoryController::class);
        Route::resource('suppliers', \App\Http\Controllers\Inventory\SupplierController::class);

        // Stock counts and adjustments
        Route::get('stocks', [\App\Http\Controllers\Inventory\StockController::class, 'index'])->name('stocks.index');
        Route::post('stocks/adjust', [\App\Http\Controllers\Inventory\StockController::class, 'adjust'])->name('stocks.adjust');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
});

require __DIR__.'/settings.php';
