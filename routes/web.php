<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Finance\ExpenseCategoryController;
use App\Http\Controllers\Finance\IncomeCategoryController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectCategoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Projects\FieldDefinitionController;
use App\Http\Controllers\TraineeController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/public.php';

// 'throttle:admin-write' is the backstop behind CSRF and the permission gates:
// a stolen session still cannot hammer the write endpoints.
Route::middleware(['auth', 'verified', 'throttle:admin-write'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

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

    // Expense entries (static segments must come before the {expense} wildcard)
    Route::get('expense', [ExpenseController::class, 'index'])->name('expense.index');
    Route::get('expense/create', [ExpenseController::class, 'create'])->name('expense.create');
    Route::post('expense', [ExpenseController::class, 'store'])->name('expense.store');

    // Expense categories (before expense/{expense} wildcard)
    Route::get('expense/categories', [ExpenseCategoryController::class, 'index'])->name('expense.categories');
    Route::get('expense/categories/data', [ExpenseCategoryController::class, 'data'])->name('expense.categories.data');
    Route::get('expense/categories/{category}', [ExpenseCategoryController::class, 'show'])->name('expense.categories.show');
    Route::post('expense/categories', [ExpenseCategoryController::class, 'store'])->name('expense.categories.store');
    Route::put('expense/categories/{category}', [ExpenseCategoryController::class, 'update'])->name('expense.categories.update');
    Route::delete('expense/categories/{category}', [ExpenseCategoryController::class, 'destroy'])->name('expense.categories.destroy');

    Route::get('expense/{expense}', [ExpenseController::class, 'show'])->name('expense.show');
    Route::get('expense/{expense}/edit', [ExpenseController::class, 'edit'])->name('expense.edit');
    Route::put('expense/{expense}', [ExpenseController::class, 'update'])->name('expense.update');
    Route::delete('expense/{expense}', [ExpenseController::class, 'destroy'])->name('expense.destroy');
    Route::post('expense/{expense}/approve', [ExpenseController::class, 'approve'])->name('expense.approve');
    Route::post('expense/{expense}/reimburse', [ExpenseController::class, 'reimburse'])->name('expense.reimburse');

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
    // Returns the custom fields for a project, so the registration modal can
    // rebuild itself when the project is changed.
    Route::get('projects/trainees/fields', [TraineeController::class, 'fields'])->name('projects.trainees.fields');
    Route::get('projects/trainees/data', [TraineeController::class, 'data'])->name('projects.trainees.data');
    Route::post('projects/trainees', [TraineeController::class, 'store'])->name('projects.trainees.store');
    Route::post('projects/trainees/{trainee}', [TraineeController::class, 'update'])->name('projects.trainees.update');
    Route::delete('projects/trainees/{trainee}', [TraineeController::class, 'destroy'])->name('projects.trainees.destroy');

    // Registration form builder — the custom fields a project asks for.
    // {project} is constrained to digits so it cannot swallow the static
    // projects/categories and projects/trainees paths above.
    Route::prefix('projects/{project}')->whereNumber('project')->name('projects.fields.')->group(function () {
        Route::get('fields', [FieldDefinitionController::class, 'index'])->name('index');
        Route::post('fields', [FieldDefinitionController::class, 'store'])->name('store');
        Route::post('fields/reorder', [FieldDefinitionController::class, 'reorder'])->name('reorder');
        Route::put('fields/{field}', [FieldDefinitionController::class, 'update'])->name('update');
        Route::delete('fields/{field}', [FieldDefinitionController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/settings.php';

// CMS Routes
Route::middleware(['auth', 'verified', 'throttle:admin-write'])->prefix('admin/cms')->name('admin.')->group(function () {
    Route::get('page-sections/data', [\App\Http\Controllers\Admin\PageSectionController::class, 'data'])->name('page-sections.data');
    Route::resource('page-sections', \App\Http\Controllers\Admin\PageSectionController::class)->except(['show']);
    Route::get('team-members/data', [\App\Http\Controllers\Admin\TeamMemberController::class, 'data'])->name('team-members.data');
    Route::resource('team-members', \App\Http\Controllers\Admin\TeamMemberController::class)->except(['show']);
    Route::get('portfolio/data', [\App\Http\Controllers\Admin\PortfolioItemController::class, 'data'])->name('portfolio.data');
    Route::resource('portfolio', \App\Http\Controllers\Admin\PortfolioItemController::class)->except(['show']);
    Route::get('contact-settings', [\App\Http\Controllers\Admin\ContactSettingController::class, 'edit'])->name('contact-settings.edit');
    Route::put('contact-settings', [\App\Http\Controllers\Admin\ContactSettingController::class, 'update'])->name('contact-settings.update');

    // Website traffic, recorded in this app
    Route::get('analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');

    // SEO and Google analytics tooling
    Route::get('seo', [\App\Http\Controllers\Admin\SeoController::class, 'settings'])->name('seo.settings');
    Route::post('seo', [\App\Http\Controllers\Admin\SeoController::class, 'updateSettings'])->name('seo.settings.update');
    Route::get('seo/pages', [\App\Http\Controllers\Admin\SeoController::class, 'pages'])->name('seo.pages');
    Route::post('seo/pages', [\App\Http\Controllers\Admin\SeoController::class, 'updatePage'])->name('seo.pages.update');
});

// Asset Management Routes
Route::middleware(['auth', 'verified', 'throttle:admin-write'])->group(function () {
    Route::get('assets/data', [\App\Http\Controllers\AssetController::class, 'data'])->name('assets.data');
    Route::resource('assets', \App\Http\Controllers\AssetController::class);
});

// Course Management Routes
Route::middleware(['auth', 'verified', 'throttle:admin-write'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('courses/data', [\App\Http\Controllers\Admin\CourseController::class, 'data'])->name('courses.data');
    Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class)->except(['show']);
    Route::get('course-categories/data', [\App\Http\Controllers\Admin\CourseCategoryController::class, 'data'])->name('course-categories.data');
    Route::resource('course-categories', \App\Http\Controllers\Admin\CourseCategoryController::class)->except(['show']);
});

// Service Management Routes
Route::middleware(['auth', 'verified', 'throttle:admin-write'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('services/data', [\App\Http\Controllers\Admin\ServiceController::class, 'data'])->name('services.data');
    Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class)->except(['show']);
    Route::get('service-categories/data', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'data'])->name('service-categories.data');
    Route::resource('service-categories', \App\Http\Controllers\Admin\ServiceCategoryController::class)->except(['show']);
});

// Notification rules — who gets told about what
Route::middleware(['auth', 'verified', 'throttle:admin-write'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('notification-rules', [\App\Http\Controllers\Admin\NotificationRuleController::class, 'index'])->name('notification-rules.index');
    Route::post('notification-rules', [\App\Http\Controllers\Admin\NotificationRuleController::class, 'store'])->name('notification-rules.store');
    Route::put('notification-rules/{rule}', [\App\Http\Controllers\Admin\NotificationRuleController::class, 'update'])->name('notification-rules.update');
    Route::post('notification-rules/{rule}/toggle', [\App\Http\Controllers\Admin\NotificationRuleController::class, 'toggle'])->name('notification-rules.toggle');
    Route::delete('notification-rules/{rule}', [\App\Http\Controllers\Admin\NotificationRuleController::class, 'destroy'])->name('notification-rules.destroy');
});

// Roles & permissions — superadmin only, enforced by the roles.manage gate
Route::middleware(['auth', 'verified', 'throttle:admin-write'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
    Route::post('roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
    Route::put('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
    Route::put('roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'updatePermissions'])->name('roles.permissions');
    Route::delete('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
});

// User accounts — gated on users.manage; superadmin rules enforced in the controller
Route::middleware(['auth', 'verified', 'throttle:admin-write'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/data', [\App\Http\Controllers\Admin\UserController::class, 'data'])->name('users.data');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::put('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::post('users/{user}/toggle', [\App\Http\Controllers\Admin\UserController::class, 'toggle'])->name('users.toggle');
    Route::delete('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
});
