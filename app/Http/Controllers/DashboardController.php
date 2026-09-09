<?php

namespace App\Http\Controllers;

use App\Models\ExpenseEntry;
use App\Models\IncomeEntry;
use App\Models\Project;
use App\Models\Trainee;
use Illuminate\Http\Request;

/**
 * What this person needs to see when they sign in.
 *
 * Everything is scoped to the viewer: their own contributions and claims
 * always, everyone else's only with finance.view_all.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('dashboard', [
            'contributions' => $this->contributions($user),
            'reimbursements' => $this->reimbursements($user),
            'projects' => $this->runningProjects($user),
            'attention' => $this->needsAttention($user),
            'notifications' => $user->notifications()->latest()->limit(6)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Money this person has personally put into the office.
     */
    private function contributions($user): array
    {
        $base = fn () => IncomeEntry::query()->contributedBy($user->id);

        $approved = (clone $base())->where('status', IncomeEntry::STATUS_FULLY_APPROVED);

        return [
            'total' => (float) $approved->sum('amount'),
            'count' => (clone $base())->count(),
            'pending' => (float) (clone $base())
                ->whereIn('status', [
                    IncomeEntry::STATUS_PENDING,
                    IncomeEntry::STATUS_PENDING_DIRECTOR,
                    IncomeEntry::STATUS_PENDING_CHAIRMAN,
                ])->sum('amount'),
            'recent' => (clone $base())->with('project:id,title')->latest('date')->limit(5)->get(),
            'thisYear' => (float) (clone $approved)->whereYear('date', now()->year)->sum('amount'),
        ];
    }

    /**
     * What the office owes this person for expenses they paid themselves.
     */
    private function reimbursements($user): array
    {
        $mine = fn () => ExpenseEntry::query()
            ->where('payment_source', ExpenseEntry::SOURCE_PERSONAL)
            ->where('paid_by', $user->id);

        return [
            'owed' => (float) (clone $mine())->where('reimbursement_status', ExpenseEntry::REIMBURSE_PENDING)->sum('amount'),
            'owedCount' => (clone $mine())->where('reimbursement_status', ExpenseEntry::REIMBURSE_PENDING)->count(),
            'reimbursed' => (float) (clone $mine())->where('reimbursement_status', ExpenseEntry::REIMBURSE_DONE)->sum('amount'),
            'recent' => (clone $mine())->with('category:id,name')->latest('date')->limit(5)->get(),
        ];
    }

    /**
     * Projects currently running, with how many students are on them.
     */
    private function runningProjects($user)
    {
        return Project::query()
            ->whereIn('status', [Project::STATUS_ACTIVE, Project::STATUS_PLANNING])
            ->withCount('trainees')
            ->orderByRaw("FIELD(status, 'active', 'planning')")
            ->orderBy('start_date')
            ->limit(6)
            ->get()
            ->map(function (Project $project) {
                $target = (int) $project->targeted_trainees;
                $registered = (int) $project->trainees_count;

                $project->progress = $target > 0
                    ? min(100, (int) round(($registered / $target) * 100))
                    : null;

                return $project;
            });
    }

    /**
     * Things waiting on this particular person.
     */
    private function needsAttention($user): array
    {
        $items = [];

        // Entries sitting at a stage only this person can act on. The status
        // filter keeps the scan small; canBeApprovedBy() decides whose turn it is.
        $pendingIncome = IncomeEntry::query()
            ->whereIn('status', [
                IncomeEntry::STATUS_PENDING,
                IncomeEntry::STATUS_PENDING_DIRECTOR,
                IncomeEntry::STATUS_PENDING_CHAIRMAN,
            ])
            ->get(['id', 'amount', 'status'])
            ->filter(fn (IncomeEntry $e) => $e->canBeApprovedBy($user));

        $pendingExpense = ExpenseEntry::query()
            ->whereIn('status', [
                ExpenseEntry::STATUS_PENDING,
                ExpenseEntry::STATUS_PENDING_DIRECTOR,
                ExpenseEntry::STATUS_PENDING_CHAIRMAN,
            ])
            ->get(['id', 'amount', 'status'])
            ->filter(fn (ExpenseEntry $e) => $e->canBeApprovedBy($user));

        if ($pendingIncome->isNotEmpty()) {
            $items[] = [
                'kind' => 'approval',
                'tone' => 'amber',
                'title' => trans_choice('{1} :count income entry needs your approval|[2,*] :count income entries need your approval',
                    $pendingIncome->count(), ['count' => $pendingIncome->count()]),
                'note' => '৳ ' . number_format((float) $pendingIncome->sum('amount'), 2),
                'url' => route('income.index'),
            ];
        }

        if ($pendingExpense->isNotEmpty()) {
            $items[] = [
                'kind' => 'approval',
                'tone' => 'amber',
                'title' => trans_choice('{1} :count expense entry needs your approval|[2,*] :count expense entries need your approval',
                    $pendingExpense->count(), ['count' => $pendingExpense->count()]),
                'note' => '৳ ' . number_format((float) $pendingExpense->sum('amount'), 2),
                'url' => route('expense.index'),
            ];
        }

        // Their own entries that were sent back for correction.
        $sentBack = ExpenseEntry::query()
            ->where('created_by', $user->id)
            ->where('status', ExpenseEntry::STATUS_SENT_BACK)
            ->count();

        if ($sentBack > 0) {
            $items[] = [
                'kind' => 'sent_back',
                'tone' => 'rose',
                'title' => trans_choice('{1} :count expense was sent back to you|[2,*] :count expenses were sent back to you',
                    $sentBack, ['count' => $sentBack]),
                'note' => __('Open it to see what needs correcting.'),
                'url' => route('expense.index'),
            ];
        }

        if ($user->hasPermission('finance.manage')) {
            $toPay = ExpenseEntry::query()
                ->awaitingReimbursement()
                ->where('status', ExpenseEntry::STATUS_FULLY_APPROVED)
                ->get();

            if ($toPay->isNotEmpty()) {
                $items[] = [
                    'kind' => 'reimburse',
                    'tone' => 'sky',
                    'title' => trans_choice('{1} :count approved claim is waiting to be paid|[2,*] :count approved claims are waiting to be paid',
                        $toPay->count(), ['count' => $toPay->count()]),
                    'note' => '৳ ' . number_format((float) $toPay->sum('amount'), 2),
                    'url' => route('expense.index'),
                ];
            }
        }

        return $items;
    }
}
