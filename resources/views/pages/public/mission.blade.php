@extends('layouts.public')

@section('title', 'Our Mission | HashTag Research & Technology Ltd.')

@section('content')
@php
    $section = $sections->firstWhere('section_key', 'main');
@endphp
<section class="section" style="min-height: 400px;">
    <div class="container">
        <div class="section-heading reveal">
            <p class="eyebrow">{{ $section->subtitle ?? 'Our Mission' }}</p>
            <h2>{{ $section->title ?? 'Empowering Innovation Through Technology' }}</h2>
        </div>
        <div class="about-copy reveal">
            <p>{{ $section->description ?? 'Our mission is to deliver cutting-edge IT solutions that empower businesses and individuals to succeed in the digital age.' }}</p>
        </div>
    </div>
</section>
@endsection
