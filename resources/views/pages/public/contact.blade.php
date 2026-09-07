@extends('layouts.public')

@section('title', 'Contact Us | HashTag Research & Technology Ltd.')

@section('content')
@php
    $section = $sections->firstWhere('section_key', 'main');
@endphp
<section class="section" style="min-height: 400px;">
    <div class="container">
        <div class="section-heading reveal">
            <p class="eyebrow">{{ $section->subtitle ?? 'Get in Touch' }}</p>
            <h2>{{ $section->title ?? "Let's Talk About Your Next Project" }}</h2>
        </div>
        <div style="max-width: 600px; margin: 3rem auto; text-align: center;">
            <p style="margin-bottom: 2rem;">{{ $section->description ?? "We'd love to hear from you. Fill out the form on our homepage or reach out directly." }}</p>
            <a href="mailto:{{ $contact->email }}" style="color: #F59E0B; font-weight: 600;">{{ $contact->email }}</a>
            @if($contact->phone)
                <p style="margin-top: 1rem;">{{ $contact->phone }}</p>
            @endif
            @if($contact->address)
                <p>{{ $contact->address }}</p>
            @endif
        </div>
    </div>
</section>
@endsection
