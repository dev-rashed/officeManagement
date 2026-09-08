{{--
    Captcha field with a refresh button.

    Renders nothing when captcha is switched off, so a test suite or a local
    environment with CAPTCHA_ENABLED=false needs no view changes.
--}}
@if (config('captcha.enabled', true))
    <div class="cap-field">
        <label for="captcha" class="cap-label">{{ __('Security check') }}</label>

        <div class="cap-row">
            {{-- mews/captcha registers its routes without names, so these are
                 built from the path rather than route(). --}}
            <img
                src="{{ url('captcha/flat') }}?_={{ time() }}"
                alt="{{ __('Characters to type into the box below') }}"
                class="cap-image"
                data-captcha-image
                data-src="{{ url('captcha/api/flat') }}"
                data-img-src="{{ url('captcha/flat') }}"
            >
            <button type="button" class="cap-refresh" data-captcha-refresh title="{{ __('Show different characters') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                </svg>
                <span class="sr-only">{{ __('Refresh') }}</span>
            </button>
        </div>

        <input
            id="captcha"
            name="captcha"
            type="text"
            required
            autocomplete="off"
            autocapitalize="off"
            spellcheck="false"
            inputmode="text"
            placeholder="{{ __('Type the characters above') }}"
            class="cap-input @error('captcha') is-invalid @enderror"
        >

        @error('captcha')
            <p class="cap-error">{{ $message }}</p>
        @enderror
    </div>

    <style>
        .cap-field { display:flex; flex-direction:column; gap:.5rem; }
        .cap-label { color:var(--cap-label, #3f3f46); font-size:.8rem; font-weight:500; }
        .cap-row { align-items:stretch; display:flex; gap:.5rem; }
        .cap-image { background:#fff; border:1px solid #e4e4e7; border-radius:10px; display:block; flex:1; height:52px; min-width:0; object-fit:cover; width:100%; }
        .cap-refresh { align-items:center; background:#fafafa; border:1px solid #e4e4e7; border-radius:10px; color:#71717a; display:flex; flex-shrink:0; justify-content:center; transition:background .15s ease, color .15s ease; width:2.9rem; }
        .cap-refresh:hover { background:#f4f4f5; color:#18181b; }
        .cap-refresh:active svg { transform:rotate(-180deg); transition:transform .3s ease; }
        .cap-input { background:#fff; border:1px solid #e4e4e7; border-radius:10px; font-size:.9rem; letter-spacing:.12em; padding:.6rem .8rem; width:100%; }
        .cap-input:focus { border-color:#18181b; box-shadow:0 0 0 3px rgba(24,24,27,.08); outline:none; }
        .cap-input.is-invalid { border-color:#e11d48; }
        .cap-error { color:#e11d48; font-size:.78rem; }
        .dark .cap-label { color:#d4d4d8; }
        .dark .cap-image, .dark .cap-refresh, .dark .cap-input { background:#18181b; border-color:#3f3f46; }
        .dark .cap-refresh { color:#a1a1aa; }
        .dark .cap-input { color:#fafafa; }
        .dark .cap-input:focus { border-color:#e4e4e7; box-shadow:0 0 0 3px rgba(228,228,231,.12); }
        @media (prefers-reduced-motion: reduce) { .cap-refresh:active svg { transition:none; } }
    </style>

    <script>
        (() => {
            const init = () => {
                document.querySelectorAll('[data-captcha-refresh]').forEach((btn) => {
                    if (btn.dataset.bound === '1') return;
                    btn.dataset.bound = '1';

                    btn.addEventListener('click', async () => {
                        const img = btn.parentElement.querySelector('[data-captcha-image]');
                        if (!img) return;

                        try {
                            const res = await fetch(img.dataset.src, { headers: { 'Accept': 'application/json' } });
                            const json = await res.json();
                            img.src = json.img ?? img.dataset.imgSrc + '?_=' + Date.now();
                        } catch {
                            // Fall back to a cache-busted reload of the image route.
                            img.src = img.dataset.imgSrc + '?_=' + Date.now();
                        }

                        const input = btn.closest('.cap-field')?.querySelector('.cap-input');
                        if (input) { input.value = ''; input.focus(); }
                    });
                });
            };

            document.addEventListener('DOMContentLoaded', init);
            document.addEventListener('livewire:navigated', init);
            init();
        })();
    </script>
@endif
