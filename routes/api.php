<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BudgetCategoryController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\RecurringTransactionController;
use App\Http\Controllers\Api\SavingTargetController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('members', MemberController::class);
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('budget-categories', BudgetCategoryController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('recurring-transactions', RecurringTransactionController::class);
    Route::apiResource('savings', SavingTargetController::class);

    Route::post('transfer', [TransferController::class, 'store']);
    Route::get('dashboard', [DashboardController::class, 'index']);
});
