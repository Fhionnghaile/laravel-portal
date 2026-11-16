<?php

use App\Http\Controllers\CredentialRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/requests', [CredentialRequestController::class, 'index'])->name('requests.index');
    Route::post('/requests', [CredentialRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{credentialRequest}', [CredentialRequestController::class, 'show'])
        ->name('requests.show');
    Route::post('/requests/{credentialRequest}/verify', [CredentialRequestController::class, 'verify'])
        ->name('requests.verify');
});
