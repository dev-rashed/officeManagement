<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\ExpenseEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        Gate::authorize('finance.categories.manage');

        $totalCategories = ExpenseCategory::count();
        $activeCategories = ExpenseCategory::where('status', ExpenseCategory::STATUS_ACTIVE)->count();
        $inactiveCategories = ExpenseCategory::where('status', ExpenseCategory::STATUS_INACTIVE)->count();

        return view('pages.finance.expense.categories.index', compact(
            'totalCategories',
            'activeCategories',
            'inactiveCategories',
        ));
    }

    public function data(Request $request)
    {
        Gate::authorize('finance.categories.manage');

        $query = ExpenseCategory::query()
            ->withCount('entries')
            ->withSum('entries', 'amount')
            ->withMax('entries', 'date')
            ->orderBy('name');

        $totalRecords = ExpenseCategory::count();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 20);

        $categories = $query->skip($start)->take($length)->get();

        $data = $categories->map(function (ExpenseCategory $category) {
            $entriesCount = (int) $category->entries_count;

            $statusBadge = '<span class="category-status-badge '
                .($category->isActive() ? 'is-active' : 'is-inactive')
                .'"><span class="category-status-dot"></span>'.ucfirst($category->status).'</span>';

            $name = '<span class="category-name-text">'.e($category->name).'</span>';

            if ($category->code) {
                $name .= '<span class="category-code-text">'.e($category->code).'</span>';
            }

            $actions = '<div class="category-actions">';
            $actions .= '<a href="'.route('expense.categories.show', $category).'" class="category-action-btn is-neutral">View</a>';
            $actions .= '<button type="button" class="category-action-btn is-primary category-edit-btn"'
                .' data-category-id="'.$category->id.'"'
                .' data-category-name="'.e($category->name).'"'
                .' data-category-code="'.e((string) $category->code).'"'
                .' data-category-description="'.e((string) $category->description).'"'
                .' data-category-status="'.$category->status.'"'
                .'>Edit</button>';

            // A category that entries already point at must not be removed --
            // deactivating keeps existing expenses resolvable.
            if ($entriesCount > 0) {
                $actions .= '<button type="button" class="category-action-btn is-muted" disabled'
                    .' title="Used by '.number_format($entriesCount).' expense(s) - deactivate instead">In use</button>';
            } else {
                $actions .= '<button type="button" class="category-action-btn is-danger category-delete-btn"'
                    .' data-category-id="'.$category->id.'"'
                    .' data-destroy-url="'.route('expense.categories.destroy', $category).'"'
                    .'>Delete</button>';
            }

            $actions .= '</div>';

            return [
                'name' => $name,
                'status' => $statusBadge,
                'entries_count' => number_format($entriesCount),
                'total_amount' => '&#2547; '.number_format((float) $category->entries_sum_amount, 2),
                'latest_date' => $category->entries_max_date
                    ? Carbon::parse($category->entries_max_date)->format('d M Y')
                    : '&mdash;',
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

    public function show(ExpenseCategory $category)
    {
        Gate::authorize('finance.categories.manage');

        $base = ExpenseEntry::where('expense_category_id', $category->id);

        $totalEntries = (clone $base)->count();
        $totalAmount = (float) (clone $base)->sum('amount');
        $avgAmount = $totalEntries > 0 ? $totalAmount / $totalEntries : 0;

        $monthly = (clone $base)
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

        $recentEntries = (clone $base)->orderByDesc('date')->limit(10)->get();

        return view('pages.finance.expense.categories.show', compact(
            'category', 'totalEntries', 'totalAmount', 'avgAmount', 'months', 'recentEntries'
        ));
    }

    public function store(Request $request)
    {
        Gate::authorize('finance.categories.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name'],
            'code' => ['nullable', 'string', 'max:50', 'unique:expense_categories,code'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(ExpenseCategory::STATUSES)],
        ]);

        $category = ExpenseCategory::create($data);

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, ExpenseCategory $category)
    {
        Gate::authorize('finance.categories.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('expense_categories', 'name')->ignore($category->id)],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('expense_categories', 'code')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(ExpenseCategory::STATUSES)],
        ]);

        $category->update($data);

        return response()->json(['message' => 'Category updated successfully.']);
    }

    public function destroy(ExpenseCategory $category)
    {
        Gate::authorize('finance.categories.manage');

        $inUse = ExpenseEntry::where('expense_category_id', $category->id)->count();

        if ($inUse > 0) {
            return response()->json([
                'message' => "This category is used by {$inUse} expense entry(s). Set it to inactive instead of deleting it.",
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
