<x-layouts::app :title="__('Notifications')">
    <div class="space-y-5">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Notifications') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                    @if ($unreadCount)
                        {{ trans_choice('{1} :count unread notification|[2,*] :count unread notifications', $unreadCount, ['count' => $unreadCount]) }}
                    @else
                        {{ __('You are all caught up.') }}
                    @endif
                </p>
            </div>

            @if ($unreadCount)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-200">
                        {{ __('Mark all as read') }}
                    </button>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('success') }}</div>
        @endif

        @if ($notifications->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <p class="font-medium text-slate-700 dark:text-zinc-200">{{ __('No notifications yet') }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ __('New income and expense entries will appear here, based on the notification rules for your role.') }}</p>
            </div>
        @else
            <ul class="space-y-2">
                @foreach ($notifications as $notification)
                    @php($d = $notification->data)
                    <li @class([
                        'nt-row',
                        'is-unread' => $notification->read_at === null,
                    ])>
                        <span @class([
                            'nt-icon',
                            'is-income' => ($d['entry_type'] ?? null) === 'income',
                            'is-expense' => ($d['entry_type'] ?? null) === 'expense',
                        ])>
                            @if (($d['entry_type'] ?? null) === 'income')
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v9.586l3.293-3.293a1 1 0 111.414 1.414l-5 5a1 1 0 01-1.414 0l-5-5a1 1 0 111.414-1.414L9 13.586V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 17a1 1 0 01-1-1V6.414L5.707 9.707a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0l5 5a1 1 0 01-1.414 1.414L11 6.414V16a1 1 0 01-1 1z" clip-rule="evenodd"/></svg>
                            @endif
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="nt-title">{{ $d['title'] ?? __('Notification') }}</span>
                                @if ($notification->read_at === null)
                                    <span class="nt-chip">{{ __('new') }}</span>
                                @endif
                                @if (! empty($d['category']))
                                    <span class="nt-cat">{{ $d['category'] }}</span>
                                @endif
                            </div>

                            <p class="nt-msg">{{ $d['message'] ?? '' }}</p>

                            <p class="nt-meta">
                                {{ $notification->created_at->format('d M Y, H:i') }}
                                <span class="text-slate-300">·</span>
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-1.5">
                            @if (! empty($d['url']))
                                <a href="{{ $d['url'] }}" class="nt-btn is-primary">{{ __('Open') }}</a>
                            @endif
                            <button type="button" class="nt-btn is-neutral nt-dismiss" data-url="{{ route('notifications.destroy', $notification->id) }}">{{ __('Dismiss') }}</button>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div>{{ $notifications->links() }}</div>
        @endif
    </div>

    <style>
        .nt-row { align-items:flex-start; background:#fff; border:1px solid #dbe4f0; border-radius:14px; display:flex; gap:.8rem; padding:.85rem 1rem; }
        .nt-row.is-unread { border-color:#bae6fd; background:#f0f9ff; }
        .nt-icon { align-items:center; border-radius:9999px; display:flex; flex-shrink:0; height:2rem; justify-content:center; width:2rem; }
        .nt-icon.is-income { background:#d1fae5; color:#047857; }
        .nt-icon.is-expense { background:#fef3c7; color:#b45309; }
        .nt-title { color:#0f172a; font-size:.82rem; font-weight:600; }
        .nt-chip { background:#0284c7; border-radius:9999px; color:#fff; font-size:.58rem; font-weight:700; letter-spacing:.05em; padding:.12rem .42rem; text-transform:uppercase; }
        .nt-cat { background:#f1f5f9; border-radius:9999px; color:#475569; font-size:.62rem; padding:.12rem .45rem; }
        .nt-msg { color:#475569; font-size:.76rem; line-height:1.45; margin-top:.2rem; }
        .nt-meta { color:#94a3b8; font-size:.66rem; margin-top:.35rem; }
        .nt-btn { border:1px solid transparent; border-radius:8px; font-size:.68rem; font-weight:600; padding:.32rem .6rem; }
        .nt-btn.is-primary { background:#0284c7; color:#fff; }
        .nt-btn.is-neutral { background:#fff; border-color:#dbe4f0; color:#64748b; }
        .dark .nt-row { background:#18181b; border-color:#3f3f46; }
        .dark .nt-row.is-unread { background:#0c1f2e; border-color:#075985; }
        .dark .nt-title { color:#fafafa; }
        .dark .nt-msg { color:#a1a1aa; }
        .dark .nt-btn.is-neutral { background:transparent; border-color:#3f3f46; color:#a1a1aa; }
    </style>

    <script>
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.nt-dismiss');
            if (!btn) return;

            const row = btn.closest('.nt-row');
            btn.disabled = true;

            try {
                const res = await fetch(btn.dataset.url, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                });
                if (!res.ok) throw new Error('Could not remove it');
                row.remove();
            } catch (err) {
                btn.disabled = false;
                iziToast.error({ title: 'Error', message: err.message, position: 'topRight' });
            }
        });
    </script>
</x-layouts::app>
