<x-layouts::app :title="__('Website Analytics')">
    @php
        $fmt = fn ($n) => number_format((float) $n);
        $maxViews = max(1, $daily->max('views'));
        $chartW = 900;
        $chartH = 220;
        $padL = 42;
        $padB = 26;
        $plotW = $chartW - $padL - 10;
        $plotH = $chartH - $padB - 12;
        $step = $daily->count() > 1 ? $plotW / ($daily->count() - 1) : 0;

        $pointsViews = $daily->values()->map(fn ($d, $i) =>
            round($padL + $i * $step, 1) . ',' . round(12 + $plotH - ($d['views'] / $maxViews) * $plotH, 1)
        )->implode(' ');

        $pointsVisitors = $daily->values()->map(fn ($d, $i) =>
            round($padL + $i * $step, 1) . ',' . round(12 + $plotH - ($d['visitors'] / $maxViews) * $plotH, 1)
        )->implode(' ');

        // Label every Nth day so the axis never collides with itself.
        $labelEvery = max(1, (int) ceil($daily->count() / 12));
        $maxHour = max(1, $byHour->max('views'));
    @endphp

    <div class="space-y-5">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Website Analytics') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Traffic to the public site, recorded here — no Google account needed.') }}
                    <span class="text-zinc-400">{{ $from->format('d M Y') }} &ndash; {{ $to->format('d M Y') }}</span>
                </p>
            </div>

            <form method="GET" class="flex flex-wrap items-center gap-2">
                @foreach ($ranges as $value => $label)
                    <a href="{{ route('admin.analytics.index', ['range' => $value]) }}"
                       @class(['an-range', 'is-active' => $range === $value])>{{ $label }}</a>
                @endforeach
                <span class="an-sep"></span>
                <input type="date" name="from" value="{{ $from->toDateString() }}" class="an-date">
                <input type="date" name="to" value="{{ $to->toDateString() }}" class="an-date">
                <button type="submit" class="an-apply">{{ __('Apply') }}</button>
            </form>
        </div>

        @unless ($hasAnyData)
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">
                <p class="font-semibold">{{ __('No views recorded yet') }}</p>
                <p class="mt-1 text-sky-800">
                    {{ __('Tracking starts from now. Visits to the public site are counted automatically — logged-in staff and known bots are excluded, so your own browsing will not appear here.') }}
                </p>
            </div>
        @endunless

        {{-- Headline numbers --}}
        <div class="an-tiles">
            @foreach ([
                ['label' => __('Page views'), 'key' => 'views', 'accent' => 'blue', 'icon' => 'M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z'],
                ['label' => __('Unique visitors'), 'key' => 'visitors', 'accent' => 'green', 'icon' => 'M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.97 5.97 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z'],
                ['label' => __('Sessions'), 'key' => 'sessions', 'accent' => 'violet', 'icon' => 'M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z'],
            ] as $tile)
                @php($stat = $summary[$tile['key']])
                <article class="an-tile is-{{ $tile['accent'] }}">
                    <header class="an-tile-head">
                        <span class="an-tile-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="{{ $tile['icon'] }}" clip-rule="evenodd"/></svg>
                        </span>
                        <p class="an-tile-label">{{ $tile['label'] }}</p>
                    </header>

                    <p class="an-tile-value">{{ $fmt($stat['value']) }}</p>

                    <footer class="an-tile-foot">
                        @if ($stat['change'] !== null)
                            <span @class(['an-delta', 'is-up' => $stat['change'] >= 0, 'is-down' => $stat['change'] < 0])>
                                @if ($stat['change'] >= 0)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3"><path fill-rule="evenodd" d="M10 17a1 1 0 01-1-1V6.41L5.7 9.71a1 1 0 01-1.4-1.42l5-5a1 1 0 011.4 0l5 5a1 1 0 11-1.4 1.42L11 6.4V16a1 1 0 01-1 1z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v9.59l3.3-3.3a1 1 0 111.4 1.42l-5 5a1 1 0 01-1.4 0l-5-5a1 1 0 111.4-1.42L9 13.6V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                                @endif
                                {{ abs($stat['change']) }}%
                            </span>
                            {{-- diffInDays returns a float, so an unrounded value would print "29.999 days" --}}
                            <span class="an-delta-note">{{ __('vs previous :n days', ['n' => max(1, (int) round($from->diffInDays($to)) + 1)]) }}</span>
                        @else
                            <span class="an-delta is-flat">{{ __('No earlier data to compare') }}</span>
                        @endif
                    </footer>
                </article>
            @endforeach

            <article class="an-tile is-amber">
                <header class="an-tile-head">
                    <span class="an-tile-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0 1 1 0 002 0zm-1 3a1 1 0 011 1v3a1 1 0 11-2 0v-3a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    </span>
                    <p class="an-tile-label">{{ __('Views per visitor') }}</p>
                </header>

                <p class="an-tile-value">{{ $summary['views_per_visitor']['value'] }}</p>

                <footer class="an-tile-foot">
                    <span class="an-delta-note">{{ $fmt($summary['bot_views']) }} {{ __('bot views filtered out') }}</span>
                </footer>
            </article>
        </div>

        {{-- Traffic over time --}}
        <section class="an-card">
            <header class="an-card-head">
                <h2>{{ __('Traffic over time') }}</h2>
                <div class="an-legend">
                    <span><i class="an-key is-views"></i>{{ __('Views') }}</span>
                    <span><i class="an-key is-visitors"></i>{{ __('Visitors') }}</span>
                </div>
            </header>

            <div class="an-chart-wrap">
                <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="an-chart" role="img"
                     aria-label="{{ __('Daily page views and unique visitors over the selected period') }}">
                    {{-- horizontal gridlines + y axis --}}
                    @foreach ([0, 0.25, 0.5, 0.75, 1] as $frac)
                        @php($y = 12 + $plotH - ($frac * $plotH))
                        <line x1="{{ $padL }}" y1="{{ $y }}" x2="{{ $chartW - 10 }}" y2="{{ $y }}" class="an-grid"/>
                        <text x="{{ $padL - 8 }}" y="{{ $y + 3.5 }}" class="an-axis" text-anchor="end">{{ $fmt(round($maxViews * $frac)) }}</text>
                    @endforeach

                    @if ($daily->count() > 1)
                        <polyline points="{{ $pointsViews }}" class="an-line is-views"/>
                        <polyline points="{{ $pointsVisitors }}" class="an-line is-visitors"/>
                    @endif

                    {{-- points + x axis labels --}}
                    @foreach ($daily->values() as $i => $d)
                        @php($x = $padL + $i * $step)
                        @php($y = 12 + $plotH - ($d['views'] / $maxViews) * $plotH)
                        <circle cx="{{ round($x, 1) }}" cy="{{ round($y, 1) }}" r="2.5" class="an-dot is-views">
                            <title>{{ $d['label'] }} — {{ $fmt($d['views']) }} views, {{ $fmt($d['visitors']) }} visitors</title>
                        </circle>
                        @if ($i % $labelEvery === 0)
                            <text x="{{ round($x, 1) }}" y="{{ $chartH - 8 }}" class="an-axis" text-anchor="middle">{{ $d['label'] }}</text>
                        @endif
                    @endforeach
                </svg>
            </div>
        </section>

        <div class="an-pair">
            {{-- Top pages --}}
            <section class="an-card">
                <header class="an-card-head"><h2>{{ __('Most viewed pages') }}</h2></header>
                @if ($topPages->isEmpty())
                    <p class="an-none">{{ __('Nothing yet.') }}</p>
                @else
                    <table class="an-table">
                        <thead><tr><th>{{ __('Page') }}</th><th class="num">{{ __('Views') }}</th><th class="num">{{ __('Visitors') }}</th></tr></thead>
                        <tbody>
                            @foreach ($topPages as $page)
                                <tr>
                                    <td>
                                        <span class="an-path">{{ $page->path }}</span>
                                        @if ($page->title)
                                            <span class="an-subtle">{{ Str::limit($page->title, 60) }}</span>
                                        @endif
                                    </td>
                                    <td class="num">{{ $fmt($page->views) }}</td>
                                    <td class="num">{{ $fmt($page->visitors) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            {{-- Where they came from --}}
            <section class="an-card">
                <header class="an-card-head"><h2>{{ __('Where visitors came from') }}</h2></header>
                @if ($referrers->isEmpty())
                    <p class="an-none">{{ __('All traffic so far is direct or has no referrer.') }}</p>
                @else
                    <table class="an-table">
                        <thead><tr><th>{{ __('Source') }}</th><th class="num">{{ __('Views') }}</th></tr></thead>
                        <tbody>
                            @foreach ($referrers as $ref)
                                <tr><td><span class="an-path">{{ $ref->referrer_host }}</span></td><td class="num">{{ $fmt($ref->views) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if ($campaigns->isNotEmpty())
                    <h3 class="an-sub">{{ __('Campaigns') }}</h3>
                    <table class="an-table">
                        <thead><tr><th>{{ __('Source / Medium') }}</th><th>{{ __('Campaign') }}</th><th class="num">{{ __('Views') }}</th></tr></thead>
                        <tbody>
                            @foreach ($campaigns as $c)
                                <tr>
                                    <td><span class="an-path">{{ $c->utm_source }}{{ $c->utm_medium ? ' / '.$c->utm_medium : '' }}</span></td>
                                    <td class="an-subtle">{{ $c->utm_campaign ?? '—' }}</td>
                                    <td class="num">{{ $fmt($c->views) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            {{-- Courses --}}
            <section class="an-card">
                <header class="an-card-head"><h2>{{ __('Most viewed courses') }}</h2></header>
                @if ($topCourses->isEmpty())
                    <p class="an-none">{{ __('No course pages viewed in this period.') }}</p>
                @else
                    <table class="an-table">
                        <thead><tr><th>{{ __('Course') }}</th><th class="num">{{ __('Views') }}</th><th class="num">{{ __('Visitors') }}</th></tr></thead>
                        <tbody>
                            @foreach ($topCourses as $row)
                                <tr>
                                    <td>
                                        <span class="an-path">{{ $row->display_name }}</span>
                                        @unless ($row->still_exists)<span class="an-gone">{{ __('deleted') }}</span>@endunless
                                    </td>
                                    <td class="num">{{ $fmt($row->views) }}</td>
                                    <td class="num">{{ $fmt($row->visitors) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>

            {{-- Services --}}
            <section class="an-card">
                <header class="an-card-head"><h2>{{ __('Most viewed services') }}</h2></header>
                @if ($topServices->isEmpty())
                    <p class="an-none">{{ __('No service pages viewed in this period.') }}</p>
                @else
                    <table class="an-table">
                        <thead><tr><th>{{ __('Service') }}</th><th class="num">{{ __('Views') }}</th><th class="num">{{ __('Visitors') }}</th></tr></thead>
                        <tbody>
                            @foreach ($topServices as $row)
                                <tr>
                                    <td>
                                        <span class="an-path">{{ $row->display_name }}</span>
                                        @unless ($row->still_exists)<span class="an-gone">{{ __('deleted') }}</span>@endunless
                                    </td>
                                    <td class="num">{{ $fmt($row->views) }}</td>
                                    <td class="num">{{ $fmt($row->visitors) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        </div>

        {{-- Audience breakdowns --}}
        <div class="an-trio">
            @foreach ([
                ['title' => __('Devices'), 'rows' => $devices],
                ['title' => __('Browsers'), 'rows' => $browsers],
                ['title' => __('Platforms'), 'rows' => $platforms],
            ] as $panel)
                <section class="an-card">
                    <header class="an-card-head"><h2>{{ $panel['title'] }}</h2></header>
                    @if ($panel['rows']->isEmpty())
                        <p class="an-none">{{ __('Nothing yet.') }}</p>
                    @else
                        <ul class="an-bars">
                            @foreach ($panel['rows'] as $row)
                                <li>
                                    <div class="an-bar-top">
                                        <span>{{ ucfirst($row->label) }}</span>
                                        <span class="an-subtle">{{ $fmt($row->views) }} · {{ $row->share }}%</span>
                                    </div>
                                    <div class="an-bar-track"><span class="an-bar-fill" style="width: {{ $row->share }}%"></span></div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
        </div>

        {{-- Time of day --}}
        <section class="an-card">
            <header class="an-card-head"><h2>{{ __('Busiest time of day') }}</h2></header>
            <div class="an-hours">
                @foreach ($byHour as $h)
                    <div class="an-hour" title="{{ $h['label'] }} — {{ $fmt($h['views']) }} views">
                        <span class="an-hour-bar" style="height: {{ max(2, round(($h['views'] / $maxHour) * 100)) }}%"></span>
                        @if ($h['hour'] % 3 === 0)
                            <span class="an-hour-label">{{ $h['hour'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Latest views --}}
        <section class="an-card">
            <header class="an-card-head"><h2>{{ __('Latest views') }}</h2></header>
            @if ($recent->isEmpty())
                <p class="an-none">{{ __('Nothing yet.') }}</p>
            @else
                <table class="an-table">
                    <thead><tr><th>{{ __('Page') }}</th><th>{{ __('Device') }}</th><th>{{ __('Source') }}</th><th class="num">{{ __('When') }}</th></tr></thead>
                    <tbody>
                        @foreach ($recent as $view)
                            <tr>
                                <td><span class="an-path">{{ $view->path }}</span></td>
                                <td class="an-subtle">{{ ucfirst((string) $view->device) }}{{ $view->browser ? ' · '.$view->browser : '' }}</td>
                                <td class="an-subtle">{{ $view->referrer_host ?? __('Direct') }}</td>
                                <td class="num an-subtle">{{ $view->viewed_at->diffForHumans(short: true) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </div>

    <style>
        .an-range { border:1px solid #dbe4f0; border-radius:9px; color:#475569; font-size:.7rem; font-weight:600; padding:.35rem .6rem; }
        .an-range:hover { background:#f8fafc; }
        .an-range.is-active { background:#0284c7; border-color:#0284c7; color:#fff; }
        .an-sep { background:#e2e8f0; height:1.2rem; width:1px; }
        .an-date { border:1px solid #dbe4f0; border-radius:9px; font-size:.7rem; padding:.32rem .45rem; }
        .an-apply { background:#0f172a; border-radius:9px; color:#fff; font-size:.7rem; font-weight:600; padding:.36rem .7rem; }

        /* Own grid, so the layout does not depend on Tailwind classes being
           present in the compiled stylesheet. */
        .an-tiles { display:grid; gap:.85rem; grid-template-columns:1fr; }
        .an-pair { display:grid; gap:1.15rem; grid-template-columns:1fr; }
        .an-trio { display:grid; gap:1.15rem; grid-template-columns:1fr; }
        @media (min-width:640px){ .an-tiles{grid-template-columns:repeat(2,minmax(0,1fr))} }
        @media (min-width:1024px){ .an-trio{grid-template-columns:repeat(3,minmax(0,1fr))} }
        @media (min-width:1280px){
            .an-tiles{grid-template-columns:repeat(4,minmax(0,1fr))}
            .an-pair{grid-template-columns:repeat(2,minmax(0,1fr))}
        }

        /* ---- KPI tiles ---- */
        .an-tile {
            background:#fff; border:1px solid #e6ecf4; border-radius:16px;
            box-shadow:0 1px 2px rgba(15,23,42,.04);
            display:flex; flex-direction:column; gap:.15rem;
            padding:1rem 1.05rem .9rem; position:relative; overflow:hidden;
            transition:box-shadow .18s ease, border-color .18s ease, transform .18s ease;
        }
        .an-tile::before { content:''; height:100%; left:0; position:absolute; top:0; width:3px; }
        .an-tile:hover { border-color:#d3e0f0; box-shadow:0 6px 18px rgba(15,23,42,.07); transform:translateY(-1px); }
        .an-tile.is-blue::before   { background:#0284c7; }
        .an-tile.is-green::before  { background:#059669; }
        .an-tile.is-violet::before { background:#7c3aed; }
        .an-tile.is-amber::before  { background:#d97706; }

        .an-tile-head { align-items:center; display:flex; gap:.45rem; }
        .an-tile-icon { align-items:center; border-radius:8px; display:flex; height:1.5rem; justify-content:center; width:1.5rem; }
        .an-tile.is-blue   .an-tile-icon { background:#e0f2fe; color:#0369a1; }
        .an-tile.is-green  .an-tile-icon { background:#d1fae5; color:#047857; }
        .an-tile.is-violet .an-tile-icon { background:#ede9fe; color:#6d28d9; }
        .an-tile.is-amber  .an-tile-icon { background:#fef3c7; color:#b45309; }
        .an-tile-label { color:#64748b; font-size:.72rem; font-weight:600; letter-spacing:.01em; }

        .an-tile-value { color:#0f172a; font-size:2rem; font-weight:700; font-variant-numeric:tabular-nums; letter-spacing:-.02em; line-height:1.15; margin-top:.5rem; }

        .an-tile-foot { align-items:center; display:flex; flex-wrap:wrap; gap:.4rem; margin-top:.55rem; }
        .an-delta { align-items:center; border-radius:9999px; display:inline-flex; font-size:.66rem; font-weight:700; gap:.15rem; padding:.14rem .4rem; }
        .an-delta.is-up { background:#dcfce7; color:#15803d; }
        .an-delta.is-down { background:#fee2e2; color:#b91c1c; }
        .an-delta.is-flat { background:transparent; color:#94a3b8; font-weight:500; padding:0; }
        .an-delta-note { color:#94a3b8; font-size:.66rem; font-weight:500; }

        /* ---- Panels ---- */
        .an-card { background:#fff; border:1px solid #e6ecf4; border-radius:16px; box-shadow:0 1px 2px rgba(15,23,42,.04); padding:1.05rem 1.15rem 1.15rem; }
        .an-card-head { align-items:center; border-bottom:1px solid #f1f5f9; display:flex; gap:1rem; justify-content:space-between; margin-bottom:.9rem; padding-bottom:.7rem; }
        .an-card-head h2 { color:#0f172a; font-size:.88rem; font-weight:700; letter-spacing:-.01em; }
        .an-sub { color:#64748b; font-size:.68rem; font-weight:700; letter-spacing:.06em; margin:1rem 0 .4rem; text-transform:uppercase; }
        .an-none { color:#94a3b8; font-size:.75rem; font-style:italic; padding:.6rem 0; }

        .an-chart-wrap { overflow-x:auto; }
        .an-chart { display:block; height:auto; min-width:640px; width:100%; }
        .an-grid { stroke:#eef2f7; stroke-width:1; }
        .an-axis { fill:#94a3b8; font-size:9px; }
        .an-line { fill:none; stroke-width:2; stroke-linejoin:round; stroke-linecap:round; }
        .an-line.is-views { stroke:#0284c7; }
        .an-line.is-visitors { stroke:#10b981; stroke-dasharray:4 3; }
        .an-dot.is-views { fill:#0284c7; }
        .an-legend { display:flex; gap:.8rem; }
        .an-legend span { align-items:center; color:#64748b; display:flex; font-size:.66rem; gap:.3rem; }
        .an-key { border-radius:2px; display:inline-block; height:.55rem; width:.9rem; }
        .an-key.is-views { background:#0284c7; }
        .an-key.is-visitors { background:#10b981; }

        .an-table { border-collapse:collapse; font-size:.74rem; width:100%; }
        .an-table th { border-bottom:1px solid #eef2f7; color:#94a3b8; font-size:.64rem; font-weight:700; letter-spacing:.05em; padding:.35rem .4rem; text-align:left; text-transform:uppercase; }
        .an-table td { border-bottom:1px solid #f8fafc; padding:.45rem .4rem; vertical-align:top; }
        .an-table tr:last-child td { border-bottom:none; }
        .an-table .num { font-variant-numeric:tabular-nums; text-align:right; white-space:nowrap; }
        .an-path { color:#0f172a; display:block; font-weight:600; }
        .an-subtle { color:#94a3b8; display:block; font-size:.68rem; font-weight:400; }
        .an-gone { background:#fee2e2; border-radius:9999px; color:#b91c1c; font-size:.58rem; margin-left:.3rem; padding:.08rem .32rem; }

        .an-bars { display:flex; flex-direction:column; gap:.6rem; list-style:none; margin:0; padding:0; }
        .an-bar-top { display:flex; font-size:.72rem; justify-content:space-between; margin-bottom:.22rem; }
        .an-bar-top span:first-child { color:#334155; font-weight:600; }
        .an-bar-track { background:#f1f5f9; border-radius:9999px; height:.4rem; overflow:hidden; }
        .an-bar-fill { background:#0284c7; border-radius:9999px; display:block; height:100%; }

        .an-hours { align-items:flex-end; display:flex; gap:.25rem; height:7rem; }
        .an-hour { display:flex; flex:1; flex-direction:column; height:100%; justify-content:flex-end; position:relative; }
        .an-hour-bar { background:#bae6fd; border-radius:3px 3px 0 0; display:block; width:100%; }
        .an-hour:hover .an-hour-bar { background:#0284c7; }
        .an-hour-label { color:#cbd5e1; font-size:.55rem; position:absolute; text-align:center; top:100%; width:100%; }

        .dark .an-tile, .dark .an-card { background:#18181b; border-color:#3f3f46; }
        .dark .an-tile-label { color:#a1a1aa; }
        .dark .an-tile-value, .dark .an-card-head h2, .dark .an-path { color:#fafafa; }
        .dark .an-card-head { border-color:#27272a; }
        .dark .an-grid { stroke:#27272a; }
        .dark .an-table th { border-color:#27272a; }
        .dark .an-table td { border-color:#1f1f22; }
        .dark .an-bar-track { background:#27272a; }
        .dark .an-range { border-color:#3f3f46; color:#d4d4d8; }
        .dark .an-range:hover { background:#27272a; }
        .dark .an-date { background:#09090b; border-color:#3f3f46; color:#fafafa; }
    </style>
</x-layouts::app>
