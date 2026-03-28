<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\IncomeCategory;
use App\Models\IncomeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IncomeController extends Controller
{
    public function index()
    {
        $entries = IncomeEntry::orderByDesc('date')->paginate(20);

        return view('pages.finance.income.index', compact('entries'));
    }

    public function create()
    {
        $this->authorizeFinanceEditor();

        return view('pages.finance.income.create');
    }

    public function categories()
    {
        $canManageCategories = $this->canManageFinanceEditor();

        $categories = IncomeCategory::query()
            ->orderBy('name')
            ->paginate(20);

        $categories->getCollection()->transform(function (IncomeCategory $category) {
            $category->entries_count = IncomeEntry::where('source_category', $category->name)->count();
            $category->total_amount = (float) IncomeEntry::where('source_category', $category->name)->sum('amount');
            $category->latest_date = IncomeEntry::where('source_category', $category->name)->max('date');

            return $category;
        });

        return view('pages.finance.income.categories', compact('categories', 'canManageCategories'));
    }

    public function storeCategory(Request $request)
    {
        $this->authorizeFinanceEditor();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:income_categories,name'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        IncomeCategory::create($data);

        return redirect()
            ->route('income.categories')
            ->with('success', 'Income category added successfully.');
    }

    public function updateCategory(Request $request, IncomeCategory $category)
    {
        $this->authorizeFinanceEditor();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:income_categories,name,'.$category->id],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $category->update($data);

        return redirect()
            ->route('income.categories')
            ->with('success', 'Income category updated successfully.');
    }

    public function destroyCategory(IncomeCategory $category)
    {
        $this->authorizeFinanceEditor();

        $category->delete();

        return redirect()
            ->route('income.categories')
            ->with('success', 'Income category deleted successfully.');
    }

    public function store(Request $request)
    {
        $this->authorizeFinanceEditor();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'source_category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'max:20480'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('uploads/income', 'public');
        }

        $data['status'] = IncomeEntry::STATUS_PENDING;
        $data['created_by'] = $request->user()->id;

        IncomeEntry::create($data);

        return redirect()->route('income.index')->with('success', 'Income entry created successfully.');
    }

    public function show(IncomeEntry $income)
    {
        return view('pages.finance.income.show', compact('income'));
    }

    public function edit(IncomeEntry $income)
    {
        $this->authorizeFinanceEditor();

        return view('pages.finance.income.edit', compact('income'));
    }

    public function update(Request $request, IncomeEntry $income)
    {
        $this->authorizeFinanceEditor();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'source_category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'max:20480'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('attachment')) {
            Storage::disk('public')->delete($income->attachment_path);
            $data['attachment_path'] = $request->file('attachment')->store('uploads/income', 'public');
        }

        $income->update($data);

        return redirect()->route('income.index')->with('success', 'Income entry updated successfully.');
    }

    public function destroy(IncomeEntry $income)
    {
        $this->authorizeFinanceEditor();

        Storage::disk('public')->delete($income->attachment_path);
        $income->delete();

        return redirect()->route('income.index')->with('success', 'Income entry deleted successfully.');
    }

    public function approve(Request $request, IncomeEntry $income)
    {
        $request->validate([
            'action' => ['required', 'string', 'in:approve,reject,send_back'],
            'comments' => ['nullable', 'string'],
        ]);

        if (! $income->canBeApprovedBy($request->user())) {
            abort(403);
        }

        $stage = $income->nextApprovalStage();

        if (! $stage) {
            abort(403);
        }

        $status = match ($request->input('action')) {
            'approve' => 'approved',
            'reject' => 'rejected',
            default => 'sent_back',
        };

        /** @var Approval $approval */
        $income->approvals()->updateOrCreate(
            ['stage' => $stage],
            [
                'status' => $status,
                'comments' => $request->input('comments'),
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ],
        );

        if ($request->input('action') === 'approve') {
            $income->status = match ($income->status) {
                IncomeEntry::STATUS_PENDING => IncomeEntry::STATUS_PENDING_DIRECTOR,
                IncomeEntry::STATUS_PENDING_DIRECTOR => IncomeEntry::STATUS_PENDING_CHAIRMAN,
                IncomeEntry::STATUS_PENDING_CHAIRMAN => IncomeEntry::STATUS_FULLY_APPROVED,
                default => $income->status,
            };
        } elseif ($request->input('action') === 'reject') {
            $income->status = IncomeEntry::STATUS_REJECTED;
        } else {
            $income->status = IncomeEntry::STATUS_SENT_BACK;
        }

        $income->save();

        return back()->with('success', 'Approval action recorded successfully.');
    }

    private function authorizeFinanceEditor(): void
    {
        if (! $this->canManageFinanceEditor()) {
            abort(403);
        }
    }

    private function canManageFinanceEditor(): bool
    {
        $user = auth()->user();

        return (bool) $user && ($user->isSuperAdmin() || $user->isAdmin() || $user->isAccountant());
    }
}
