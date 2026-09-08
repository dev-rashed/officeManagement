<x-layouts::app :title="__('SEO & Analytics')">
    <div class="space-y-5">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('SEO & Analytics') }}</h1>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Site-wide defaults, Google tracking, and search engine verification.') }}</p>
            </div>
            <a href="{{ route('admin.seo.pages') }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-transparent dark:text-zinc-200">
                {{ __('Per-page SEO') }} &rarr;
            </a>
        </div>

        @if (! $settings->is_indexable)
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                <p class="font-semibold">{{ __('This site is hidden from search engines') }}</p>
                <p class="mt-1 text-rose-800">{{ __('Every page sends noindex, robots.txt disallows everything, and analytics do not load. Turn "Allow search engines" back on when the site goes live.') }}</p>
            </div>
        @endif

        @if ($settings->google_analytics_id && $settings->google_tag_manager_id)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-semibold">{{ __('Both GA4 and Tag Manager are set') }}</p>
                <p class="mt-1 text-amber-800">{{ __('If GA4 is also configured inside your GTM container, every pageview is counted twice. Usually you want one or the other here.') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.seo.settings.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Google tools --}}
            <section class="seo-card">
                <header class="seo-card-head">
                    <h2>{{ __('Google tools') }}</h2>
                    <p>{{ __('Paste the IDs from Analytics, Tag Manager and Search Console.') }}</p>
                </header>

                <div class="seo-grid">
                    <div>
                        <label for="ga" class="seo-label">{{ __('GA4 Measurement ID') }}</label>
                        <input id="ga" name="google_analytics_id" type="text" value="{{ old('google_analytics_id', $settings->google_analytics_id) }}" class="seo-input font-mono" placeholder="G-XXXXXXXXXX">
                        <p class="seo-hint">{{ __('Analytics → Admin → Data streams → your stream.') }}</p>
                        @error('google_analytics_id') <p class="seo-err">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="gtm" class="seo-label">{{ __('Tag Manager Container ID') }}</label>
                        <input id="gtm" name="google_tag_manager_id" type="text" value="{{ old('google_tag_manager_id', $settings->google_tag_manager_id) }}" class="seo-input font-mono" placeholder="GTM-XXXXXXX">
                        <p class="seo-hint">{{ __('Loads the container in the head and the noscript frame after body.') }}</p>
                        @error('google_tag_manager_id') <p class="seo-err">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="gsv" class="seo-label">{{ __('Search Console verification') }}</label>
                        <input id="gsv" name="google_site_verification" type="text" value="{{ old('google_site_verification', $settings->google_site_verification) }}" class="seo-input font-mono" placeholder="{{ __('the content value of the meta tag') }}">
                        <p class="seo-hint">{{ __('Choose the HTML tag method and paste only the content value.') }}</p>
                        @error('google_site_verification') <p class="seo-err">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="bsv" class="seo-label">{{ __('Bing Webmaster verification') }}</label>
                        <input id="bsv" name="bing_site_verification" type="text" value="{{ old('bing_site_verification', $settings->bing_site_verification) }}" class="seo-input font-mono">
                    </div>
                </div>
            </section>

            {{-- Defaults --}}
            <section class="seo-card">
                <header class="seo-card-head">
                    <h2>{{ __('Search result defaults') }}</h2>
                    <p>{{ __('Used on any page that has no SEO of its own.') }}</p>
                </header>

                <div class="seo-grid">
                    <div>
                        <label for="site_name" class="seo-label">{{ __('Site name') }}</label>
                        <input id="site_name" name="site_name" type="text" value="{{ old('site_name', $settings->site_name) }}" class="seo-input">
                    </div>

                    <div>
                        <label for="tpl" class="seo-label">{{ __('Title format') }}</label>
                        <input id="tpl" name="title_template" type="text" value="{{ old('title_template', $settings->title_template) }}" class="seo-input font-mono" required>
                        <p class="seo-hint">{{ __('Use {title} and {site}. A page title that already ends in the site name is left alone.') }}</p>
                        @error('title_template') <p class="seo-err">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="desc" class="seo-label">{{ __('Default meta description') }}</label>
                        <textarea id="desc" name="default_meta_description" rows="2" class="seo-input" maxlength="320" data-counter="desc-count">{{ old('default_meta_description', $settings->default_meta_description) }}</textarea>
                        <p class="seo-hint"><span id="desc-count">0</span>/320 — {{ __('Google usually shows about 155 characters.') }}</p>
                        @error('default_meta_description') <p class="seo-err">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="canon" class="seo-label">{{ __('Canonical base URL') }}</label>
                        <input id="canon" name="canonical_base_url" type="url" value="{{ old('canonical_base_url', $settings->canonical_base_url) }}" class="seo-input" placeholder="https://hrt-bd.com">
                        <p class="seo-hint">{{ __('Leave blank to use the current domain.') }}</p>
                        @error('canonical_base_url') <p class="seo-err">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tw" class="seo-label">{{ __('Twitter / X handle') }}</label>
                        <input id="tw" name="twitter_handle" type="text" value="{{ old('twitter_handle', $settings->twitter_handle) }}" class="seo-input" placeholder="@hashtag">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="og" class="seo-label">{{ __('Default share image') }}</label>
                        @if ($settings->default_og_image)
                            <img src="{{ Storage::url($settings->default_og_image) }}" alt="" class="mb-2 h-24 rounded-lg border border-slate-200 object-cover">
                        @endif
                        <input id="og" name="default_og_image" type="file" accept="image/*" class="seo-input">
                        <p class="seo-hint">{{ __('Shown when a page is shared on Facebook, LinkedIn or WhatsApp. 1200×630 works best.') }}</p>
                        @error('default_og_image') <p class="seo-err">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Organisation schema --}}
            <section class="seo-card">
                <header class="seo-card-head">
                    <h2>{{ __('Organisation details') }}</h2>
                    <p>{{ __('Emitted as structured data so Google can show a knowledge panel.') }}</p>
                </header>

                <div class="seo-grid">
                    <div>
                        <label for="orgtype" class="seo-label">{{ __('Organisation type') }}</label>
                        <select id="orgtype" name="organization_type" class="seo-input">
                            @foreach ($organizationTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('organization_type', $settings->organization_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="logo" class="seo-label">{{ __('Logo') }}</label>
                        @if ($settings->organization_logo)
                            <img src="{{ Storage::url($settings->organization_logo) }}" alt="" class="mb-2 h-12 rounded border border-slate-200 object-contain">
                        @endif
                        <input id="logo" name="organization_logo" type="file" accept="image/*" class="seo-input">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="seo-label">{{ __('Social profile URLs') }}</label>
                        <div id="social-profiles" class="space-y-2">
                            @php($profiles = old('social_profiles', $settings->social_profiles ?: ['']))
                            @foreach ($profiles as $profile)
                                <div class="flex gap-2">
                                    <input name="social_profiles[]" type="url" value="{{ $profile }}" class="seo-input" placeholder="https://facebook.com/yourpage">
                                    <button type="button" class="shrink-0 px-2 text-slate-400 hover:text-rose-500" onclick="this.parentElement.remove()">&times;</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-profile" class="mt-2 text-xs font-medium text-sky-600 hover:text-sky-700">+ {{ __('Add profile') }}</button>
                        @error('social_profiles.*') <p class="seo-err">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Crawling --}}
            <section class="seo-card">
                <header class="seo-card-head">
                    <h2>{{ __('Crawling') }}</h2>
                    <p>{{ __('robots.txt is served from here, and the sitemap link is appended automatically.') }}</p>
                </header>

                <div class="space-y-4">
                    <label class="flex items-start gap-3">
                        <input type="hidden" name="is_indexable" value="0">
                        <input type="checkbox" name="is_indexable" value="1" @checked(old('is_indexable', $settings->is_indexable)) class="mt-0.5 size-4 rounded border-slate-300 text-sky-600">
                        <span>
                            <span class="block text-sm font-medium text-slate-800 dark:text-zinc-100">{{ __('Allow search engines to index this site') }}</span>
                            <span class="block text-xs text-slate-500">{{ __('Turn off for staging. Overrides every per-page setting and stops analytics loading.') }}</span>
                        </span>
                    </label>

                    <div>
                        <label for="robots" class="seo-label">{{ __('robots.txt') }}</label>
                        <textarea id="robots" name="robots_txt" rows="6" class="seo-input font-mono text-xs">{{ old('robots_txt', $settings->robots_txt) }}</textarea>
                        <p class="seo-hint">
                            {{ __('Live at') }} <a href="{{ url('robots.txt') }}" target="_blank" class="text-sky-600 hover:underline">/robots.txt</a>
                            · {{ __('sitemap at') }} <a href="{{ url('sitemap.xml') }}" target="_blank" class="text-sky-600 hover:underline">/sitemap.xml</a>
                        </p>
                    </div>
                </div>
            </section>

            {{-- Escape hatch --}}
            <section class="seo-card">
                <header class="seo-card-head">
                    <h2>{{ __('Custom code') }}</h2>
                    <p class="text-rose-600">{{ __('Injected raw into every public page. Only paste code you trust.') }}</p>
                </header>

                <div class="space-y-4">
                    <div>
                        <label for="head" class="seo-label">{{ __('Before </head>') }}</label>
                        <textarea id="head" name="custom_head_snippet" rows="3" class="seo-input font-mono text-xs" placeholder="{{ __('e.g. a Meta Pixel or Hotjar tag') }}">{{ old('custom_head_snippet', $settings->custom_head_snippet) }}</textarea>
                    </div>
                    <div>
                        <label for="bodysnip" class="seo-label">{{ __('Before </body>') }}</label>
                        <textarea id="bodysnip" name="custom_body_snippet" rows="3" class="seo-input font-mono text-xs">{{ old('custom_body_snippet', $settings->custom_body_snippet) }}</textarea>
                    </div>
                </div>
            </section>

            <div class="flex justify-end gap-2">
                <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-sky-700">{{ __('Save settings') }}</button>
            </div>
        </form>
    </div>

    <style>
        .seo-card { background:#fff; border:1px solid #dbe4f0; border-radius:18px; padding:1.1rem 1.2rem; }
        .seo-card-head { border-bottom:1px solid #eef2f7; margin:-0.2rem 0 1rem; padding-bottom:.7rem; }
        .seo-card-head h2 { color:#0f172a; font-size:.9rem; font-weight:700; }
        .seo-card-head p { color:#64748b; font-size:.75rem; margin-top:.15rem; }
        .seo-grid { display:grid; gap:1rem; }
        @media (min-width:640px) { .seo-grid { grid-template-columns:1fr 1fr; } .sm\:col-span-2 { grid-column:span 2; } }
        .seo-label { color:#334155; display:block; font-size:.75rem; font-weight:600; margin-bottom:.3rem; }
        .seo-input { background:#fff; border:1px solid #e2e8f0; border-radius:9px; font-size:.8rem; padding:.5rem .65rem; width:100%; }
        .seo-input:focus { border-color:#0ea5e9; box-shadow:0 0 0 2px rgba(14,165,233,.16); outline:none; }
        .seo-hint { color:#94a3b8; font-size:.68rem; margin-top:.3rem; }
        .seo-err { color:#e11d48; font-size:.7rem; margin-top:.3rem; }
        .dark .seo-card { background:#18181b; border-color:#3f3f46; }
        .dark .seo-card-head { border-color:#27272a; }
        .dark .seo-card-head h2 { color:#fafafa; }
        .dark .seo-label { color:#d4d4d8; }
        .dark .seo-input { background:#09090b; border-color:#3f3f46; color:#fafafa; }
    </style>

    <script>
        (() => {
            document.getElementById('add-profile')?.addEventListener('click', () => {
                const wrap = document.getElementById('social-profiles');
                const row = document.createElement('div');
                row.className = 'flex gap-2';
                row.innerHTML = '<input name="social_profiles[]" type="url" class="seo-input" placeholder="https://linkedin.com/company/...">'
                    + '<button type="button" class="shrink-0 px-2 text-slate-400 hover:text-rose-500">&times;</button>';
                row.querySelector('button').addEventListener('click', () => row.remove());
                wrap.appendChild(row);
            });

            document.querySelectorAll('[data-counter]').forEach((el) => {
                const out = document.getElementById(el.dataset.counter);
                const sync = () => { out.textContent = el.value.length; };
                el.addEventListener('input', sync);
                sync();
            });
        })();
    </script>
</x-layouts::app>
