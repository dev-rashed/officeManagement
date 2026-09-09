<x-layouts::app :title="__('Dashboard')">
    @php
        $money = fn ($n) => '৳ ' . number_format((float) $n, 2);
        $user = auth()->user();
        $isContributor = in_array($user->role, \App\Models\IncomeEntry::CONTRIBUTOR_ROLES, true)
            || $contributions['count'] > 0;
    @endphp

    <div class="db-page">

        <header class="db-head">
            <div>
                <h1 class="db-title">{{ __('Welcome back, :name', ['name' => explode(' ', $user->name)[0]]) }}</h1>
                <p class="db-sub">{{ now()->format('l, d F Y') }}</p>
            </div>
            <span class="db-role">{{ ucwords(str_replace('_', ' ', (string) $user->role)) }}</span>
        </header>

        {{-- Things waiting on this person --}}
        @if (! empty($attention))
            <section class="db-attention">
                @foreach ($attention as $item)
                    <a href="{{ $item['url'] }}" class="db-alert is-{{ $item['tone'] }}">
                        <span class="db-alert-icon">
                            @if ($item['kind'] === 'approval')
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.5 2.5a1 1 0 001.414-1.414L11 9.586V6z" clip-rule="evenodd"/></svg>
                            @elseif ($item['kind'] === 'sent_back')
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M7.793 2.232a.75.75 0 01-.025 1.06L3.622 7.25h10.003a5.375 5.375 0 010 10.75H10.75a.75.75 0 010-1.5h2.875a3.875 3.875 0 000-7.75H3.622l4.146 3.957a.75.75 0 01-1.036 1.085l-5.5-5.25a.75.75 0 010-1.085l5.5-5.25a.75.75 0 011.06.025z" clip-rule="evenodd"/></svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9z"/></svg>
                            @endif
                        </span>
                        <span class="db-alert-body">
                            <span class="db-alert-title">{{ $item['title'] }}</span>
                            <span class="db-alert-note">{{ $item['note'] }}</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="db-alert-arrow size-4"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </a>
                @endforeach
            </section>
        @endif

        {{-- Personal money position --}}
        <section class="db-money">
            @if ($isContributor)
                <article class="db-card is-emerald">
                    <header class="db-card-head">
                        <span class="db-card-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/></svg>
                        </span>
                        <p class="db-card-label">{{ __('You have contributed') }}</p>
                    </header>
                    <p class="db-card-value">{{ $money($contributions['total']) }}</p>
                    <footer class="db-card-foot">
                        {{ trans_choice('{0} No contributions yet|{1} :count contribution|[2,*] :count contributions', $contributions['count'], ['count' => $contributions['count']]) }}
                        @if ($contributions['pending'] > 0)
                            <span class="db-chip is-amber">{{ $money($contributions['pending']) }} {{ __('awaiting approval') }}</span>
                        @endif
                    </footer>
                </article>

                <article class="db-card">
                    <header class="db-card-head">
                        <span class="db-card-icon is-plain">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                        </span>
                        <p class="db-card-label">{{ __('This year') }}</p>
                    </header>
                    <p class="db-card-value">{{ $money($contributions['thisYear']) }}</p>
                    <footer class="db-card-foot">{{ __('Approved contributions in :year', ['year' => now()->year]) }}</footer>
                </article>
            @endif

            <article class="db-card {{ $reimbursements['owed'] > 0 ? 'is-amber' : '' }}">
                <header class="db-card-head">
                    <span class="db-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 3a1 1 0 000 2h8a1 1 0 100-2H6zm0 4a1 1 0 100 2h4a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                    </span>
                    <p class="db-card-label">{{ __('The office owes you') }}</p>
                </header>
                <p class="db-card-value">{{ $money($reimbursements['owed']) }}</p>
                <footer class="db-card-foot">
                    @if ($reimbursements['owedCount'] > 0)
                        {{ trans_choice('{1} :count claim awaiting payment|[2,*] :count claims awaiting payment', $reimbursements['owedCount'], ['count' => $reimbursements['owedCount']]) }}
                    @else
                        {{ __('Nothing outstanding') }}
                    @endif
                    @if ($reimbursements['reimbursed'] > 0)
                        <span class="db-chip is-green">{{ $money($reimbursements['reimbursed']) }} {{ __('paid back') }}</span>
                    @endif
                </footer>
            </article>
        </section>

        <div class="db-split">
            {{-- Running projects --}}
            <section class="db-panel">
                <header class="db-panel-head">
                    <h2>{{ __('Running projects') }}</h2>
                    @can('projects.view')
                        <a href="{{ route('projects.index') }}" class="db-link">{{ __('All projects') }}</a>
                    @endcan
                </header>

                @forelse ($projects as $project)
                    <div class="db-project">
                        <div class="db-project-top">
                            <span class="db-project-name">{{ $project->title }}</span>
                            <span class="db-pill is-{{ $project->status === 'active' ? 'green' : 'slate' }}">{{ $project->statusLabel() }}</span>
                        </div>

                        <div class="db-project-meta">
                            <span class="db-students">
                                <strong>{{ number_format($project->trainees_count) }}</strong>
                                {{ trans_choice('{1} student|[2,*] students', $project->trainees_count) }}
                            </span>
                            @if ($project->targeted_trainees)
                                <span class="db-dot">·</span>
                                <span>{{ __('target :n', ['n' => number_format($project->targeted_trainees)]) }}</span>
                            @endif
                            @if ($project->end_date)
                                <span class="db-dot">·</span>
                                <span>{{ __('ends :date', ['date' => $project->end_date->format('M Y')]) }}</span>
                            @endif
                        </div>

                        @if ($project->progress !== null)
                            <div class="db-bar" title="{{ $project->progress }}% {{ __('of target') }}">
                                <span class="db-bar-fill" style="width: {{ $project->progress }}%"></span>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="db-empty">{{ __('No projects are running at the moment.') }}</p>
                @endforelse
            </section>

            {{-- Notifications --}}
            <section class="db-panel">
                <header class="db-panel-head">
                    <h2>
                        {{ __('Notifications') }}
                        @if ($unreadCount > 0)<span class="db-count">{{ $unreadCount }}</span>@endif
                    </h2>
                    <a href="{{ route('notifications.index') }}" class="db-link">{{ __('See all') }}</a>
                </header>

                @forelse ($notifications as $notification)
                    @php($d = $notification->data)
                    <a href="{{ $d['url'] ?? route('notifications.index') }}" class="db-note {{ $notification->read_at ? '' : 'is-unread' }}">
                        <span class="db-note-dot is-{{ ($d['entry_type'] ?? '') === 'income' ? 'green' : 'amber' }}"></span>
                        <span class="db-note-body">
                            <span class="db-note-title">{{ $d['title'] ?? __('Notification') }}</span>
                            <span class="db-note-msg">{{ \Illuminate\Support\Str::limit($d['message'] ?? '', 90) }}</span>
                        </span>
                        <span class="db-note-ago">{{ $notification->created_at->diffForHumans(short: true) }}</span>
                    </a>
                @empty
                    <p class="db-empty">{{ __('Nothing to report yet.') }}</p>
                @endforelse
            </section>
        </div>

        {{-- Recent personal activity --}}
        @if ($contributions['recent']->isNotEmpty() || $reimbursements['recent']->isNotEmpty())
            <div class="db-split">
                @if ($contributions['recent']->isNotEmpty())
                    <section class="db-panel">
                        <header class="db-panel-head"><h2>{{ __('Your recent contributions') }}</h2></header>
                        @foreach ($contributions['recent'] as $entry)
                            <a href="{{ route('income.show', $entry) }}" class="db-row">
                                <span class="db-row-main">
                                    <span class="db-row-title">{{ $entry->title }}</span>
                                    <span class="db-row-meta">
                                        {{ $entry->date?->format('d M Y') }}
                                        @if ($entry->project)<span class="db-dot">·</span>{{ $entry->project->title }}@endif
                                    </span>
                                </span>
                                <span class="db-row-amount is-green">{{ $money($entry->amount) }}</span>
                            </a>
                        @endforeach
                    </section>
                @endif

                @if ($reimbursements['recent']->isNotEmpty())
                    <section class="db-panel">
                        <header class="db-panel-head"><h2>{{ __('Your recent claims') }}</h2></header>
                        @foreach ($reimbursements['recent'] as $entry)
                            <a href="{{ route('expense.show', $entry) }}" class="db-row">
                                <span class="db-row-main">
                                    <span class="db-row-title">{{ $entry->title }}</span>
                                    <span class="db-row-meta">
                                        {{ $entry->date?->format('d M Y') }}
                                        <span class="db-dot">·</span>
                                        <span class="db-pill is-{{ $entry->isReimbursed() ? 'green' : 'amber' }}">{{ $entry->reimbursementLabel() }}</span>
                                    </span>
                                </span>
                                <span class="db-row-amount is-amber">{{ $money($entry->amount) }}</span>
                            </a>
                        @endforeach
                    </section>
                @endif
            </div>
        @endif
    </div>

    <style>
        .db-page { display:flex; flex-direction:column; gap:1.15rem; }

        .db-head { align-items:flex-start; display:flex; gap:1rem; justify-content:space-between; }
        .db-title { color:#0f172a; font-size:1.3rem; font-weight:700; letter-spacing:-.02em; }
        .db-sub { color:#64748b; font-size:.82rem; margin-top:.15rem; }
        .db-role { background:#f1f5f9; border-radius:9999px; color:#475569; flex-shrink:0; font-size:.68rem; font-weight:600; padding:.25rem .6rem; }

        .db-attention { display:flex; flex-direction:column; gap:.55rem; }
        .db-alert { align-items:center; border:1px solid; border-radius:12px; display:flex; gap:.7rem; padding:.7rem .9rem; transition:transform .12s ease; }
        .db-alert:hover { transform:translateX(2px); }
        .db-alert.is-amber { background:#fffbeb; border-color:#fde68a; }
        .db-alert.is-rose { background:#fef2f2; border-color:#fecaca; }
        .db-alert.is-sky { background:#f0f9ff; border-color:#bae6fd; }
        .db-alert-icon { align-items:center; border-radius:9px; display:flex; flex-shrink:0; height:1.9rem; justify-content:center; width:1.9rem; }
        .db-alert.is-amber .db-alert-icon { background:#fef3c7; color:#b45309; }
        .db-alert.is-rose .db-alert-icon { background:#fee2e2; color:#b91c1c; }
        .db-alert.is-sky .db-alert-icon { background:#e0f2fe; color:#0369a1; }
        .db-alert-body { display:flex; flex:1; flex-direction:column; min-width:0; }
        .db-alert-title { font-size:.82rem; font-weight:600; }
        .db-alert.is-amber .db-alert-title { color:#92400e; }
        .db-alert.is-rose .db-alert-title { color:#991b1b; }
        .db-alert.is-sky .db-alert-title { color:#075985; }
        .db-alert-note { font-size:.72rem; opacity:.85; }
        .db-alert-arrow { flex-shrink:0; opacity:.5; }

        .db-money { display:grid; gap:.85rem; grid-template-columns:1fr; }
        @media (min-width:640px){ .db-money{grid-template-columns:repeat(2,minmax(0,1fr))} }
        @media (min-width:1024px){ .db-money{grid-template-columns:repeat(3,minmax(0,1fr))} }
        .db-card { background:#fff; border:1px solid #e6ecf4; border-radius:16px; box-shadow:0 1px 2px rgba(15,23,42,.04); overflow:hidden; padding:1rem 1.05rem .9rem; position:relative; }
        .db-card::before { background:#cbd5e1; content:''; height:100%; left:0; position:absolute; top:0; width:3px; }
        .db-card.is-emerald::before { background:#059669; }
        .db-card.is-amber::before { background:#d97706; }
        .db-card-head { align-items:center; display:flex; gap:.45rem; }
        .db-card-icon { align-items:center; background:#e0f2fe; border-radius:8px; color:#0369a1; display:flex; height:1.5rem; justify-content:center; width:1.5rem; }
        .db-card.is-emerald .db-card-icon { background:#d1fae5; color:#047857; }
        .db-card.is-amber .db-card-icon { background:#fef3c7; color:#b45309; }
        .db-card-icon.is-plain { background:#f1f5f9; color:#64748b; }
        .db-card-label { color:#64748b; font-size:.72rem; font-weight:600; }
        .db-card-value { color:#0f172a; font-size:1.7rem; font-weight:700; font-variant-numeric:tabular-nums; letter-spacing:-.02em; line-height:1.15; margin-top:.5rem; }
        .db-card-foot { align-items:center; color:#94a3b8; display:flex; flex-wrap:wrap; font-size:.7rem; gap:.4rem; margin-top:.5rem; }
        .db-chip { border-radius:9999px; font-size:.64rem; font-weight:600; padding:.1rem .4rem; }
        .db-chip.is-amber { background:#fef3c7; color:#b45309; }
        .db-chip.is-green { background:#dcfce7; color:#15803d; }

        .db-split { display:grid; gap:1.15rem; grid-template-columns:1fr; }
        @media (min-width:1024px){ .db-split{grid-template-columns:repeat(2,minmax(0,1fr))} }
        .db-panel { background:#fff; border:1px solid #e6ecf4; border-radius:16px; box-shadow:0 1px 2px rgba(15,23,42,.04); overflow:hidden; }
        .db-panel-head { align-items:center; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; padding:.85rem 1.05rem .7rem; }
        .db-panel-head h2 { align-items:center; color:#0f172a; display:flex; font-size:.88rem; font-weight:700; gap:.4rem; }
        .db-count { background:#0284c7; border-radius:9999px; color:#fff; font-size:.62rem; font-weight:700; padding:.08rem .38rem; }
        .db-link { color:#0284c7; font-size:.7rem; font-weight:600; }
        .db-link:hover { text-decoration:underline; }

        .db-project { border-bottom:1px solid #f5f8fb; padding:.75rem 1.05rem; }
        .db-project:last-child { border-bottom:none; }
        .db-project-top { align-items:center; display:flex; gap:.5rem; justify-content:space-between; }
        .db-project-name { color:#0f172a; font-size:.82rem; font-weight:600; }
        .db-pill { border-radius:9999px; flex-shrink:0; font-size:.62rem; font-weight:600; padding:.1rem .42rem; }
        .db-pill.is-green { background:#dcfce7; color:#15803d; }
        .db-pill.is-slate { background:#f1f5f9; color:#64748b; }
        .db-pill.is-amber { background:#fef3c7; color:#b45309; }
        .db-project-meta { align-items:center; color:#94a3b8; display:flex; flex-wrap:wrap; font-size:.72rem; gap:.3rem; margin-top:.25rem; }
        .db-students strong { color:#0f172a; font-size:.8rem; }
        .db-dot { color:#cbd5e1; }
        .db-bar { background:#f1f5f9; border-radius:9999px; height:.3rem; margin-top:.5rem; overflow:hidden; }
        .db-bar-fill { background:#0284c7; border-radius:9999px; display:block; height:100%; }

        .db-note { align-items:flex-start; border-bottom:1px solid #f5f8fb; display:flex; gap:.6rem; padding:.7rem 1.05rem; }
        .db-note:last-child { border-bottom:none; }
        .db-note:hover { background:#fafcfe; }
        .db-note.is-unread { background:#f0f9ff; }
        .db-note-dot { border-radius:9999px; flex-shrink:0; height:.45rem; margin-top:.4rem; width:.45rem; }
        .db-note-dot.is-green { background:#059669; }
        .db-note-dot.is-amber { background:#d97706; }
        .db-note-body { display:flex; flex:1; flex-direction:column; min-width:0; }
        .db-note-title { color:#0f172a; font-size:.78rem; font-weight:600; }
        .db-note-msg { color:#64748b; font-size:.7rem; line-height:1.35; margin-top:.1rem; }
        .db-note-ago { color:#cbd5e1; flex-shrink:0; font-size:.65rem; }

        .db-row { align-items:center; border-bottom:1px solid #f5f8fb; display:flex; gap:1rem; justify-content:space-between; padding:.65rem 1.05rem; }
        .db-row:last-child { border-bottom:none; }
        .db-row:hover { background:#fafcfe; }
        .db-row-main { display:flex; flex-direction:column; min-width:0; }
        .db-row-title { color:#0f172a; font-size:.8rem; font-weight:600; }
        .db-row-meta { align-items:center; color:#94a3b8; display:flex; flex-wrap:wrap; font-size:.7rem; gap:.3rem; margin-top:.15rem; }
        .db-row-amount { flex-shrink:0; font-size:.85rem; font-weight:700; font-variant-numeric:tabular-nums; }
        .db-row-amount.is-green { color:#047857; }
        .db-row-amount.is-amber { color:#b45309; }
        .db-empty { color:#94a3b8; font-size:.8rem; padding:1.6rem 1.05rem; text-align:center; }

        .dark .db-card, .dark .db-panel { background:#18181b; border-color:#3f3f46; }
        .dark .db-title, .dark .db-card-value, .dark .db-panel-head h2, .dark .db-project-name,
        .dark .db-note-title, .dark .db-row-title, .dark .db-students strong { color:#fafafa; }
        .dark .db-panel-head, .dark .db-project, .dark .db-note, .dark .db-row { border-color:#27272a; }
        .dark .db-note:hover, .dark .db-row:hover { background:#1f1f23; }
        .dark .db-note.is-unread { background:#0c1f2e; }
        .dark .db-role { background:#27272a; color:#d4d4d8; }
    </style>
</x-layouts::app>
