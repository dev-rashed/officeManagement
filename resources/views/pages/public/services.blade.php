@extends('layouts.public')

@section('title', 'Services | HashTag Research & Technology Ltd.')

@section('content')
<section class="section">
    <div class="container">
        <div class="section-heading reveal">
            <p class="eyebrow">Our Services</p>
            <h2>End-to-end IT solutions tailored to your business</h2>
        </div>

        <div class="services-grid">
            @forelse($services as $service)
            <article class="service-card reveal">
                <a href="{{ route('services.show', $service->slug) }}">
                    <span class="service-index">{{ $loop->iteration }}</span>
                    <h3>{{ $service->title }}</h3>
                    <p>{{ $service->short_description }}</p>
                </a>
            </article>
            @empty
            <p style="grid-column: 1 / -1; text-align: center; padding: 2rem;">No services available yet. Check back soon!</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
