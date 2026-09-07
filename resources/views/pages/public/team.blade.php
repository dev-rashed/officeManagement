@extends('layouts.public')

@section('title', 'Team | HashTag Research & Technology Ltd.')

@section('content')
@php
    $section = $sections->firstWhere('section_key', 'main');
@endphp
<section class="section" style="min-height: 400px;">
    <div class="container">
        <div class="section-heading reveal">
            <p class="eyebrow">{{ $section->subtitle ?? 'Meet the Team' }}</p>
            <h2>{{ $section->title ?? 'Talented Professionals Committed to Excellence' }}</h2>
        </div>
        <p style="text-align: center; margin-top: 2rem;">{{ $section->description ?? 'Our team of experienced professionals is dedicated to delivering exceptional results.' }}</p>

        @if($teamMembers->isNotEmpty())
            <div class="detail-grid" style="margin-top: 3rem;">
                @foreach($teamMembers as $member)
                    <article class="detail-card reveal">
                        @if($member->image_path)
                            <img class="team-photo reveal" src="{{ asset('storage/' . $member->image_path) }}" alt="{{ $member->name }}" loading="lazy">
                        @endif
                        <h3>{{ $member->name }}</h3>
                        <p class="team-title">{{ $member->position }}</p>
                        @if($member->bio)
                            <p>{{ $member->bio }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
