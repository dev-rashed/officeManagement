@extends('layouts.public')

@section('title', '{{ $service->title }} | Services | HashTag')

@section('content')
<section class="section" style="min-height: 500px;">
    <div class="container">
        @if($service->featured_image)
        <div style="margin-bottom: 2rem;">
            <img src="{{ asset('storage/' . $service->featured_image) }}" alt="{{ $service->title }}" style="width: 100%; height: auto; border-radius: 8px;">
        </div>
        @endif

        <div style="max-width: 800px;">
            <p class="eyebrow">Service Details</p>
            <h1>{{ $service->title }}</h1>
            <p style="font-size: 1.1rem; margin: 1.5rem 0;">{{ $service->short_description }}</p>

            <div style="margin: 2rem 0;">
                <h2>About This Service</h2>
                {!! $service->full_description !!}
            </div>

            <div style="margin: 2rem 0;">
                <a class="btn btn-primary" href="{{ route('contact') }}">Get a Quote</a>
            </div>
        </div>
    </div>
</section>
@endsection
