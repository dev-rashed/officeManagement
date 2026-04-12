<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\ExpenseEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index()
    {
        $entries = ExpenseEntry::orderByDesc('date')->paginate(20);
        $totalEntries = ExpenseEntry::count();
        $pendingEntries = ExpenseEntry::whereIn('status', [
            ExpenseEntry::STATUS_PENDING,
            ExpenseEntry::STATUS_PENDING_DIRECTOR,
            ExpenseEntry::STATUS_PENDING_CHAIRMAN,
        ])->count();
        $approvedEntries = ExpenseEntry::where('status', ExpenseEntry::STATUS_FULLY_APPROVED)->count();

        return view('pages.finance.expense.index', compact(
            'entries',
            'totalEntries',
            'pendingEntries',
            'approvedEntries',
        ));
    }

    public function create()
    {
        $this->authorizeFinanceEditor();

        return view('pages.finance.expense.create');
    }

    public function store(Request $request)
    {
        $this->authorizeFinanceEditor();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'expense_category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'max:20480'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('uploads/expense', 'public');
        }

        $data['status'] = ExpenseEntry::STATUS_PENDING;
        $data['created_by'] = $request->user()->id;

        ExpenseEntry::create($data);

        return redirect()->route('expense.index')->with('success', 'Expense entry created successfully.');
    }

    public function show(ExpenseEntry $expense)
    {
        return view('pages.finance.expense.show', compact('expense'));
    }

    public function edit(ExpenseEntry $expense)
    {
        $this->authorizeFinanceEditor();

        return view('pages.finance.expense.edit', compact('expense'));
    }

    public function update(Request $request, ExpenseEntry $expense)
    {
        $this->authorizeFinanceEditor();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'expense_category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'max:20480'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('attachment')) {
            Storage::disk('public')->delete($expense->attachment_path);
            $data['attachment_path'] = $request->file('attachment')->store('uploads/expense', 'public');
        }

        $expense->update($data);

        return redirect()->route('expense.index')->with('success', 'Expense entry updated successfully.');
    }

    public function destroy(ExpenseEntry $expense)
    {
        $this->authorizeFinanceEditor();

        Storage::disk('public')->delete($expense->attachment_path);
        $expense->delete();

        return redirect()->route('expense.index')->with('success', 'Expense entry deleted successfully.');
    }

    public function approve(Request $request, ExpenseEntry $expense)
    {
        $request->validate([
            'action' => ['required', 'string', 'in:approve,reject,send_back'],
            'comments' => ['nullable', 'string'],
        ]);

        if (! $expense->canBeApprovedBy($request->user())) {
            abort(403);
        }

        $stage = $expense->nextApprovalStage();

        if (! $stage) {
            abort(403);
        }

        $status = match ($request->input('action')) {
            'approve' => 'approved',
            'reject' => 'rejected',
            default => 'sent_back',
        };

        $expense->approvals()->updateOrCreate(
            ['stage' => $stage],
            [
                'status' => $status,
                'comments' => $request->input('comments'),
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ],
        );

        if ($request->input('action') === 'approve') {
            $expense->status = match ($expense->status) {
                ExpenseEntry::STATUS_PENDING => ExpenseEntry::STATUS_PENDING_DIRECTOR,
                ExpenseEntry::STATUS_PENDING_DIRECTOR => ExpenseEntry::STATUS_PENDING_CHAIRMAN,
                ExpenseEntry::STATUS_PENDING_CHAIRMAN => ExpenseEntry::STATUS_FULLY_APPROVED,
                default => $expense->status,
            };
        } elseif ($request->input('action') === 'reject') {
            $expense->status = ExpenseEntry::STATUS_REJECTED;
        } else {
            $expense->status = ExpenseEntry::STATUS_SENT_BACK;
        }

        $expense->save();

        return back()->with('success', 'Approval action recorded successfully.');
    }

    private function authorizeFinanceEditor(): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasPermission('finance.manage')) {
            abort(403);
        }
    }
}
