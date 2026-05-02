<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Finance\IncomeCategoryController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ProjectCategoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TraineeController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity.log');
    Route::get('activity-log/data', [ActivityLogController::class, 'data'])->name('activity.log.data');

    // Income entries (static segments must come before {income} wildcard)
    Route::get('income', [IncomeController::class, 'index'])->name('income.index');
    Route::get('income/data', [IncomeController::class, 'indexData'])->name('income.data');
    Route::get('income/create', [IncomeController::class, 'create'])->name('income.create');
    Route::post('income', [IncomeController::class, 'store'])->name('income.store');

    // Income categories (before income/{income} wildcard)
    Route::get('income/categories', [IncomeCategoryController::class, 'index'])->name('income.categories');
    Route::get('income/categories/data', [IncomeCategoryController::class, 'data'])->name('income.categories.data');
    Route::get('income/categories/{category}', [IncomeCategoryController::class, 'show'])->name('income.categories.show');
    Route::post('income/categories', [IncomeCategoryController::class, 'store'])->name('income.categories.store');
    Route::put('income/categories/{category}', [IncomeCategoryController::class, 'update'])->name('income.categories.update');
    Route::delete('income/categories/{category}', [IncomeCategoryController::class, 'destroy'])->name('income.categories.destroy');

    // Income wildcard routes
    Route::get('income/{income}', [IncomeController::class, 'show'])->name('income.show');
    Route::get('income/{income}/edit', [IncomeController::class, 'edit'])->name('income.edit');
    Route::put('income/{income}', [IncomeController::class, 'update'])->name('income.update');
    Route::delete('income/{income}', [IncomeController::class, 'destroy'])->name('income.destroy');
    Route::post('income/{income}/approve', [IncomeController::class, 'approve'])->name('income.approve');

    // Expense entries
    Route::get('expense', [ExpenseController::class, 'index'])->name('expense.index');
    Route::get('expense/create', [ExpenseController::class, 'create'])->name('expense.create');
    Route::post('expense', [ExpenseController::class, 'store'])->name('expense.store');
    Route::get('expense/{expense}', [ExpenseController::class, 'show'])->name('expense.show');
    Route::get('expense/{expense}/edit', [ExpenseController::class, 'edit'])->name('expense.edit');
    Route::put('expense/{expense}', [ExpenseController::class, 'update'])->name('expense.update');
    Route::delete('expense/{expense}', [ExpenseController::class, 'destroy'])->name('expense.destroy');
    Route::post('expense/{expense}/approve', [ExpenseController::class, 'approve'])->name('expense.approve');

    // Projects
    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/data', [ProjectController::class, 'data'])->name('projects.data');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('projects/categories', [ProjectCategoryController::class, 'index'])->name('projects.categories.index');
    Route::get('projects/categories/data', [ProjectCategoryController::class, 'data'])->name('projects.categories.data');
    Route::post('projects/categories', [ProjectCategoryController::class, 'store'])->name('projects.categories.store');
    Route::put('projects/categories/{category}', [ProjectCategoryController::class, 'update'])->name('projects.categories.update');
    Route::delete('projects/categories/{category}', [ProjectCategoryController::class, 'destroy'])->name('projects.categories.destroy');

    Route::get('projects/trainees', [TraineeController::class, 'index'])->name('projects.trainees.index');
    Route::get('projects/trainees/data', [TraineeController::class, 'data'])->name('projects.trainees.data');
    Route::post('projects/trainees', [TraineeController::class, 'store'])->name('projects.trainees.store');
    Route::post('projects/trainees/{trainee}', [TraineeController::class, 'update'])->name('projects.trainees.update');
    Route::delete('projects/trainees/{trainee}', [TraineeController::class, 'destroy'])->name('projects.trainees.destroy');
});

require __DIR__.'/settings.php';
