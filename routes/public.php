<?php

use App\Http\Controllers\Public\CourseController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ServiceController;
use Illuminate\Support\Facades\Route;

// Public pages
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/mission', [PageController::class, 'mission'])->name('mission');
Route::get('/vision', [PageController::class, 'vision'])->name('vision');
Route::get('/team', [PageController::class, 'team'])->name('team');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::redirect('/work', '/portfolio', 301);
Route::get('/portfolio', [PageController::class, 'portfolio'])->name('portfolio');

// Courses
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{slug}', [CourseController::class, 'show'])->name('courses.show');

// Services
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
