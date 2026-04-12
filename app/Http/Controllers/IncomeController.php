<?php

namespace App\Http\Controllers;

use App\Models\IncomeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IncomeController extends Controller
{
    public function index()
    {
        $totalEntries = IncomeEntry::count();
        $pendingEntries = IncomeEntry::whereIn('status', [
            IncomeEntry::STATUS_PENDING,
            IncomeEntry::STATUS_PENDING_DIRECTOR,
            IncomeEntry::STATUS_PENDING_CHAIRMAN,
        ])->count();
        $approvedEntries = IncomeEntry::where('status', IncomeEntry::STATUS_FULLY_APPROVED)->count();

        return view('pages.finance.income.index', compact(
            'totalEntries',
            'pendingEntries',
            'approvedEntries',
        ));
    }

    public function indexData(Request $request)
    {
        $query = IncomeEntry::query()->orderByDesc('date');

        $totalRecords = IncomeEntry::count();

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('source_category', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 20);

        $entries = $query->skip($start)->take($length)->get();

        $data = $entries->map(function (IncomeEntry $income) {
            $statusClasses = match ($income->status) {
                IncomeEntry::STATUS_FULLY_APPROVED => 'is-active',
                IncomeEntry::STATUS_REJECTED => 'is-danger',
                IncomeEntry::STATUS_SENT_BACK => 'is-danger',
                default => 'is-pending',
            };

            $actions  = '<div class="data-table-actions">';
            $actions .= '<a href="'.route('income.show', $income).'" class="data-table-action-btn is-neutral">View</a>';
            $actions .= '<a href="'.route('income.edit', $income).'" class="data-table-action-btn is-primary">Edit</a>';
            $actions .= '<button type="button" class="data-table-action-btn is-danger income-delete-btn" data-destroy-url="'.route('income.destroy', $income).'">Delete</button>';
            $actions .= '</div>';

            return [
                'title'           => '<span class="data-table-title">'.e($income->title).'</span>',
                'source_category' => '<span class="data-table-subtle">'.e($income->source_category).'</span>',
                'amount'          => '&#2547; '.number_format((float) $income->amount, 2),
                'date'            => $income->date instanceof \Illuminate\Support\Carbon ? $income->date->format('Y-m-d') : \Illuminate\Support\Carbon::parse($income->date)->format('Y-m-d'),
                'status'          => '<span class="data-table-badge '.$statusClasses.'">'.e($income->statusLabel()).'</span>',
                'next_approval'   => '<span class="data-table-subtle">'.e($income->nextApprovalLabel() ?? __('Completed')).'</span>',
                'actions'         => $actions,
            ];
        });

        return response()->json([
            'draw'            => $request->input('draw'),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ]);
    }

    public function create()
    {
        $this->authorizeFinanceEditor();

        return view('pages.finance.income.create');
    }

    public function store(Request $request)
    {
        $this->authorizeFinanceEditor();

        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'source_category'  => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:0'],
            'date'             => ['required', 'date'],
            'payment_method'   => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'attachment'       => ['nullable', 'file', 'max:20480'],
            'description'      => ['nullable', 'string'],
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('uploads/income', 'public');
        }

        $data['status']     = IncomeEntry::STATUS_PENDING;
        $data['created_by'] = $request->user()->id;

        IncomeEntry::create($data);

        notify('Income entry created successfully.', 'Created', 'success');

        return redirect()->route('income.index');
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
            'title'            => ['required', 'string', 'max:255'],
            'source_category'  => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:0'],
            'date'             => ['required', 'date'],
            'payment_method'   => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'attachment'       => ['nullable', 'file', 'max:20480'],
            'description'      => ['nullable', 'string'],
        ]);

        if ($request->hasFile('attachment')) {
            Storage::disk('public')->delete($income->attachment_path);
            $data['attachment_path'] = $request->file('attachment')->store('uploads/income', 'public');
        }

        $income->update($data);

        notify('Income entry updated successfully.', 'Updated', 'success');

        return redirect()->route('income.index');
    }

    public function destroy(IncomeEntry $income)
    {
        $this->authorizeFinanceEditor();

        Storage::disk('public')->delete($income->attachment_path);
        $income->delete();

        return response()->json(['message' => 'Income entry deleted successfully.']);
    }

    public function approve(Request $request, IncomeEntry $income)
    {
        $request->validate([
            'action'   => ['required', 'string', 'in:approve,reject,send_back'],
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
            'approve'  => 'approved',
            'reject'   => 'rejected',
            default    => 'sent_back',
        };

        $income->approvals()->updateOrCreate(
            ['stage' => $stage],
            [
                'status'      => $status,
                'comments'    => $request->input('comments'),
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ],
        );

        if ($request->input('action') === 'approve') {
            $income->status = match ($income->status) {
                IncomeEntry::STATUS_PENDING          => IncomeEntry::STATUS_PENDING_DIRECTOR,
                IncomeEntry::STATUS_PENDING_DIRECTOR => IncomeEntry::STATUS_PENDING_CHAIRMAN,
                IncomeEntry::STATUS_PENDING_CHAIRMAN => IncomeEntry::STATUS_FULLY_APPROVED,
                default                              => $income->status,
            };
        } elseif ($request->input('action') === 'reject') {
            $income->status = IncomeEntry::STATUS_REJECTED;
        } else {
            $income->status = IncomeEntry::STATUS_SENT_BACK;
        }

        $income->save();

        notify('Approval action recorded successfully.', 'Done', 'success');

        return back();
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

        return (bool) $user && $user->hasPermission('finance.manage');
    }
}
