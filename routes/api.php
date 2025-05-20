<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GpsDataController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/gps-data', [GpsDataController::class, 'store']);
Route::get('/gps-data', [GpsDataController::class, 'getLatest']);
