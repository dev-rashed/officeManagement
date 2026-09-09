{{--
    The notification bell. Polls the lightweight /notifications/recent endpoint
    rather than holding a socket open, which keeps this working on ordinary
    shared hosting with no broadcasting setup.
--}}
<div class="nb-wrap" data-notification-bell>
    <button type="button" class="nb-trigger" data-nb-toggle aria-haspopup="true" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-2.83-2h5.66A3 3 0 0110 18z"/>
        </svg>
        <span class="nb-badge" data-nb-count hidden>0</span>
        <span class="sr-only">{{ __('Notifications') }}</span>
    </button>

    <div class="nb-panel" data-nb-panel hidden>
        <header class="nb-head">
            <span>{{ __('Notifications') }}</span>
            <button type="button" class="nb-mark-all" data-nb-mark-all>{{ __('Mark all read') }}</button>
        </header>

        <ul class="nb-list" data-nb-list>
            <li class="nb-empty">{{ __('Loading...') }}</li>
        </ul>

        <footer class="nb-foot">
            <a href="{{ route('notifications.index') }}">{{ __('See all notifications') }}</a>
        </footer>
    </div>
</div>

<style>
    .nb-wrap { position: relative; }
    .nb-trigger { align-items:center; background:transparent; border-radius:9px; color:#52525b; display:flex; justify-content:center; padding:.4rem; position:relative; }
    .nb-trigger:hover { background:#f4f4f5; color:#18181b; }
    .nb-badge { background:#e11d48; border-radius:9999px; color:#fff; font-size:.6rem; font-weight:700; line-height:1; min-width:1rem; padding:.16rem .25rem; position:absolute; right:.05rem; top:.05rem; text-align:center; }
    /*
     * Fixed, not absolute. The bell lives inside the sidebar, which scrolls --
     * an absolutely positioned panel is clipped by that overflow. Fixed takes
     * it out of the sidebar entirely and the coordinates are set in JS from the
     * bell's position.
     */
    .nb-panel { background:#fff; border:1px solid #e4e4e7; border-radius:14px; box-shadow:0 12px 34px rgba(24,24,27,.16); display:flex; flex-direction:column; max-height:min(28rem, calc(100vh - 2rem)); overflow:hidden; position:fixed; width:20rem; z-index:200; }
    .nb-head { align-items:center; border-bottom:1px solid #f4f4f5; color:#18181b; display:flex; font-size:.78rem; font-weight:600; justify-content:space-between; padding:.65rem .8rem; }
    .nb-mark-all { color:#0284c7; font-size:.68rem; font-weight:600; }
    .nb-mark-all:hover { text-decoration:underline; }
    .nb-list { flex:1 1 auto; list-style:none; margin:0; min-height:0; overflow-y:auto; padding:0; }
    .nb-head, .nb-foot { flex-shrink:0; }
    .nb-item { border-bottom:1px solid #fafafa; display:block; padding:.65rem .8rem; }
    .nb-item:hover { background:#fafafa; }
    .nb-item.is-unread { background:#f0f9ff; }
    .nb-item.is-unread:hover { background:#e0f2fe; }
    .nb-item-top { align-items:center; display:flex; gap:.4rem; justify-content:space-between; }
    .nb-item-title { color:#18181b; font-size:.74rem; font-weight:600; }
    .nb-item-ago { color:#a1a1aa; flex-shrink:0; font-size:.62rem; }
    .nb-item-msg { color:#52525b; font-size:.7rem; line-height:1.35; margin-top:.15rem; }
    .nb-dot { border-radius:9999px; display:inline-block; height:.4rem; margin-right:.3rem; width:.4rem; }
    .nb-dot.is-income { background:#059669; }
    .nb-dot.is-expense { background:#d97706; }
    .nb-empty { color:#a1a1aa; font-size:.72rem; padding:1.4rem .8rem; text-align:center; }
    .nb-foot { border-top:1px solid #f4f4f5; padding:.55rem .8rem; text-align:center; }
    .nb-foot a { color:#0284c7; font-size:.7rem; font-weight:600; }
    .nb-foot a:hover { text-decoration:underline; }
    .dark .nb-trigger { color:#a1a1aa; }
    .dark .nb-trigger:hover { background:#27272a; color:#fafafa; }
    .dark .nb-panel { background:#18181b; border-color:#3f3f46; }
    .dark .nb-head { border-color:#27272a; color:#fafafa; }
    .dark .nb-item { border-color:#27272a; }
    .dark .nb-item:hover { background:#27272a; }
    .dark .nb-item.is-unread { background:#0c1f2e; }
    .dark .nb-item-title { color:#fafafa; }
    .dark .nb-item-msg { color:#a1a1aa; }
    .dark .nb-foot { border-color:#27272a; }
</style>

<script>
    (() => {
        const init = () => {
            const wrap = document.querySelector('[data-notification-bell]');
            if (!wrap || wrap.dataset.bound === '1') return;
            wrap.dataset.bound = '1';

            const RECENT_URL = @js(route('notifications.recent'));
            const READ_ALL_URL = @js(route('notifications.read-all'));
            const READ_URL = @js(url('notifications'));
            const CSRF = '{{ csrf_token() }}';

            const toggle = wrap.querySelector('[data-nb-toggle]');
            const panel = wrap.querySelector('[data-nb-panel]');
            const list = wrap.querySelector('[data-nb-list]');
            const count = wrap.querySelector('[data-nb-count]');

            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => (
                { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
            ));

            const render = (data) => {
                count.textContent = data.unread > 99 ? '99+' : data.unread;
                count.hidden = data.unread === 0;

                if (!data.items.length) {
                    list.innerHTML = '<li class="nb-empty">' + @js(__('Nothing yet.')) + '</li>';
                    return;
                }

                list.innerHTML = data.items.map((n) => `
                    <li>
                        <a class="nb-item ${n.read ? '' : 'is-unread'}" href="${esc(n.url ?? '#')}" data-nb-item="${esc(n.id)}">
                            <span class="nb-item-top">
                                <span class="nb-item-title">
                                    <span class="nb-dot is-${esc(n.type ?? 'income')}"></span>${esc(n.title)}
                                </span>
                                <span class="nb-item-ago">${esc(n.ago)}</span>
                            </span>
                            <span class="nb-item-msg">${esc(n.message)}</span>
                        </a>
                    </li>`).join('');
            };

            const load = async () => {
                try {
                    const res = await fetch(RECENT_URL, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    render(await res.json());

                    // The list changes the panel's height, so re-anchor it.
                    if (!panel.hidden) position();
                } catch {
                    // A failed poll must never disturb the page.
                }
            };

            const GAP = 8;

            /**
             * Anchor the panel to the bell in viewport coordinates.
             *
             * Opens to the right of the bell where there is room -- the sidebar
             * is on the left, so a right-aligned panel would hang off the edge
             * of the screen -- and flips to the left when there is not.
             */
            const position = () => {
                const r = toggle.getBoundingClientRect();
                const w = panel.offsetWidth || 320;
                const h = panel.offsetHeight || 320;

                let left = r.right + GAP;

                if (left + w > window.innerWidth - GAP) {
                    left = r.left - w - GAP;          // flip to the other side
                }
                if (left < GAP) {
                    left = Math.max(GAP, (window.innerWidth - w) / 2); // centre as a last resort
                }

                // Prefer aligning the panel's bottom with the bell, since the
                // bell sits low in the sidebar; clamp so it stays on screen.
                let top = r.bottom - h;
                top = Math.min(top, window.innerHeight - h - GAP);
                top = Math.max(GAP, top);

                panel.style.left = Math.round(left) + 'px';
                panel.style.top = Math.round(top) + 'px';
            };

            const openPanel = () => {
                panel.hidden = false;
                position();
                toggle.setAttribute('aria-expanded', 'true');
                load();
            };

            const closePanel = () => {
                panel.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
            };

            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                panel.hidden ? openPanel() : closePanel();
            });

            document.addEventListener('click', (e) => {
                if (!panel.hidden && !wrap.contains(e.target) && !panel.contains(e.target)) {
                    closePanel();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !panel.hidden) closePanel();
            });

            // A fixed panel does not travel with the page, so keep it pinned.
            window.addEventListener('resize', () => { if (!panel.hidden) position(); });
            window.addEventListener('scroll', () => { if (!panel.hidden) position(); }, true);

            // Mark one read as it is opened, so the badge is honest.
            list.addEventListener('click', (e) => {
                const item = e.target.closest('[data-nb-item]');
                if (!item || !item.classList.contains('is-unread')) return;

                navigator.sendBeacon?.(
                    `${READ_URL}/${item.dataset.nbItem}/read`,
                    new Blob([new URLSearchParams({ _token: CSRF })], { type: 'application/x-www-form-urlencoded' }),
                );
            });

            wrap.querySelector('[data-nb-mark-all]').addEventListener('click', async () => {
                await fetch(READ_ALL_URL, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                });
                load();
            });

            load();
            setInterval(load, 60000);
        };

        document.addEventListener('DOMContentLoaded', init);
        document.addEventListener('livewire:navigated', init);
        init();
    })();
</script>
