<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('tickets.index')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

    Route::middleware('tenant.ticket')->group(function () {
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
            ->whereNumber('ticket')
            ->name('tickets.show');
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])
            ->whereNumber('ticket')
            ->name('tickets.status.update');
    });
});
