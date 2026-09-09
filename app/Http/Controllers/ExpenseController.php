<?php

namespace App\Http\Controllers;

use App\Concerns\HandlesImageUploads;
use App\Models\Approval;
use App\Models\ExpenseCategory;
use App\Models\ExpenseEntry;
use App\Models\User;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    use HandlesImageUploads;

    public function index(Request $request)
    {
        $user = $request->user();

        // Everyone sees their own; finance.view_all widens it to everybody's.
        $scoped = fn () => ExpenseEntry::query()->visibleTo($user);

        $entries = $scoped()
            ->with(['category', 'creator:id,name', 'payer:id,name'])
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        $totalEntries = $scoped()->count();
        $pendingEntries = $scoped()->whereIn('status', [
            ExpenseEntry::STATUS_PENDING,
            ExpenseEntry::STATUS_PENDING_DIRECTOR,
            ExpenseEntry::STATUS_PENDING_CHAIRMAN,
        ])->count();
        $approvedEntries = $scoped()->where('status', ExpenseEntry::STATUS_FULLY_APPROVED)->count();

        // What the office still owes people.
        $owedQuery = $scoped()->awaitingReimbursement();
        $owedCount = (clone $owedQuery)->count();
        $owedAmount = (float) (clone $owedQuery)->sum('amount');

        $seesEveryone = $user->hasPermission('finance.view_all');

        return view('pages.finance.expense.index', compact(
            'entries',
            'totalEntries',
            'pendingEntries',
            'approvedEntries',
            'owedCount',
            'owedAmount',
            'seesEveryone',
        ));
    }

    /**
     * Anyone signed in may record an expense they paid for.
     *
     * Deliberately not behind finance.manage: a Director or Chairman who buys
     * something out of their own pocket has to be able to claim it back, and
     * they hold no finance permissions.
     */
    public function create()
    {
        $categories = ExpenseCategory::selectable()->get();
        $payers = $this->payerOptions();

        return view('pages.finance.expense.create', compact('categories', 'payers'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedEntry($request);

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

    public function show(Request $request, ExpenseEntry $expense)
    {
        $this->authorizeView($request, $expense);

        $expense->load(['category', 'approvals.approver', 'creator:id,name', 'payer:id,name', 'reimburser:id,name']);

        return view('pages.finance.expense.show', compact('expense'));
    }

    public function edit(Request $request, ExpenseEntry $expense)
    {
        $this->authorizeEdit($request, $expense);

        // Keep the entry's own category in the list even if it was since
        // deactivated, so editing an older expense cannot silently blank it.
        $categories = ExpenseCategory::selectable($expense->expense_category_id)->get();
        $payers = $this->payerOptions();

        return view('pages.finance.expense.edit', compact('expense', 'categories', 'payers'));
    }

    public function update(Request $request, ExpenseEntry $expense)
    {
        $this->authorizeEdit($request, $expense);

        $data = $this->validatedEntry($request, $expense);

        $this->applyUpload($request, $data, 'attachment', $expense->attachment_path, 'uploads/expense', 'document', column: 'attachment_path');

        $expense->update($data);

        return redirect()->route('expense.index')->with('success', 'Expense entry updated successfully.');
    }

    public function destroy(Request $request, ExpenseEntry $expense)
    {
        $this->authorizeEdit($request, $expense);

        Storage::disk('public')->delete($expense->attachment_path);
        $expense->delete();

        return redirect()->route('expense.index')->with('success', 'Expense entry deleted successfully.');
    }

    /**
     * Record that the office has paid someone back.
     */
    public function reimburse(Request $request, ExpenseEntry $expense)
    {
        if (! $request->user()->hasPermission('finance.manage')) {
            abort(403);
        }

        $data = $request->validate([
            'action' => ['required', 'string', 'in:mark_paid,mark_unpaid'],
            'reimbursement_note' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $expense->isPersonal()) {
            return back()->withErrors(['action' => 'This expense was paid by the office, so there is nothing to reimburse.']);
        }

        // Paying someone back for money that was never approved would be
        // paying out on an unchecked claim.
        if ($data['action'] === 'mark_paid' && $expense->status !== ExpenseEntry::STATUS_FULLY_APPROVED) {
            return back()->withErrors([
                'action' => 'This expense is not fully approved yet, so it cannot be marked as reimbursed.',
            ]);
        }

        if ($data['action'] === 'mark_paid') {
            $expense->reimbursement_status = ExpenseEntry::REIMBURSE_DONE;
            $expense->reimbursed_at = now();
            $expense->reimbursed_by = $request->user()->id;
        } else {
            $expense->reimbursement_status = ExpenseEntry::REIMBURSE_PENDING;
            $expense->reimbursed_at = null;
            $expense->reimbursed_by = null;
        }

        $expense->reimbursement_note = $data['reimbursement_note'] ?? null;
        $expense->save();

        return back()->with('success', $data['action'] === 'mark_paid'
            ? 'Marked as reimbursed.'
            : 'Moved back to awaiting reimbursement.');
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

    /**
     * Validation shared by store() and update(), including the reimbursement
     * fields.
     */
    private function validatedEntry(Request $request, ?ExpenseEntry $expense = null): array
    {
        $user = $request->user();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'payment_source' => ['required', 'string', Rule::in(array_keys(ExpenseEntry::SOURCES))],
            'paid_by' => ['nullable', 'integer', 'exists:users,id'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'max:20480'],
            'description' => ['nullable', 'string'],
        ]);

        if ($data['payment_source'] === ExpenseEntry::SOURCE_PERSONAL) {
            // Someone without finance.manage can only claim for themselves --
            // otherwise anyone could file a claim in a colleague's name.
            $canNameOthers = $user->hasPermission('finance.manage');
            $data['paid_by'] = $canNameOthers && filled($data['paid_by'] ?? null)
                ? (int) $data['paid_by']
                : $user->id;

            // Don't reset the status of an entry already settled.
            $data['reimbursement_status'] = $expense?->isReimbursed()
                ? ExpenseEntry::REIMBURSE_DONE
                : ExpenseEntry::REIMBURSE_PENDING;
        } else {
            $data['paid_by'] = null;
            $data['reimbursement_status'] = ExpenseEntry::REIMBURSE_NOT_REQUIRED;
        }

        return $data;
    }

    /** Options for "who paid", when the person is allowed to name someone else. */
    private function payerOptions()
    {
        if (! auth()->user()?->hasPermission('finance.manage')) {
            return collect();
        }

        return User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'role']);
    }

    private function authorizeView(Request $request, ExpenseEntry $expense): void
    {
        abort_unless($expense->isVisibleTo($request->user()), 403);
    }

    /**
     * Editing is for finance staff, or for the person who filed it while it is
     * still their own pending claim.
     */
    private function authorizeEdit(Request $request, ExpenseEntry $expense): void
    {
        $user = $request->user();

        if ($user->hasPermission('finance.manage')) {
            return;
        }

        $isOwn = $expense->created_by === $user->id || $expense->paid_by === $user->id;
        $stillEditable = $expense->status === ExpenseEntry::STATUS_PENDING
            || $expense->status === ExpenseEntry::STATUS_SENT_BACK;

        abort_unless($isOwn && $stillEditable && ! $expense->isReimbursed(), 403);
    }
}
