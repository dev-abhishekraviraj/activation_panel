<?php

use App\Http\Controllers\Api\ApiClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('device/create', [ApiClientController::class, 'create_device']);
Route::post('login', [ApiClientController::class, 'login']);
Route::post('generate_qr', [ApiClientController::class, 'generate_qr']);

Route::middleware(['auth:sanctum'])->group(function () {
   Route::post('playlist/list', [ApiClientController::class, 'get_playlist']);
   Route::post('get_file_data', [ApiClientController::class, 'get_file_data']);
});

