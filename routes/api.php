<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiRfidArduino;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('scan/{idkartu}', [ApiRfidArduino::class, 'getArduino']);
Route::get('sinkron', [ApiRfidArduino::class, 'getJsonBudutwj']);
Route::get('kirim', [ApiRfidArduino::class, 'sendToServer']);
