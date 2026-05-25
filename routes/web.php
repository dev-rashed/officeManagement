<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Finance\IncomeCategoryController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ProjectCategoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TraineeController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/public.php';

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

// CMS Routes
Route::middleware(['auth', 'verified'])->prefix('admin/cms')->group(function () {
    Route::resource('page-sections', \App\Http\Controllers\Admin\PageSectionController::class);
    Route::get('page-sections/data', [\App\Http\Controllers\Admin\PageSectionController::class, 'data'])->name('page-sections.data');
    Route::resource('team-members', \App\Http\Controllers\Admin\TeamMemberController::class);
    Route::get('team-members/data', [\App\Http\Controllers\Admin\TeamMemberController::class, 'data'])->name('team-members.data');
    Route::resource('portfolio', \App\Http\Controllers\Admin\PortfolioItemController::class);
    Route::get('portfolio/data', [\App\Http\Controllers\Admin\PortfolioItemController::class, 'data'])->name('portfolio.data');
    Route::get('contact-settings', [\App\Http\Controllers\Admin\ContactSettingController::class, 'edit'])->name('contact-settings.edit');
    Route::put('contact-settings', [\App\Http\Controllers\Admin\ContactSettingController::class, 'update'])->name('contact-settings.update');
});

// Asset Management Routes
Route::middleware(['auth', 'verified'])->resource('assets', \App\Http\Controllers\AssetController::class);
Route::get('assets/data', [\App\Http\Controllers\AssetController::class, 'data'])->name('assets.data');

// Course Management Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class);
    Route::get('courses/data', [\App\Http\Controllers\Admin\CourseController::class, 'data'])->name('admin.courses.data');
    Route::resource('course-categories', \App\Http\Controllers\Admin\CourseCategoryController::class);
    Route::get('course-categories/data', [\App\Http\Controllers\Admin\CourseCategoryController::class, 'data'])->name('admin.course-categories.data');
});

// Service Management Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);
    Route::get('services/data', [\App\Http\Controllers\Admin\ServiceController::class, 'data'])->name('admin.services.data');
    Route::resource('service-categories', \App\Http\Controllers\Admin\ServiceCategoryController::class);
    Route::get('service-categories/data', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'data'])->name('admin.service-categories.data');
});
