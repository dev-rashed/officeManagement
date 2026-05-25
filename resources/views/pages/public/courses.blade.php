@extends('layouts.public')

@section('title', 'Courses | HashTag Research & Technology Ltd.')

@section('content')
<section class="section">
    <div class="container">
        <div class="section-heading reveal">
            <p class="eyebrow">Professional Courses</p>
            <h2>Skill-focused training for digital careers</h2>
        </div>

        <div class="courses-grid">
            @forelse($courses as $course)
            <article class="course-card reveal">
                <a href="{{ route('courses.show', $course->slug) }}">
                    @if($course->featured_image)
                    <div class="course-image">
                        <img src="{{ asset('storage/' . $course->featured_image) }}" alt="{{ $course->title }}" width="640" height="360" loading="lazy">
                    </div>
                    @endif
                    <span class="course-tag">{{ $course->category ?? 'Course' }}</span>
                    <h3>{{ $course->title }}</h3>
                    <p>{{ $course->short_description }}</p>
                    <div class="course-meta">
                        <span>{{ $course->duration ?? 'Duration' }}</span>
                        <span>{{ $course->class_type ?? 'Offline' }}</span>
                    </div>
                </a>
            </article>
            @empty
            <p style="grid-column: 1 / -1; text-align: center; padding: 2rem;">No courses available yet. Check back soon!</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
