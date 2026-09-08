{{--
    All SEO tags and Google tooling for a public page.

    $seo comes from SeoService::resolve() via the ShareSeoData composer, so
    every public view has it without each controller passing it.
--}}
@php($settings = $seo['settings'])

<title>{{ $seo['title'] }}</title>

<meta name="description" content="{{ $seo['description'] }}">
@if ($seo['keywords'])
    <meta name="keywords" content="{{ $seo['keywords'] }}">
@endif

<link rel="canonical" href="{{ $seo['canonical'] }}">

@if ($seo['noindex'] || $seo['nofollow'])
    <meta name="robots" content="{{ $seo['noindex'] ? 'noindex' : 'index' }},{{ $seo['nofollow'] ? 'nofollow' : 'follow' }}">
@else
    <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
@endif

{{-- Search Console / Bing ownership --}}
@if ($settings->google_site_verification)
    <meta name="google-site-verification" content="{{ $settings->google_site_verification }}">
@endif
@if ($settings->bing_site_verification)
    <meta name="msvalidate.01" content="{{ $settings->bing_site_verification }}">
@endif

{{-- Open Graph --}}
<meta property="og:type" content="{{ $seo['og_type'] }}">
<meta property="og:site_name" content="{{ $settings->site_name }}">
<meta property="og:title" content="{{ $seo['og_title'] }}">
<meta property="og:description" content="{{ $seo['og_description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:locale" content="en_US">
@if ($seo['og_image'])
    <meta property="og:image" content="{{ $seo['og_image'] }}">
    <meta property="og:image:alt" content="{{ $seo['og_title'] }}">
@endif

{{-- Twitter / X --}}
<meta name="twitter:card" content="{{ $seo['og_image'] ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo['og_title'] }}">
<meta name="twitter:description" content="{{ $seo['og_description'] }}">
@if ($seo['og_image'])
    <meta name="twitter:image" content="{{ $seo['og_image'] }}">
@endif
@if ($seo['twitter_handle'])
    <meta name="twitter:site" content="{{ Str::start($seo['twitter_handle'], '@') }}">
@endif

{{-- Organisation structured data --}}
@if ($organizationSchema = app(\App\Services\SeoService::class)->organizationSchema())
    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif

{{--
    Google Tag Manager and GA4.

    Only one is normally wanted: if GTM is set, GA4 is usually configured
    inside GTM instead. Both are emitted if both are filled, which double-counts
    pageviews -- the settings screen warns about that.

    Analytics never loads for a page marked noindex, so staging and hidden
    pages stay out of the reports.
--}}
@unless ($seo['noindex'])
    @if ($settings->google_tag_manager_id)
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
            var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
            j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{{ $settings->google_tag_manager_id }}');
        </script>
    @endif

    @if ($settings->google_analytics_id)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings->google_analytics_id }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $settings->google_analytics_id }}');
        </script>
    @endif
@endunless

@if ($settings->custom_head_snippet)
    {!! $settings->custom_head_snippet !!}
@endif
