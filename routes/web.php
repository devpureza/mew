<?php

use App\Http\Controllers\InvitationController;
use App\Http\Controllers\AuthController;
use App\Models\Wedding;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/referencias', 'references')->name('references');
Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/confirmacao', [InvitationController::class, 'showForm'])->name('invitations.form');
Route::post('/confirmacao', [InvitationController::class, 'confirmWeb'])->name('invitations.confirm.web');

Route::middleware(['auth', 'role:superadmin'])->prefix('cms')->group(function () {
    Route::view('/', 'cms.dashboard')->name('cms');
    Route::view('/usuarios', 'cms.users')->name('cms.users');
    Route::view('/casamentos', 'cms.weddings')->name('cms.weddings');
    Route::view('/convidados', 'cms.guests-select')->name('cms.guests');
    Route::get('/convidados/{wedding}', function (Wedding $wedding) {
        return view('cms.guests', ['weddingId' => $wedding->id]);
    })->name('cms.guests.wedding');
});

Route::middleware(['auth', 'role:couple'])->prefix('cms')->group(function () {
    Route::get('/meu-casamento', function () {
        $wedding = auth()->user()->weddings()->with('couples')->first();
        abort_unless($wedding, 404, 'Nenhum casamento vinculado.');
        return view('cms.couple', ['wedding' => $wedding]);
    })->name('cms.couple');
    
    Route::get('/marcacoes', function () {
        $wedding = auth()->user()->weddings()->with('couples')->first();
        abort_unless($wedding, 404, 'Nenhum casamento vinculado.');
        return view('cms.tags', ['wedding' => $wedding]);
    })->name('cms.tags');
    
    Route::get('/importar', function () {
        $wedding = auth()->user()->weddings()->with('couples')->first();
        abort_unless($wedding, 404, 'Nenhum casamento vinculado.');
        return view('cms.import', ['wedding' => $wedding]);
    })->name('cms.import');
});
