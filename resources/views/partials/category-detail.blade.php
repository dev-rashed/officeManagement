{{--
    Shared detail page for an income or expense category.

    Expects: $category, $totalEntries, $totalAmount, $avgAmount, $months,
    $recentEntries, plus $backRoute, $entryRouteName, $kind ('Income'|'Expense')
    and $accent ('emerald'|'amber').

    Styled with its own CSS rather than Tailwind utilities, so it does not
    depend on the compiled stylesheet being up to date.
--}}
@php
    $money = fn ($n) => '৳ ' . number_format((float) $n, 2);
@endphp

<div class="cd-page">

    {{-- Header --}}
    <header class="cd-header">
        <a href="{{ $backRoute }}" class="cd-back" aria-label="{{ __('Back to categories') }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
        </a>

        <div class="cd-header-text">
            <div class="cd-title-row">
                <h1 class="cd-title">{{ $category->name }}</h1>
                <span class="cd-status {{ $category->isActive() ? 'is-active' : 'is-inactive' }}">
                    <span class="cd-status-dot"></span>{{ ucfirst($category->status) }}
                </span>
                @if (! empty($category->code))
                    <code class="cd-code">{{ $category->code }}</code>
                @endif
            </div>
            <p class="cd-sub">{{ __(':kind category overview and analytics', ['kind' => $kind]) }}</p>
        </div>
    </header>

    {{-- Stats --}}
    <div class="cd-stats">
        <article class="cd-stat">
            <p class="cd-stat-label">{{ __('Total entries') }}</p>
            <p class="cd-stat-value">{{ number_format($totalEntries) }}</p>
        </article>
        <article class="cd-stat is-{{ $accent }}">
            <p class="cd-stat-label">{{ __('Total amount') }}</p>
            <p class="cd-stat-value">{{ $money($totalAmount) }}</p>
        </article>
        <article class="cd-stat is-sky">
            <p class="cd-stat-label">{{ __('Average per entry') }}</p>
            <p class="cd-stat-value">{{ $money($avgAmount) }}</p>
        </article>
    </div>

    {{-- Chart --}}
    <section class="cd-card">
        <header class="cd-card-head">
            <h2>{{ __('Last 12 months') }}</h2>
            <span class="cd-card-note">{{ __('Amount and number of entries') }}</span>
        </header>
        <div class="cd-chart"><canvas id="monthly-chart" height="90"></canvas></div>
    </section>

    {{-- Recent entries --}}
    <section class="cd-card is-flush">
        <header class="cd-card-head is-inset">
            <h2>{{ __('Recent entries') }}</h2>
            <span class="cd-card-note">{{ __('Newest first') }}</span>
        </header>

        @forelse ($recentEntries as $entry)
            <div class="cd-row">
                <div class="cd-row-main">
                    <p class="cd-row-title">{{ $entry->title }}</p>
                    <p class="cd-row-meta">
                        {{ $entry->date?->format('d M Y') }}
                        <span class="cd-dot">·</span>
                        <span class="cd-badge {{ $entry->status === 'fully_approved' ? 'is-ok' : (in_array($entry->status, ['rejected', 'sent_back'], true) ? 'is-bad' : 'is-pending') }}">
                            {{ $entry->statusLabel() }}
                        </span>
                    </p>
                </div>
                <div class="cd-row-side">
                    <span class="cd-amount is-{{ $accent }}">{{ $money($entry->amount) }}</span>
                    <a href="{{ route($entryRouteName, $entry) }}" class="cd-view">{{ __('View') }}</a>
                </div>
            </div>
        @empty
            <p class="cd-empty">{{ __('No entries in this category yet.') }}</p>
        @endforelse
    </section>
</div>

