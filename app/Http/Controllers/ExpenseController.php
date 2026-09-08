<?php

namespace App\Http\Controllers;

use App\Concerns\HandlesImageUploads;
use App\Models\Approval;
use App\Models\ExpenseCategory;
use App\Models\ExpenseEntry;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $entries = ExpenseEntry::with('category')->orderByDesc('date')->paginate(20);
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

        $categories = ExpenseCategory::selectable()->get();

        return view('pages.finance.expense.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorizeFinanceEditor();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'max:20480'],
            'description' => ['nullable', 'string'],
        ]);

        // A photographed invoice is optimised; a PDF is stored as uploaded.
        $this->applyUpload($request, $data, 'attachment', null, 'uploads/expense', 'document', column: 'attachment_path');

        $data['status'] = ExpenseEntry::STATUS_PENDING;
        $data['created_by'] = $request->user()->id;

        $expense = ExpenseEntry::create($data);

        app(NotificationDispatcher::class)->dispatch(
            'expense.created',
            $expense->load(['category', 'creator']),
        );

        return redirect()->route('expense.index')->with('success', 'Expense entry created successfully.');
    }

    public function show(ExpenseEntry $expense)
    {
        $expense->load(['category', 'approvals.approver']);

        return view('pages.finance.expense.show', compact('expense'));
    }

    public function edit(ExpenseEntry $expense)
    {
        $this->authorizeFinanceEditor();

        // Keep the entry's own category in the list even if it was since
        // deactivated, so editing an older expense cannot silently blank it.
        $categories = ExpenseCategory::selectable($expense->expense_category_id)->get();

        return view('pages.finance.expense.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, ExpenseEntry $expense)
    {
        $this->authorizeFinanceEditor();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'max:20480'],
            'description' => ['nullable', 'string'],
        ]);

        $this->applyUpload($request, $data, 'attachment', $expense->attachment_path, 'uploads/expense', 'document', column: 'attachment_path');

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
            // A rejection or a send-back must say why -- the person whose entry
            // it is has to know what to fix.
            'comments' => ['required_if:action,reject,send_back', 'nullable', 'string', 'max:2000'],
        ], [
            'comments.required_if' => 'Please give a reason when rejecting or sending an entry back.',
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
