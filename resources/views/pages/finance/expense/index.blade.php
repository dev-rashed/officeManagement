<x-layouts::app :title="__('Expense Management')">
    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Expense Management') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Add, review and approve expense records through the workflow.') }}</p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isAccountant())
                <a href="{{ route('expense.create') }}" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">
                    {{ __('Add New Expense') }}
                </a>
            @endif
        </div>

        <div class="grid gap-3 md:grid-cols-3">
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Total Entries') }}</p>
                <p class="page-stat-value">{{ number_format($totalEntries) }}</p>
                <span class="page-stat-bar is-total"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Pending Approval') }}</p>
                <p class="page-stat-value">{{ number_format($pendingEntries) }}</p>
                <span class="page-stat-bar is-pending"></span>
            </div>
            <div class="page-stat-card">
                <p class="page-stat-label">{{ __('Approved') }}</p>
                <p class="page-stat-value">{{ number_format($approvedEntries) }}</p>
                <span class="page-stat-bar is-active"></span>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-900">{{ session('success') }}</div>
        @endif

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table id="expense-table" class="min-w-full text-left">
                    <thead>
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Next Approval') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $expense)
                            @php
                                $statusClass = match ($expense->status) {
                                    \App\Models\ExpenseEntry::STATUS_FULLY_APPROVED => 'is-active',
                                    \App\Models\ExpenseEntry::STATUS_REJECTED, \App\Models\ExpenseEntry::STATUS_SENT_BACK => 'is-danger',
                                    default => 'is-pending',
                                };
                            @endphp
                            <tr>
                                <td><span class="data-table-title">{{ $expense->title }}</span></td>
                                <td><span class="data-table-subtle">{{ $expense->expense_category }}</span></td>
                                <td>&#2547; {{ number_format($expense->amount, 2) }}</td>
                                <td>{{ $expense->date->format('Y-m-d') }}</td>
                                <td><span class="data-table-badge {{ $statusClass }}">{{ $expense->statusLabel() }}</span></td>
                                <td><span class="data-table-subtle">{{ $expense->nextApprovalLabel() ?? __('Completed') }}</span></td>
                                <td>
                                    <div class="data-table-actions">
                                        <a href="{{ route('expense.show', $expense) }}" class="data-table-action-btn is-neutral">{{ __('View') }}</a>
                                        @if(auth()->user()->isAdmin() || auth()->user()->isAccountant())
                                            <a href="{{ route('expense.edit', $expense) }}" class="data-table-action-btn is-primary">{{ __('Edit') }}</a>
                                            <form action="{{ route('expense.destroy', $expense) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure you want to delete this expense entry?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="data-table-action-btn is-danger">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">{{ __('No expense entries found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $entries->links() }}
        </div>

        <style>
            .page-stat-card {
                background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
                border: 1px solid #dbe4f0;
                border-radius: 18px;
                box-shadow: 0 10px 24px rgba(148, 163, 184, 0.08);
                padding: 0.95rem 1rem 0.85rem;
            }

            .page-stat-label {
                color: #64748b;
                font-size: 0.72rem;
                font-weight: 600;
            }

            .page-stat-value {
                color: #0f172a;
                font-size: 1.7rem;
                font-weight: 700;
                line-height: 1;
                margin-top: 0.8rem;
            }

            .page-stat-bar {
                border-radius: 9999px;
                display: inline-block;
                height: 2px;
                margin-top: 0.9rem;
                width: 1.7rem;
            }

            .page-stat-bar.is-total { background: #64748b; }
            .page-stat-bar.is-pending { background: #f59e0b; }
            .page-stat-bar.is-active { background: #10b981; }

            #expense-table {
                border-collapse: separate;
                border-spacing: 0;
                color: #0f172a;
                width: 100%;
            }

            #expense-table thead th {
                background: #fbfdff;
                border-bottom: 1px solid #e8eef6;
                color: #8ba0c0;
                font-size: 0.64rem;
                font-weight: 700;
                letter-spacing: 0.18em;
                padding: 0.68rem 0.9rem;
                text-transform: uppercase;
            }

            #expense-table tbody td {
                background: #fff;
                border-bottom: 1px solid #edf2f7;
                font-size: 0.76rem;
                padding: 0.78rem 0.9rem;
                vertical-align: middle;
            }

            #expense-table tbody tr:hover td {
                background: #f8fbff;
            }

            .data-table-title {
                color: #0f172a;
                font-size: 0.78rem;
                font-weight: 600;
            }

            .data-table-subtle {
                color: #64748b;
                font-size: 0.74rem;
            }

            .data-table-badge {
                border: 1px solid;
                border-radius: 9999px;
                display: inline-flex;
                font-size: 0.64rem;
                font-weight: 600;
                line-height: 1;
                padding: 0.25rem 0.48rem;
            }

            .data-table-badge.is-active {
                background: #ecfdf3;
                border-color: #a7f3d0;
                color: #047857;
            }

            .data-table-badge.is-pending {
                background: #fff7ed;
                border-color: #fdba74;
                color: #c2410c;
            }

            .data-table-badge.is-danger {
                background: #fef2f2;
                border-color: #fca5a5;
                color: #b91c1c;
            }

            .data-table-actions {
                display: inline-flex;
                gap: 0.32rem;
                white-space: nowrap;
            }

            .data-table-action-btn {
                border: 1px solid transparent;
                border-radius: 8px;
                font-size: 0.67rem;
                font-weight: 600;
                padding: 0.34rem 0.62rem;
            }

            .data-table-action-btn.is-neutral {
                background: #fff;
                border-color: #dbe4f0;
                color: #334155;
            }

            .data-table-action-btn.is-primary {
                background: #059669;
                color: #fff;
            }

            .data-table-action-btn.is-danger {
                background: #ef4444;
                color: #fff;
            }

            .dark .page-stat-card,
            .dark #expense-table tbody td,
            .dark .data-table-action-btn.is-neutral {
                background: #09090b !important;
                border-color: #27272a !important;
                color: #f4f4f5 !important;
            }

            .dark .page-stat-label,
            .dark #expense-table thead th,
            .dark .data-table-subtle {
                color: #94a3b8;
            }

            .dark .page-stat-value,
            .dark .data-table-title,
            .dark #expense-table {
                color: #fafafa;
            }

            .dark #expense-table thead th {
                background: #111827;
                border-bottom-color: #27272a;
            }

            .dark #expense-table tbody td {
                border-bottom-color: #27272a;
            }

            .dark #expense-table tbody tr:hover td {
                background: #111827;
            }
        </style>
    </div>
</x-layouts::app>
