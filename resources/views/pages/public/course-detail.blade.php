@extends('layouts.public')

@section('title', $course->title . ' | Courses | HashTag')

@section('content')
<section class="section" style="min-height: 500px;">
    <div class="container">
        @if($course->featured_image)
        <div style="margin-bottom: 2rem;">
            <img src="{{ asset('storage/' . $course->featured_image) }}" alt="{{ $course->title }}" style="width: 100%; height: auto; border-radius: 8px;">
        </div>
        @endif

        <div style="max-width: 800px;">
            <p class="eyebrow">{{ $course->category?->name ?? 'Course' }}</p>
            <h1>{{ $course->title }}</h1>
            <p style="font-size: 1.1rem; margin: 1.5rem 0;">{{ $course->short_description }}</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin: 2rem 0;">
                @if($course->duration)
                <div>
                    <strong>Duration</strong>
                    <p>{{ $course->duration }}</p>
                </div>
                @endif
                @if($course->location)
                <div>
                    <strong>Location</strong>
                    <p>{{ $course->location }}</p>
                </div>
                @endif
                @if($course->fee)
                <div>
                    <strong>Fee</strong>
                    <p>{{ $course->fee }}</p>
                </div>
                @endif
            </div>

            <div style="margin: 2rem 0;">
                <h2>Course Overview</h2>
                {!! $course->full_description !!}
            </div>

            @if($course->course_outline)
            <div style="margin: 2rem 0;">
                <h2>Course Outline</h2>
                {!! $course->course_outline !!}
            </div>
            @endif

            <div style="margin: 2rem 0;">
                <a class="btn btn-primary" href="{{ route('contact') }}">Get More Information</a>
            </div>
        </div>
    </div>
</section>
@endsection
