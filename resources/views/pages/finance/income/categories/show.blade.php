<x-layouts::app :title="$category->name . ' — Income Category'">
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('income.categories') }}" class="rounded-lg border border-zinc-700 p-1.5 text-zinc-400 transition hover:bg-zinc-800 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-semibold tracking-tight text-white">{{ $category->name }}</h1>
                        <span class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-medium {{ $category->isActive() ? 'border-emerald-900/70 bg-emerald-950/40 text-emerald-300' : 'border-amber-900/70 bg-amber-950/40 text-amber-300' }}">
                            {{ ucfirst($category->status) }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-sm text-zinc-500">{{ __('Income category overview and analytics') }}</p>
                </div>
            </div>
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">{{ __('Total Entries') }}</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ number_format($totalEntries) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">{{ __('Total Amount') }}</p>
                <p class="mt-2 text-3xl font-bold text-emerald-400">{{ number_format($totalAmount, 2) }}</p>
            </div>
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">{{ __('Average per Entry') }}</p>
                <p class="mt-2 text-3xl font-bold text-sky-400">{{ number_format($avgAmount, 2) }}</p>
            </div>
        </div>

        {{-- Monthly chart --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
            <h2 class="mb-4 text-sm font-semibold text-zinc-300">{{ __('Monthly Income — Last 12 Months') }}</h2>
            <canvas id="monthly-chart" height="90"></canvas>
        </div>

        {{-- Recent entries --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900">
            <div class="border-b border-zinc-800 px-5 py-3">
                <h2 class="text-sm font-semibold text-zinc-300">{{ __('Recent Entries') }}</h2>
            </div>
            @forelse($recentEntries as $entry)
                <div class="flex items-center justify-between border-b border-zinc-800/60 px-5 py-3 last:border-b-0">
                    <div>
                        <p class="text-sm font-medium text-zinc-100">{{ $entry->title }}</p>
                        <p class="mt-0.5 text-xs text-zinc-500">{{ $entry->date->format('M d, Y') }} &middot; {{ $entry->statusLabel() }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-emerald-400">{{ number_format((float) $entry->amount, 2) }}</span>
                        <a href="{{ route('income.show', $entry) }}" class="rounded-lg bg-zinc-800 px-3 py-1 text-xs font-medium text-zinc-300 transition hover:bg-zinc-700">View</a>
                    </div>
                </div>
            @empty
                <p class="px-5 py-6 text-center text-sm text-zinc-500">{{ __('No entries found for this category.') }}</p>
            @endforelse
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const labels  = @json($months->pluck('label')->values());
            const amounts = @json($months->pluck('total')->values());
            const counts  = @json($months->pluck('count')->values());

            const ctx = document.getElementById('monthly-chart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Total Amount',
                            data: amounts,
                            backgroundColor: 'rgba(16, 185, 129, 0.25)',
                            borderColor: 'rgba(16, 185, 129, 0.8)',
                            borderWidth: 2,
                            borderRadius: 6,
                            yAxisID: 'y',
                        },
                        {
                            label: 'Entries',
                            data: counts,
                            type: 'line',
                            borderColor: 'rgba(56, 189, 248, 0.8)',
                            backgroundColor: 'rgba(56, 189, 248, 0.1)',
                            borderWidth: 2,
                            pointRadius: 4,
                            pointBackgroundColor: 'rgba(56, 189, 248, 0.9)',
                            tension: 0.35,
                            fill: true,
                            yAxisID: 'y1',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            labels: { color: '#a1a1aa', font: { size: 12 } },
                        },
                        tooltip: {
                            backgroundColor: 'rgba(9,9,11,0.95)',
                            borderColor: 'rgba(63,63,70,0.8)',
                            borderWidth: 1,
                            titleColor: '#f4f4f5',
                            bodyColor: '#a1a1aa',
                            padding: 10,
                        },
                    },
                    scales: {
                        x: {
                            ticks: { color: '#71717a', font: { size: 11 } },
                            grid: { color: 'rgba(39,39,42,0.5)' },
                        },
                        y: {
                            position: 'left',
                            ticks: { color: '#71717a', font: { size: 11 } },
                            grid: { color: 'rgba(39,39,42,0.5)' },
                            title: { display: true, text: 'Amount', color: '#71717a', font: { size: 11 } },
                        },
                        y1: {
                            position: 'right',
                            ticks: { color: '#71717a', font: { size: 11 }, stepSize: 1 },
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'Entries', color: '#71717a', font: { size: 11 } },
                        },
                    },
                },
            });
        });
    </script>
</x-layouts::app>
