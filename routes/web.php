<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('auth')->group((function() {
    Route::post('/login', LoginController::class);
    Route::post('/register', RegisterController::class);
}));

