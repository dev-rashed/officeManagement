@extends('layouts.public')

@section('title', 'About Us | HashTag Research & Technology Ltd.')

@section('content')
@php
    $section = $sections->firstWhere('section_key', 'main');
@endphp
<section class="section" style="min-height: 400px;">
    <div class="container">
        <div class="section-heading reveal">
            <p class="eyebrow">{{ $section->subtitle ?? 'About Us' }}</p>
            <h2>{{ $section->title ?? 'HashTag Research & Technology Ltd.' }}</h2>
        </div>
        <div class="about-copy reveal">
            @if($section?->description)
                {!! nl2br(e($section->description)) !!}
            @else
                <p>HashTag Research & Technology Ltd. is a leading IT services and consulting company in Bangladesh, committed to innovation, quality, and client success.</p>
                <p>Since 2017, we have delivered scalable software, digital solutions, and training programs to clients across more than 25 countries worldwide.</p>
            @endif
        </div>
    </div>
</section>
@endsection
