<?php

use App\Http\Controllers\GuestController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeddingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['web', 'auth'])->group(function (): void {
    Route::apiResource('users', UserController::class);
    Route::apiResource('weddings', WeddingController::class);
    Route::apiResource('guests', GuestController::class);
    Route::patch('guests/{guest}/tags', [GuestController::class, 'updateTags'])->name('guests.updateTags');
    Route::post('guests/import', [ImportController::class, 'import'])->name('guests.import');

    Route::get('metrics', MetricsController::class)->name('metrics.index');
});

// Rotas públicas de convite (não precisam de autenticação)
Route::prefix('v1')->group(function (): void {
    Route::post('invitations/confirm', [InvitationController::class, 'confirm'])->name('invitations.confirm');
    Route::get('invitations/lookup', [InvitationController::class, 'lookup'])->name('invitations.lookup');
});
