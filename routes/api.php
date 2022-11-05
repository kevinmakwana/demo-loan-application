<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LoanController;
use App\Http\Controllers\API\LoanRepaymentController;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('loans')->group(function() {
        Route::get('/', [LoanController::class, 'index']);
        Route::post('create', [LoanController::class, 'store']);
        Route::put('approve-loan/{loan}', [LoanController::class, 'changeStatus'])->middleware('checkIfAdmin');
        Route::get('/{id}', [LoanController::class, 'show']);

        Route::put('/{loanId}/installment/{loanRepaymentId}', [LoanRepaymentController::class, 'payWeeklyInstallments']);
    });
});