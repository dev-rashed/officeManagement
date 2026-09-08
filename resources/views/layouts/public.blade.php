<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1E3A5F">

    @php
        // The page's own @section values are the fallback, so titles written in
        // Blade keep working until someone overrides them from the CMS.
        //
        // yieldContent hands back rendered HTML, so entities are already
        // encoded. They have to be decoded here or {{ }} escapes them a second
        // time -- and an "&amp;" title would never match the raw site name,
        // which would append the site name to a title that already ends in it.
        $seoFallback = fn (string $section): ?string => trim(html_entity_decode(
            $__env->yieldContent($section),
            ENT_QUOTES | ENT_HTML5,
        )) ?: null;

        $seo = app(\App\Services\SeoService::class)->resolve(
            \App\Services\SeoService::pageKeyForRoute(request()->route()?->getName()),
            $seoModel ?? null,
            [
                'title' => $seoFallback('title'),
                'description' => $seoFallback('description'),
            ],
        );
    @endphp

    <meta name="author" content="{{ $seo['settings']->site_name }}">

    @include('partials.seo-head', ['seo' => $seo])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>
    {{-- GTM needs a noscript iframe immediately after <body> to work at all --}}
    @unless ($seo['noindex'])
        @if ($seo['settings']->google_tag_manager_id)
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $seo['settings']->google_tag_manager_id }}"
                height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        @endif
    @endunless

    @php
        $navClass = fn (array|string $routes): string => request()->routeIs(...(array) $routes) ? 'is-active' : '';
    @endphp
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="site-header" data-header>
        <nav class="nav container" aria-label="Primary navigation">
            <a class="brand" href="{{ route('home') }}" aria-label="HashTag home">
                <span class="brand-mark">H</span>
                <span>
                    <span class="brand-name">HashTag</span>
                    <span class="brand-line">Research & Technology</span>
                </span>
            </a>

            <button class="nav-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-nav-toggle>
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="nav-panel" data-nav-panel>
                <a href="{{ route('services.index') }}" class="{{ $navClass('services.*') }}">Services</a>
                <a href="{{ route('courses.index') }}" class="{{ $navClass('courses.*') }}">Courses</a>
                <a href="{{ route('about') }}" class="{{ $navClass('about') }}">About</a>
                <a href="{{ route('mission') }}" class="{{ $navClass('mission') }}">Mission</a>
                <a href="{{ route('vision') }}" class="{{ $navClass('vision') }}">Vision</a>
                <a href="{{ route('team') }}" class="{{ $navClass('team') }}">Team</a>
                <a href="{{ route('portfolio') }}" class="{{ $navClass('portfolio') }}">Portfolio</a>
                <a href="{{ route('contact') }}" class="nav-cta {{ $navClass('contact') }}">Get a Quote</a>
            </div>
        </nav>
    </header>

    <main id="main">
        @yield('content')
    </main>

    <footer class="footer" data-footer>
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="brand" style="margin-bottom: 1rem;">
                        <span class="brand-mark">H</span>
                        <span>
                            <span class="brand-name">HashTag</span>
                            <span class="brand-line">Research & Technology</span>
                        </span>
                    </div>
                    <p style="font-size: 0.9rem; color: rgba(255, 255, 255, 0.7);">
                        Smart IT Solutions & Digital Innovation for global clients.
                    </p>
                </div>
                <div>
                    <h3 style="font-size: 0.9rem; font-weight: 600; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Services</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;"><a href="{{ route('services.index') }}" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Services</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="{{ route('courses.index') }}" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Courses</a></li>
                    </ul>
                </div>
                <div>
                    <h3 style="font-size: 0.9rem; font-weight: 600; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Company</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;"><a href="{{ route('about') }}" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">About</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="{{ route('mission') }}" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Mission</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="{{ route('vision') }}" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Vision</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="{{ route('team') }}" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Team</a></li>
                    </ul>
                </div>
                <div>
                    <h3 style="font-size: 0.9rem; font-weight: 600; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Contact</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;"><a href="{{ route('contact') }}" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Get a Quote</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="{{ route('portfolio') }}" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Portfolio</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom" style="text-align: center;">
                <p>&copy; {{ date('Y') }} HashTag Research & Technology Ltd. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/script.js') }}"></script>
    @yield('scripts')

    @if ($seo['settings']->custom_body_snippet)
        {!! $seo['settings']->custom_body_snippet !!}
    @endif
</body>
</html>
