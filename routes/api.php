<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MotorcycleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\CiReportController;
use App\Http\Controllers\Api\Auth\LoginController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::post('/createmotor', function (Request $request) {
//     return response()->json(['message' => 'Success']);
// });
Route::resource('motorcycle', MotorcycleController::class);
Route::resource('account', UserController::class);
Route::resource('application', ApplicationFormController::class);
Route::resource('report', CiReportController::class);
Route::post('/login', [LoginController::class, 'login']);