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
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\BackupController;

// Route::post('/register', [AuthController::class, 'register'])->name('register');
// Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    // Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // Route::get('/user', [AuthController::class, 'user'])->name('user');
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('members', MemberController::class);
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('budget-categories', BudgetCategoryController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('recurring-transactions', RecurringTransactionController::class);
    Route::apiResource('savings', SavingTargetController::class);

    Route::post('transfer', [TransferController::class, 'store']);
    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::get('/report/monthly', [ReportController::class, 'monthly']);
    Route::get('/report/yearly', [ReportController::class, 'yearly']);

    Route::get('/backup/export', [BackupController::class, 'export']);
    Route::post('/backup/import', [BackupController::class, 'import']);
});
