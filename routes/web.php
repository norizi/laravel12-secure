<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('throttle:5,1')->get('/test', function () {
    return 'OK';
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/users', [App\Http\Controllers\HomeController::class, 'users'])->name('users');
Route::get('/users/{id}', [App\Http\Controllers\HomeController::class, 'destroya'])
->name('users.destroya');
Route::delete('/users/{id}', [App\Http\Controllers\HomeController::class, 'destroyb'])
->name('users.destroyb');
Route::post('/users/store', [App\Http\Controllers\HomeController::class, 'store'])
->name('users.store');
Route::post('/users/update', [App\Http\Controllers\HomeController::class, 'update'])
->name('users.update');
