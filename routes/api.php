<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MotorcycleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\CiReportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\CreditHistoryController;
use App\Http\Controllers\ScheduleController;

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


/*
|--------------------------------------------------------------------------
| MOTORCYCLE ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('motorcycle')->group(function () {
    Route::get('/count', [MotorcycleController::class, 'count']);
});

Route::resource('motorcycle', MotorcycleController::class);


/*
|--------------------------------------------------------------------------
| ACCOUNT ROUTES
|--------------------------------------------------------------------------
*/
Route::resource('account', UserController::class);

Route::post('/login', [LoginController::class, 'login']);


/*
|--------------------------------------------------------------------------
| APPLICATION ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('application')->group(function () {
    Route::get('/count', [ApplicationFormController::class, 'count']);
});

Route::resource('application', ApplicationFormController::class);


/*
|--------------------------------------------------------------------------
| PAYMENT ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('payment')->group(function () {
    Route::get('/count', [PaymentController::class, 'count']);
});

Route::resource('payment', PaymentController::class);


/*
|--------------------------------------------------------------------------
| CREDIT ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('credit')->group(function () {
    Route::get('/score', [CreditHistoryController::class, 'score']);
});

Route::resource('credit', CreditHistoryController::class);


/*
|--------------------------------------------------------------------------
| CI REPORT ROUTES
|--------------------------------------------------------------------------
*/
Route::resource('report', CiReportController::class);


/*
|--------------------------------------------------------------------------
| SCHEDULE ROUTES
|--------------------------------------------------------------------------
*/
Route::resource('schedule', ScheduleController::class);
