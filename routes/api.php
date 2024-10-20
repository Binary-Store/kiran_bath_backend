<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

Route::post('users/signup', [UserController::class, 'signUp']);
Route::post('users/login', [UserController::class, 'login']);
Route::post('users/verify-otp', [UserController::class, 'verifyOtp']);
