<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use App\Models\IncomeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class IncomeCategoryController extends Controller
{
    public function index()
    {
        $totalCategories = IncomeCategory::count();
        $activeCategories = IncomeCategory::where('status', IncomeCategory::STATUS_ACTIVE)->count();
        $inactiveCategories = IncomeCategory::where('status', IncomeCategory::STATUS_INACTIVE)->count();

        return view('pages.finance.income.categories.index', compact(
            'totalCategories',
            'activeCategories',
            'inactiveCategories',
        ));
    }

    public function data(Request $request)
    {
        $query = IncomeCategory::query()->orderBy('name');

        $totalRecords = IncomeCategory::count();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 20);

        $categories = $query->skip($start)->take($length)->get();

        $data = $categories->map(function (IncomeCategory $category) {
            $entriesCount = IncomeEntry::where('source_category', $category->name)->count();
            $totalAmount = (float) IncomeEntry::where('source_category', $category->name)->sum('amount');
            $latestDate = IncomeEntry::where('source_category', $category->name)->max('date');

            $statusBadge = '<span class="category-status-badge '
                .($category->isActive() ? 'is-active' : 'is-inactive')
                .'"><span class="category-status-dot"></span>'.ucfirst($category->status).'</span>';

            $actions = '<div class="category-actions">';
            $actions .= '<a href="'.route('income.categories.show', $category).'" class="category-action-btn is-neutral">View</a>';
            $actions .= '<button type="button" class="category-action-btn is-primary category-edit-btn"'
                .' data-category-id="'.$category->id.'"'
                .' data-category-name="'.e($category->name).'"'
                .' data-category-status="'.$category->status.'"'
                .'>Edit</button>';
            $actions .= '<button type="button" class="category-action-btn is-danger category-delete-btn"'
                .' data-category-id="'.$category->id.'"'
                .' data-destroy-url="'.route('income.categories.destroy', $category).'"'
                .'>Delete</button>';
            $actions .= '</div>';

            return [
                'name' => '<span class="category-name-text">'.e($category->name).'</span>',
                'status' => $statusBadge,
                'entries_count' => number_format($entriesCount),
                'total_amount' => '&#2547; '.number_format($totalAmount, 2),
                'latest_date' => $latestDate ? Carbon::parse($latestDate)->format('d M Y') : '&mdash;',
                'actions' => $actions,
            ];
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function show(IncomeCategory $category)
    {
        $totalEntries = IncomeEntry::where('source_category', $category->name)->count();
        $totalAmount = (float) IncomeEntry::where('source_category', $category->name)->sum('amount');
        $avgAmount = $totalEntries > 0 ? $totalAmount / $totalEntries : 0;

        $monthly = IncomeEntry::where('source_category', $category->name)
            ->where('date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $months->put($key, [
                'label' => now()->subMonths($i)->format('M Y'),
                'total' => (float) ($monthly->get($key)?->total ?? 0),
                'count' => (int) ($monthly->get($key)?->count ?? 0),
            ]);
        }

        $recentEntries = IncomeEntry::where('source_category', $category->name)
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        return view('pages.finance.income.categories.show', compact(
            'category', 'totalEntries', 'totalAmount', 'avgAmount', 'months', 'recentEntries'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:income_categories,name'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $category = IncomeCategory::create($data);

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, IncomeCategory $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:income_categories,name,'.$category->id],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $category->update($data);

        return response()->json(['message' => 'Category updated successfully.']);
    }

    public function destroy(IncomeCategory $category)
    {
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
