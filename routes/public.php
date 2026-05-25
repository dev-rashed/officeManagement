<?php

use App\Http\Controllers\Public\CourseController;
use App\Http\Controllers\Public\ServiceController;
use Illuminate\Support\Facades\Route;

// Public pages
Route::view('/', 'pages.public.home')->name('home');
Route::view('/about', 'pages.public.about')->name('about');
Route::view('/mission', 'pages.public.mission')->name('mission');
Route::view('/vision', 'pages.public.vision')->name('vision');
Route::view('/team', 'pages.public.team')->name('team');
Route::view('/contact', 'pages.public.contact')->name('contact');
Route::view('/work', 'pages.public.work')->name('work');

// Courses
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{slug}', [CourseController::class, 'show'])->name('courses.show');

// Services
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
