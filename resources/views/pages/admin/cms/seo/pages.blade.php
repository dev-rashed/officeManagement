<x-layouts::app :title="__('Per-page SEO')">
    <div class="space-y-5">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Per-page SEO') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Override the title, description and share image for any public page. Blank means the site default is used.') }}</p>
            </div>
            <a href="{{ route('admin.seo.settings') }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-200">
                &larr; {{ __('SEO & Analytics') }}
            </a>
        </div>

        @php($grouped = collect($rows)->groupBy('group'))

        @foreach ($grouped as $group => $items)
            <section>
                <h2 class="sp-group">{{ $group }} <span class="sp-count">{{ count($items) }}</span></h2>

                <ul class="mt-2 space-y-2">
                    @foreach ($items as $row)
                        @php($meta = $row['meta'])
                        <li class="sp-row"
                            data-identifier="{{ $row['identifier'] }}"
                            data-page-title="{{ e($row['title']) }}"
                            data-meta-title="{{ e((string) $meta?->meta_title) }}"
                            data-meta-description="{{ e((string) $meta?->meta_description) }}"
                            data-meta-keywords="{{ e((string) $meta?->meta_keywords) }}"
                            data-og-title="{{ e((string) $meta?->og_title) }}"
                            data-og-description="{{ e((string) $meta?->og_description) }}"
                            data-canonical="{{ e((string) $meta?->canonical_url) }}"
                            data-noindex="{{ $meta?->noindex ? '1' : '0' }}"
                            data-nofollow="{{ $meta?->nofollow ? '1' : '0' }}"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="sp-title">{{ $row['title'] }}</span>
                                    @if ($meta?->noindex)
                                        <span class="sp-chip is-noindex">noindex</span>
                                    @endif
                                    @if ($meta && ($meta->meta_title || $meta->meta_description))
                                        <span class="sp-chip is-set">{{ __('customised') }}</span>
                                    @else
                                        <span class="sp-chip is-default">{{ __('using defaults') }}</span>
                                    @endif
                                </div>

                                {{-- A rough preview of the Google result --}}
                                <p class="sp-serp-title">{{ $meta?->meta_title ?: $row['title'] }}</p>
                                @if ($row['url'])
                                    <p class="sp-serp-url">{{ $row['url'] }}</p>
                                @endif
                                <p class="sp-serp-desc">{{ $meta?->meta_description ?: __('No description set — the site default will be used.') }}</p>
                            </div>

                            <div class="flex shrink-0 items-center gap-1.5">
                                @if ($row['url'])
                                    <a href="{{ $row['url'] }}" target="_blank" rel="noopener" class="sp-btn is-neutral">{{ __('View') }}</a>
                                @endif
                                <button type="button" class="sp-btn is-primary seo-edit-btn">{{ __('Edit SEO') }}</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>

    {{-- Edit modal --}}
    <div id="seo-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" aria-hidden="true">
        <div id="seo-modal-overlay" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('SEO settings') }}</h2>
                    <p id="sm-page" class="mt-0.5 text-xs text-slate-500 dark:text-zinc-400"></p>
                </div>
                <button id="sm-close" type="button" class="rounded-full bg-slate-100 p-1.5 text-slate-500 transition hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    <span class="sr-only">{{ __('Close') }}</span>
                </button>
            </div>

            <div class="mt-4 max-h-[65vh] space-y-3 overflow-y-auto pr-1">
                <div>
                    <label for="sm-title" class="sp-label">{{ __('Meta title') }}</label>
                    <input id="sm-title" type="text" class="sp-input" maxlength="255">
                    <p class="sp-hint"><span id="sm-title-count">0</span> {{ __('characters — aim for under 60') }}</p>
                </div>

                <div>
                    <label for="sm-desc" class="sp-label">{{ __('Meta description') }}</label>
                    <textarea id="sm-desc" rows="3" class="sp-input" maxlength="320"></textarea>
                    <p class="sp-hint"><span id="sm-desc-count">0</span> {{ __('characters — aim for under 155') }}</p>
                </div>

                <div>
                    <label for="sm-keywords" class="sp-label">{{ __('Keywords') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                    <input id="sm-keywords" type="text" class="sp-input" placeholder="{{ __('comma, separated') }}">
                    <p class="sp-hint">{{ __('Google ignores these, but some other engines still read them.') }}</p>
                </div>

                <details class="rounded-lg border border-slate-200 p-3 dark:border-zinc-700">
                    <summary class="cursor-pointer text-xs font-semibold text-slate-600 dark:text-zinc-300">{{ __('Social sharing and advanced') }}</summary>

                    <div class="mt-3 space-y-3">
                        <div>
                            <label for="sm-og-title" class="sp-label">{{ __('Share title') }}</label>
                            <input id="sm-og-title" type="text" class="sp-input" placeholder="{{ __('Defaults to the meta title') }}">
                        </div>
                        <div>
                            <label for="sm-og-desc" class="sp-label">{{ __('Share description') }}</label>
                            <textarea id="sm-og-desc" rows="2" class="sp-input" maxlength="320"></textarea>
                        </div>
                        <div>
                            <label for="sm-og-image" class="sp-label">{{ __('Share image') }}</label>
                            <input id="sm-og-image" type="file" accept="image/*" class="sp-input">
                        </div>
                        <div>
                            <label for="sm-canonical" class="sp-label">{{ __('Canonical URL') }}</label>
                            <input id="sm-canonical" type="url" class="sp-input" placeholder="{{ __('Leave blank unless this page duplicates another') }}">
                        </div>
                        <div class="flex items-center gap-4 pt-1">
                            <label class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-zinc-300">
                                <input id="sm-noindex" type="checkbox" class="size-3.5 rounded"> {{ __('Hide from search (noindex)') }}
                            </label>
                            <label class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-zinc-300">
                                <input id="sm-nofollow" type="checkbox" class="size-3.5 rounded"> nofollow
                            </label>
                        </div>
                    </div>
                </details>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-zinc-800">
                <button id="sm-cancel" type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-300">{{ __('Cancel') }}</button>
                <button id="sm-submit" type="button" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-sky-700 disabled:opacity-60">{{ __('Save') }}</button>
            </div>
        </div>
    </div>

    <style>
        .sp-group { color:#64748b; font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
        .sp-count { background:#e2e8f0; border-radius:9999px; color:#475569; font-size:.62rem; margin-left:.3rem; padding:.1rem .4rem; }
        .sp-row { align-items:flex-start; background:#fff; border:1px solid #dbe4f0; border-radius:14px; display:flex; gap:1rem; padding:.85rem 1rem; }
        .sp-row:hover { border-color:#bfd3ea; }
        .sp-title { color:#0f172a; font-size:.82rem; font-weight:600; }
        .sp-chip { border-radius:9999px; font-size:.58rem; font-weight:700; letter-spacing:.04em; padding:.12rem .42rem; text-transform:uppercase; }
        .sp-chip.is-set { background:#dcfce7; color:#15803d; }
        .sp-chip.is-default { background:#f1f5f9; color:#94a3b8; }
        .sp-chip.is-noindex { background:#fee2e2; color:#b91c1c; }
        .sp-serp-title { color:#1a0dab; font-size:.82rem; margin-top:.45rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .sp-serp-url { color:#006621; font-size:.68rem; margin-top:.1rem; }
        .sp-serp-desc { color:#4d5156; font-size:.72rem; line-height:1.4; margin-top:.15rem; }
        .sp-btn { border:1px solid transparent; border-radius:8px; font-size:.68rem; font-weight:600; padding:.32rem .6rem; }
        .sp-btn.is-neutral { background:#fff; border-color:#dbe4f0; color:#334155; }
        .sp-btn.is-primary { background:#0284c7; color:#fff; }
        .sp-label { color:#334155; display:block; font-size:.72rem; font-weight:600; margin-bottom:.3rem; }
        .sp-input { background:#fff; border:1px solid #e2e8f0; border-radius:8px; font-size:.76rem; padding:.45rem .6rem; width:100%; }
        .sp-input:focus { border-color:#0ea5e9; box-shadow:0 0 0 2px rgba(14,165,233,.16); outline:none; }
        .sp-hint { color:#94a3b8; font-size:.66rem; margin-top:.25rem; }
        .dark .sp-row { background:#18181b; border-color:#3f3f46; }
        .dark .sp-title { color:#fafafa; }
        .dark .sp-serp-title { color:#8ab4f8; }
        .dark .sp-serp-url { color:#7cb342; }
        .dark .sp-serp-desc { color:#bdc1c6; }
        .dark .sp-label { color:#d4d4d8; }
        .dark .sp-input { background:#09090b; border-color:#3f3f46; color:#fafafa; }
    </style>

    <script>
        (() => {
        const CSRF = '{{ csrf_token() }}';
        const SAVE_URL = @js(route('admin.seo.pages.update'));
        const $id = (i) => document.getElementById(i);
        const modal = $id('seo-modal');
        let current = null;

        const counter = (input, out) => {
            const sync = () => { $id(out).textContent = input.value.length; };
            input.addEventListener('input', sync);
            return sync;
        };
        const syncTitle = counter($id('sm-title'), 'sm-title-count');
        const syncDesc = counter($id('sm-desc'), 'sm-desc-count');

        const open = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); modal.setAttribute('aria-hidden', 'false'); $id('sm-title').focus(); };
        const close = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); modal.setAttribute('aria-hidden', 'true'); };

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.seo-edit-btn');
            if (!btn) return;

            const row = btn.closest('.sp-row');
            current = row;

            $id('sm-page').textContent = row.dataset.pageTitle;
            $id('sm-title').value = row.dataset.metaTitle;
            $id('sm-desc').value = row.dataset.metaDescription;
            $id('sm-keywords').value = row.dataset.metaKeywords;
            $id('sm-og-title').value = row.dataset.ogTitle;
            $id('sm-og-desc').value = row.dataset.ogDescription;
            $id('sm-canonical').value = row.dataset.canonical;
            $id('sm-noindex').checked = row.dataset.noindex === '1';
            $id('sm-nofollow').checked = row.dataset.nofollow === '1';
            $id('sm-og-image').value = '';

            syncTitle(); syncDesc();
            open();
        });

        $id('sm-submit').addEventListener('click', async () => {
            if (!current) return;

            const fd = new FormData();
            fd.append('identifier', current.dataset.identifier);
            fd.append('meta_title', $id('sm-title').value);
            fd.append('meta_description', $id('sm-desc').value);
            fd.append('meta_keywords', $id('sm-keywords').value);
            fd.append('og_title', $id('sm-og-title').value);
            fd.append('og_description', $id('sm-og-desc').value);
            fd.append('canonical_url', $id('sm-canonical').value);
            fd.append('noindex', $id('sm-noindex').checked ? '1' : '0');
            fd.append('nofollow', $id('sm-nofollow').checked ? '1' : '0');

            const file = $id('sm-og-image').files[0];
            if (file) fd.append('og_image', file);

            const btn = $id('sm-submit');
            btn.disabled = true;
            const original = btn.textContent;
            btn.textContent = @js(__('Saving...'));

            try {
                const res = await fetch(SAVE_URL, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: fd,
                });
                const json = await res.json();

                if (res.status === 422) {
                    const first = Object.values(json.errors ?? {})[0]?.[0] ?? json.message;
                    iziToast.warning({ title: 'Not saved', message: first, position: 'topRight' });
                    return;
                }
                if (!res.ok) throw new Error(json.message ?? 'Server error');

                close();
                iziToast.success({ title: 'Saved', message: json.message, position: 'topRight' });
                window.location.reload();
            } catch (err) {
                iziToast.error({ title: 'Error', message: err.message, position: 'topRight' });
            } finally {
                btn.disabled = false;
                btn.textContent = original;
            }
        });

        ['sm-close', 'sm-cancel'].forEach((i) => $id(i).addEventListener('click', close));
        $id('seo-modal-overlay').addEventListener('click', close);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') close();
        });
        })();
    </script>
</x-layouts::app>
