<x-layouts::app :title="__('Income Management')">
    <div class="space-y-6">
        <div class="rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Income Management') }}</h1>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Create, review and approve incoming revenue records.') }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-950/40 dark:text-emerald-300">
                        {{ trans_choice(':count record|:count records', $entries->total(), ['count' => $entries->total()]) }}
                    </div>
                    @if(auth()->user()->isAdmin() || auth()->user()->isAccountant())
                        <a href="{{ route('income.create') }}" class="inline-flex items-center justify-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700">
                            {{ __('Add New Income') }}
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
                <table id="income-table" class="min-w-full border-collapse text-left text-sm" style="width:100%">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Title') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Source / Category') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Next Approval') }}</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $income)
                            <tr class="bg-transparent transition-colors duration-200 hover:bg-zinc-800/35">
                                <td class="px-4 py-3 text-sm text-zinc-200">{{ $income->title }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-200">{{ $income->source_category }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-200">{{ number_format($income->amount, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-200">{{ $income->date->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-200">{{ $income->statusLabel() }}</td>
                                <td class="px-4 py-3 text-sm text-zinc-200">{{ $income->nextApprovalLabel() ?? __('Completed') }}</td>
                                <td class="px-4 py-3 text-sm space-x-2">
                                    <a href="{{ route('income.show', $income) }}" class="rounded-lg bg-zinc-800 px-3 py-1 text-xs font-medium text-zinc-200 transition hover:bg-zinc-700">{{ __('View') }}</a>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isAccountant())
                                        <a href="{{ route('income.edit', $income) }}" class="rounded-lg bg-sky-950/60 px-3 py-1 text-xs font-medium text-sky-300 transition hover:bg-sky-900/70">{{ __('Edit') }}</a>
                                        <form action="{{ route('income.destroy', $income) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure you want to delete this income entry?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-950/50 px-3 py-1 text-xs font-medium text-rose-300 transition hover:bg-rose-900/60">{{ __('Delete') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">{{ __('No income entries found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <style>
            #income-table {
                color: #e4e4e7;
            }

            #income-table thead th {
                background: rgba(9, 9, 11, 0.82);
                border-bottom: 1px solid rgba(39, 39, 42, 1);
                color: #a1a1aa;
                font-size: 0.7rem;
                font-weight: 700;
                letter-spacing: 0.18em;
                padding: 0.85rem 1.1rem;
                text-transform: uppercase;
            }

            #income-table tbody td {
                border-top: 1px solid rgba(39, 39, 42, 0.9);
                padding: 0.85rem 1.1rem;
                vertical-align: top;
            }

            #income-table tbody tr:hover {
                background: rgba(39, 39, 42, 0.34);
            }
        </style>

        <div>
            {{ $entries->links() }}
        </div>
    </div>
</x-layouts::app>
