<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('auth')->group((function() {
    Route::post('/login', LoginController::class);
}));
