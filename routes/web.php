<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity.log');
    Route::get('activity-log/data', [ActivityLogController::class, 'data'])->name('activity.log.data');

    Route::get('income', [IncomeController::class, 'index'])->name('income.index');
    Route::get('income/categories', [IncomeController::class, 'categories'])->name('income.categories');
    Route::post('income/categories', [IncomeController::class, 'storeCategory'])->name('income.categories.store');
    Route::put('income/categories/{category}', [IncomeController::class, 'updateCategory'])->name('income.categories.update');
    Route::delete('income/categories/{category}', [IncomeController::class, 'destroyCategory'])->name('income.categories.destroy');
    Route::get('income/create', [IncomeController::class, 'create'])->name('income.create');
    Route::post('income', [IncomeController::class, 'store'])->name('income.store');
    Route::get('income/{income}', [IncomeController::class, 'show'])->name('income.show');
    Route::get('income/{income}/edit', [IncomeController::class, 'edit'])->name('income.edit');
    Route::put('income/{income}', [IncomeController::class, 'update'])->name('income.update');
    Route::delete('income/{income}', [IncomeController::class, 'destroy'])->name('income.destroy');
    Route::post('income/{income}/approve', [IncomeController::class, 'approve'])->name('income.approve');

    Route::get('expense', [ExpenseController::class, 'index'])->name('expense.index');
    Route::get('expense/create', [ExpenseController::class, 'create'])->name('expense.create');
    Route::post('expense', [ExpenseController::class, 'store'])->name('expense.store');
    Route::get('expense/{expense}', [ExpenseController::class, 'show'])->name('expense.show');
    Route::get('expense/{expense}/edit', [ExpenseController::class, 'edit'])->name('expense.edit');
    Route::put('expense/{expense}', [ExpenseController::class, 'update'])->name('expense.update');
    Route::delete('expense/{expense}', [ExpenseController::class, 'destroy'])->name('expense.destroy');
    Route::post('expense/{expense}/approve', [ExpenseController::class, 'approve'])->name('expense.approve');
});

require __DIR__.'/settings.php';
