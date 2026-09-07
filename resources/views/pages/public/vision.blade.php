@extends('layouts.public')

@section('title', 'Our Vision | HashTag Research & Technology Ltd.')

@section('content')
@php
    $section = $sections->firstWhere('section_key', 'main');
@endphp
<section class="section" style="min-height: 400px;">
    <div class="container">
        <div class="section-heading reveal">
            <p class="eyebrow">{{ $section->subtitle ?? 'Our Vision' }}</p>
            <h2>{{ $section->title ?? 'Building the Future of Digital Solutions' }}</h2>
        </div>
        <div class="about-copy reveal">
            <p>{{ $section->description ?? 'We envision a world where innovative technology is accessible to businesses of all sizes, creating opportunities and driving sustainable growth.' }}</p>
        </div>
    </div>
</section>
@endsection
