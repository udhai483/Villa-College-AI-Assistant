<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('/login', App\Livewire\Auth\Login::class)
    ->name('login')
    ->middleware('guest');

Route::get('/auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])
    ->name('auth.google');

Route::get('/auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback');

Route::post('/logout', [App\Http\Controllers\Auth\GoogleController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::get('/chat', App\Livewire\Chat\ChatInterface::class)
    ->name('chat')
    ->middleware('auth');
