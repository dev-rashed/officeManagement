<x-layouts::app :title="__('Expense Management')">
    <div class="space-y-6">
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Expense Management') }}</h1>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Add, review and approve expense records through the workflow.') }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-300">
                        {{ trans_choice(':count record|:count records', $entries->total(), ['count' => $entries->total()]) }}
                    </div>
                    @if(auth()->user()->isAdmin() || auth()->user()->isAccountant())
                        <a href="{{ route('expense.create') }}" class="inline-flex items-center justify-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700">
                            {{ __('Add New Expense') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('success') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table id="expense-table" class="min-w-full border-collapse text-left text-sm" style="width:100%">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Title') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Category') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Next Approval') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $expense)
                            <tr class="bg-transparent transition-colors duration-200 hover:bg-zinc-100 dark:hover:bg-zinc-800/60">
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $expense->title }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $expense->expense_category }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ number_format($expense->amount, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $expense->date->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $expense->statusLabel() }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $expense->nextApprovalLabel() ?? __('Completed') }}</td>
                                <td class="px-4 py-3 text-sm space-x-2">
                                    <a href="{{ route('expense.show', $expense) }}" class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ __('View') }}</a>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isAccountant())
                                        <a href="{{ route('expense.edit', $expense) }}" class="rounded-lg bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">{{ __('Edit') }}</a>
                                        <form action="{{ route('expense.destroy', $expense) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure you want to delete this expense entry?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-100 px-3 py-1 text-xs font-medium text-rose-700">{{ __('Delete') }}</button>
                                        </form>
                                    @endif
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

        <style>
            #expense-table {
                color: #111827;
            }

            #expense-table thead th {
                background: rgba(248, 250, 252, 1);
                border-bottom: 1px solid rgba(229, 231, 235, 1);
                color: #6b7280;
                font-size: 0.7rem;
                font-weight: 700;
                letter-spacing: 0.18em;
                padding: 0.85rem 1.1rem;
                text-transform: uppercase;
            }

            #expense-table tbody td {
                border-top: 1px solid rgba(229, 231, 235, 1);
                padding: 0.85rem 1.1rem;
                vertical-align: top;
            }

            #expense-table tbody tr:hover {
                background: rgba(241, 245, 249, 1);
            }
        </style>

        <div>
            {{ $entries->links() }}
        </div>
    </div>
</x-layouts::app>
