<x-layouts::auth.branded :title="__('Log in')">
    <div class="lg-head">
        <h2 class="lg-title">{{ __('Sign in') }}</h2>
        <p class="lg-sub">{{ __('Use your work email and password to continue.') }}</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    @if ($errors->any() && ! $errors->has('captcha'))
        <div class="lg-alert" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 shrink-0">
                <path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-8-4a1 1 0 011 1v3a1 1 0 11-2 0V7a1 1 0 011-1zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="lg-form">
        @csrf

        <div class="lg-field">
            <label for="email" class="lg-label">{{ __('Email address') }}</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="you@hrt-bd.com"
                class="lg-input @error('email') is-invalid @enderror"
            >
            @error('email') <p class="lg-error">{{ $message }}</p> @enderror
        </div>

        <div class="lg-field">
            <div class="lg-label-row">
                <label for="password" class="lg-label">{{ __('Password') }}</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="lg-link">{{ __('Forgot password?') }}</a>
                @endif
            </div>

            <div class="lg-password">
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="{{ __('Enter your password') }}"
                    class="lg-input @error('password') is-invalid @enderror"
                >
                <button type="button" class="lg-reveal" data-toggle-password aria-label="{{ __('Show password') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" data-eye-open>
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" data-eye-closed hidden>
                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                    </svg>
                </button>
            </div>
            @error('password') <p class="lg-error">{{ $message }}</p> @enderror
        </div>

        <x-captcha />

        <label class="lg-remember">
            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="lg-check">
            <span>{{ __('Keep me signed in on this device') }}</span>
        </label>

        <button type="submit" class="lg-submit" data-test="login-button">
            <span data-submit-label>{{ __('Sign in') }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" data-submit-arrow>
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </form>

    <style>
        .lg-head { text-align:left; }
        .lg-title { color:#18181b; font-size:1.5rem; font-weight:700; letter-spacing:-.02em; }
        .lg-sub { color:#71717a; font-size:.88rem; margin-top:.3rem; }

        .lg-alert { align-items:flex-start; background:#fef2f2; border:1px solid #fecaca; border-radius:11px; color:#991b1b; display:flex; font-size:.82rem; gap:.5rem; line-height:1.4; padding:.7rem .85rem; }

        .lg-form { display:flex; flex-direction:column; gap:1.05rem; }
        .lg-field { display:flex; flex-direction:column; gap:.4rem; }
        .lg-label-row { align-items:baseline; display:flex; justify-content:space-between; }
        .lg-label { color:#3f3f46; font-size:.8rem; font-weight:500; }
        .lg-link { color:#1E3A5F; font-size:.76rem; font-weight:500; }
        .lg-link:hover { text-decoration:underline; }

        .lg-input { background:#fff; border:1px solid #e4e4e7; border-radius:10px; color:#18181b; font-size:.9rem; padding:.65rem .85rem; transition:border-color .15s ease, box-shadow .15s ease; width:100%; }
        .lg-input::placeholder { color:#a1a1aa; }
        .lg-input:focus { border-color:#18181b; box-shadow:0 0 0 3px rgba(24,24,27,.08); outline:none; }
        .lg-input.is-invalid { border-color:#e11d48; }
        .lg-error { color:#e11d48; font-size:.78rem; }

        .lg-password { position:relative; }
        .lg-password .lg-input { padding-right:2.6rem; }
        .lg-reveal { align-items:center; color:#a1a1aa; display:flex; height:100%; justify-content:center; position:absolute; right:0; top:0; width:2.6rem; }
        .lg-reveal:hover { color:#52525b; }

        .lg-remember { align-items:center; color:#52525b; cursor:pointer; display:flex; font-size:.82rem; gap:.5rem; }
        .lg-check { accent-color:#1E3A5F; height:.95rem; width:.95rem; }

        .lg-submit { align-items:center; background:#1E3A5F; border-radius:10px; color:#fff; display:flex; font-size:.9rem; font-weight:600; gap:.45rem; justify-content:center; padding:.72rem 1rem; transition:background .15s ease, opacity .15s ease; width:100%; }
        .lg-submit:hover { background:#16304f; }
        .lg-submit:focus-visible { box-shadow:0 0 0 3px rgba(30,58,95,.28); outline:none; }
        .lg-submit[disabled] { cursor:not-allowed; opacity:.65; }

        .dark .lg-title { color:#fafafa; }
        .dark .lg-sub { color:#a1a1aa; }
        .dark .lg-label { color:#d4d4d8; }
        .dark .lg-link { color:#93c5fd; }
        .dark .lg-input { background:#18181b; border-color:#3f3f46; color:#fafafa; }
        .dark .lg-input:focus { border-color:#e4e4e7; box-shadow:0 0 0 3px rgba(228,228,231,.12); }
        .dark .lg-remember { color:#a1a1aa; }
        .dark .lg-alert { background:#341915; border-color:#7f1d1d; color:#fca5a5; }

        @media (prefers-reduced-motion: reduce) { .lg-input, .lg-submit { transition:none; } }
    </style>

    <script>
        (() => {
            const init = () => {
                const toggle = document.querySelector('[data-toggle-password]');
                if (toggle && toggle.dataset.bound !== '1') {
                    toggle.dataset.bound = '1';
                    toggle.addEventListener('click', () => {
                        const input = document.getElementById('password');
                        const open = toggle.querySelector('[data-eye-open]');
                        const closed = toggle.querySelector('[data-eye-closed]');
                        const revealed = input.type === 'text';

                        input.type = revealed ? 'password' : 'text';
                        open.hidden = ! revealed;
                        closed.hidden = revealed;
                        toggle.setAttribute('aria-label', revealed ? @js(__('Show password')) : @js(__('Hide password')));
                        input.focus();
                    });
                }

                // Stop a double submit from a second click or an impatient Enter.
                const form = document.querySelector('.lg-form');
                if (form && form.dataset.bound !== '1') {
                    form.dataset.bound = '1';
                    form.addEventListener('submit', () => {
                        const btn = form.querySelector('.lg-submit');
                        const label = btn.querySelector('[data-submit-label]');
                        const arrow = btn.querySelector('[data-submit-arrow]');
                        btn.disabled = true;
                        label.textContent = @js(__('Signing in...'));
                        if (arrow) arrow.hidden = true;
                    });
                }
            };

            document.addEventListener('DOMContentLoaded', init);
            document.addEventListener('livewire:navigated', init);
            init();
        })();
    </script>
</x-layouts::auth.branded>
