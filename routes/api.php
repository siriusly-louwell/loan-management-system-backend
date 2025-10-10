<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MotorcycleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\CiReportController;
use App\Http\Controllers\PaymentController;
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

// ? Motorcycle Routes
Route::get('/motorcycle/count', [MotorcycleController::class, 'count']);
Route::patch('/motorcycle/{motorcycle}', [MotorcycleController::class, 'update']);
Route::get('/motorcycle/count', [MotorcycleController::class, 'count']);
Route::resource('motorcycle', MotorcycleController::class);

// ? Account Routes
Route::resource('account', UserController::class);
// Route::middleware('auth:sanctum')->get('/account', [UserController::class, 'account']);
Route::post('/login', [LoginController::class, 'login']);

// ? Application Routes
Route::get('/application/count', [ApplicationFormController::class, 'count']);
Route::get('/payment/count', [PaymentController::class, 'count']);
Route::resource('application', ApplicationFormController::class);
Route::resource('report', CiReportController::class);
Route::resource('payment', PaymentController::class);
