<?php

use App\Http\Controllers\Api\IncomeCategoryApiController;
use Illuminate\Support\Facades\Route;

Route::get('income/categories', [IncomeCategoryApiController::class, 'index']);
