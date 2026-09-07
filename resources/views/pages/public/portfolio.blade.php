@extends('layouts.public')

@section('title', 'Portfolio | HashTag Research & Technology Ltd.')

@section('content')
@php
    $section = $sections->firstWhere('section_key', 'main');
@endphp
<section class="section" style="min-height: 400px;">
    <div class="container">
        <div class="section-heading reveal">
            <p class="eyebrow">{{ $section->subtitle ?? 'Portfolio' }}</p>
            <h2>{{ $section->title ?? 'Digital Solutions Delivered for Real Business Needs' }}</h2>
        </div>
        <p style="text-align: center; margin-top: 2rem;">{{ $section->description ?? 'From ERP systems to international e-commerce builds and training programs, our portfolio blends practical engineering with long-term support.' }}</p>

        @if($portfolioItems->isNotEmpty())
            <div class="project-grid" style="margin-top: 3rem;">
                @foreach($portfolioItems as $item)
                    <article class="project-card reveal">
                        @if($item->image_path)
                            <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->title }}" loading="lazy">
                        @endif
                        <div class="project-content">
                            <h3>{{ $item->title }}</h3>
                            <p>{{ $item->description }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
