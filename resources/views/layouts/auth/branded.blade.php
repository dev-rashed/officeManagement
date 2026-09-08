<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="au-body">
        <div class="au-shell">

            {{-- Brand panel. Decorative, so it is hidden from screen readers
                 and dropped entirely on small screens where it would just push
                 the form below the fold. --}}
            <aside class="au-brand" aria-hidden="true">
                <div class="au-brand-inner">
                    <div class="au-mark">
                        <x-app-logo-icon class="size-7 fill-current text-white" />
                    </div>

                    <div>
                        <h1 class="au-brand-title">{{ config('app.name', 'Office Management') }}</h1>
                        <p class="au-brand-sub">{{ __('Finance, projects, training and website — in one place.') }}</p>
                    </div>

                    <ul class="au-points">
                        <li>
                            <span class="au-point-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                            {{ __('Three-stage approval for every income and expense') }}
                        </li>
                        <li>
                            <span class="au-point-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                            {{ __('Student registration with per-project forms') }}
                        </li>
                        <li>
                            <span class="au-point-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                            {{ __('Website content, SEO and traffic analytics') }}
                        </li>
                    </ul>

                    <p class="au-brand-foot">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
                </div>
            </aside>

            {{-- Form panel --}}
            <main class="au-main">
                <div class="au-card">
                    <a href="{{ route('home') }}" class="au-mobile-mark">
                        <x-app-logo-icon class="size-8 fill-current" />
                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>

                    {{ $slot }}

                    <p class="au-secure">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('Protected by two-factor authentication') }}
                    </p>
                </div>
            </main>
        </div>

        <x-global-popup />
        @fluxScripts

        <style>
            .au-body { background:#fafafa; margin:0; min-height:100svh; }
            .au-shell { display:grid; min-height:100svh; }

            /* Brand panel */
            .au-brand { display:none; }
            .au-brand-inner { display:flex; flex-direction:column; gap:2.2rem; height:100%; justify-content:center; max-width:26rem; padding:3.5rem; }
            .au-mark { align-items:center; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18); border-radius:14px; display:flex; height:3rem; justify-content:center; width:3rem; }
            .au-brand-title { color:#fff; font-size:1.55rem; font-weight:700; letter-spacing:-.02em; line-height:1.15; }
            .au-brand-sub { color:rgba(255,255,255,.72); font-size:.92rem; line-height:1.5; margin-top:.5rem; }
            .au-points { display:flex; flex-direction:column; gap:.9rem; list-style:none; margin:0; padding:0; }
            .au-points li { align-items:flex-start; color:rgba(255,255,255,.86); display:flex; font-size:.85rem; gap:.65rem; line-height:1.45; }
            .au-point-icon { align-items:center; background:rgba(255,255,255,.16); border-radius:9999px; color:#fff; display:flex; flex-shrink:0; height:1.25rem; justify-content:center; margin-top:.06rem; width:1.25rem; }
            .au-brand-foot { color:rgba(255,255,255,.45); font-size:.72rem; margin-top:auto; }

            /* Form panel */
            .au-main { align-items:center; display:flex; justify-content:center; padding:1.5rem; }
            .au-card { display:flex; flex-direction:column; gap:1.6rem; max-width:24rem; width:100%; }
            .au-mobile-mark { align-items:center; color:#18181b; display:flex; justify-content:center; margin-bottom:.2rem; }
            .au-secure { align-items:center; color:#a1a1aa; display:flex; font-size:.72rem; gap:.35rem; justify-content:center; }

            @media (min-width: 1024px) {
                .au-shell { grid-template-columns:minmax(0, 1fr) minmax(0, 1.05fr); }
                .au-brand { background:linear-gradient(150deg, #12233b 0%, #1E3A5F 52%, #17466b 100%); display:flex; justify-content:center; position:relative; overflow:hidden; }
                .au-brand::after { background:radial-gradient(circle at 78% 12%, rgba(255,255,255,.10), transparent 46%); content:''; inset:0; position:absolute; }
                .au-brand-inner { position:relative; z-index:1; }
                .au-mobile-mark { display:none; }
                .au-main { padding:3rem; }
            }

            .dark .au-body, body.au-body:where(.dark *) { background:#09090b; }
            .dark .au-mobile-mark { color:#fafafa; }
            .dark .au-secure { color:#52525b; }
        </style>
    </body>
</html>
