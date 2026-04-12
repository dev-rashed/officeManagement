<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use Illuminate\Http\JsonResponse;

class IncomeCategoryApiController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = IncomeCategory::query()
            ->where('status', IncomeCategory::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        return response()->json($categories);
    }
}