<style>
    .cd-page { display:flex; flex-direction:column; gap:1.15rem; }

    .cd-header { align-items:flex-start; display:flex; gap:.75rem; }
    .cd-back { align-items:center; background:#fff; border:1px solid #e2e8f0; border-radius:9px; color:#64748b; display:flex; flex-shrink:0; height:2rem; justify-content:center; margin-top:.15rem; transition:background .15s ease, color .15s ease; width:2rem; }
    .cd-back:hover { background:#f8fafc; color:#0f172a; }
    .cd-title-row { align-items:center; display:flex; flex-wrap:wrap; gap:.5rem; }
    .cd-title { color:#0f172a; font-size:1.15rem; font-weight:700; letter-spacing:-.02em; }
    .cd-sub { color:#64748b; font-size:.82rem; margin-top:.15rem; }
    .cd-code { background:#f1f5f9; border-radius:5px; color:#475569; font-family:ui-monospace,SFMono-Regular,Consolas,monospace; font-size:.66rem; padding:.12rem .38rem; }

    .cd-status { align-items:center; border:1px solid; border-radius:9999px; display:inline-flex; font-size:.66rem; font-weight:600; gap:.3rem; padding:.14rem .5rem; }
    .cd-status.is-active { background:#ecfdf5; border-color:#a7f3d0; color:#047857; }
    .cd-status.is-inactive { background:#f8fafc; border-color:#e2e8f0; color:#64748b; }
    .cd-status-dot { border-radius:9999px; height:.35rem; width:.35rem; background:currentColor; }

    .cd-stats { display:grid; gap:.85rem; grid-template-columns:1fr; }
    @media (min-width:640px){ .cd-stats{grid-template-columns:repeat(3,minmax(0,1fr))} }
    .cd-stat { background:#fff; border:1px solid #e6ecf4; border-radius:16px; box-shadow:0 1px 2px rgba(15,23,42,.04); padding:1rem 1.1rem; position:relative; overflow:hidden; }
    .cd-stat::before { background:#94a3b8; content:''; height:100%; left:0; position:absolute; top:0; width:3px; }
    .cd-stat.is-emerald::before { background:#059669; }
    .cd-stat.is-amber::before { background:#d97706; }
    .cd-stat.is-sky::before { background:#0284c7; }
    .cd-stat-label { color:#64748b; font-size:.72rem; font-weight:600; }
    .cd-stat-value { color:#0f172a; font-size:1.65rem; font-weight:700; font-variant-numeric:tabular-nums; letter-spacing:-.02em; line-height:1.15; margin-top:.45rem; }

    .cd-card { background:#fff; border:1px solid #e6ecf4; border-radius:16px; box-shadow:0 1px 2px rgba(15,23,42,.04); padding:1.05rem 1.15rem 1.15rem; }
    .cd-card.is-flush { padding:0; overflow:hidden; }
    .cd-card-head { align-items:baseline; border-bottom:1px solid #f1f5f9; display:flex; gap:.75rem; justify-content:space-between; margin-bottom:.9rem; padding-bottom:.7rem; }
    .cd-card-head.is-inset { margin:0; padding:.85rem 1.15rem .7rem; }
    .cd-card-head h2 { color:#0f172a; font-size:.88rem; font-weight:700; }
    .cd-card-note { color:#94a3b8; font-size:.68rem; }
    .cd-chart { min-height:12rem; }

    .cd-row { align-items:center; border-bottom:1px solid #f5f8fb; display:flex; gap:1rem; justify-content:space-between; padding:.7rem 1.15rem; }
    .cd-row:last-child { border-bottom:none; }
    .cd-row:hover { background:#fafcfe; }
    .cd-row-title { color:#0f172a; font-size:.82rem; font-weight:600; }
    .cd-row-meta { align-items:center; color:#94a3b8; display:flex; flex-wrap:wrap; font-size:.7rem; gap:.35rem; margin-top:.2rem; }
    .cd-dot { color:#cbd5e1; }
    .cd-badge { border-radius:9999px; font-size:.62rem; font-weight:600; padding:.1rem .4rem; }
    .cd-badge.is-ok { background:#dcfce7; color:#15803d; }
    .cd-badge.is-pending { background:#fef3c7; color:#b45309; }
    .cd-badge.is-bad { background:#fee2e2; color:#b91c1c; }
    .cd-row-side { align-items:center; display:flex; flex-shrink:0; gap:.7rem; }
    .cd-amount { font-size:.85rem; font-weight:700; font-variant-numeric:tabular-nums; }
    .cd-amount.is-emerald { color:#047857; }
    .cd-amount.is-amber { color:#b45309; }
    .cd-view { background:#fff; border:1px solid #e2e8f0; border-radius:8px; color:#475569; font-size:.68rem; font-weight:600; padding:.28rem .6rem; }
    .cd-view:hover { background:#f8fafc; color:#0f172a; }
    .cd-empty { color:#94a3b8; font-size:.82rem; padding:2rem 1.15rem; text-align:center; }

    .dark .cd-back, .dark .cd-stat, .dark .cd-card, .dark .cd-view { background:#18181b; border-color:#3f3f46; }
    .dark .cd-title, .dark .cd-stat-value, .dark .cd-card-head h2, .dark .cd-row-title { color:#fafafa; }
    .dark .cd-card-head, .dark .cd-row { border-color:#27272a; }
    .dark .cd-row:hover { background:#1f1f23; }
    .dark .cd-code { background:#27272a; color:#a1a1aa; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('monthly-chart');
        if (!canvas || typeof Chart === 'undefined') return;

        const labels  = @json($months->pluck('label')->values());
        const amounts = @json($months->pluck('total')->values());
        const counts  = @json($months->pluck('count')->values());

        const accent = @js($accent === 'amber' ? '#d97706' : '#059669');
        const accentSoft = @js($accent === 'amber' ? 'rgba(217,119,6,.18)' : 'rgba(5,150,105,.18)');

        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: @js(__('Amount')),
                        data: amounts,
                        backgroundColor: accentSoft,
                        borderColor: accent,
                        borderWidth: 1.5,
                        borderRadius: 6,
                        yAxisID: 'y',
                    },
                    {
                        label: @js(__('Entries')),
                        data: counts,
                        type: 'line',
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2,132,199,.08)',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#0284c7',
                        tension: .35,
                        fill: true,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { labels: { color: '#64748b', font: { size: 11 }, boxWidth: 12, usePointStyle: true } },
                    tooltip: {
                        backgroundColor: '#ffffff',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        titleColor: '#0f172a',
                        bodyColor: '#475569',
                        padding: 10,
                        displayColors: true,
                    },
                },
                scales: {
                    x: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { display: false } },
                    y: {
                        position: 'left', beginAtZero: true,
                        ticks: { color: '#94a3b8', font: { size: 10 } },
                        grid: { color: '#f1f5f9' },
                    },
                    y1: {
                        position: 'right', beginAtZero: true,
                        ticks: { color: '#94a3b8', font: { size: 10 }, precision: 0 },
                        grid: { drawOnChartArea: false },
                    },
                },
            },
        });
    });
</script>
