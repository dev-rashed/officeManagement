<?php

namespace App\Http\Controllers\Public;

use App\Models\Course;
use Illuminate\Routing\Controller;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::published()->sorted()->get();
        return view('pages.public.courses', compact('courses'));
    }

    public function show($slug)
    {
        $course = Course::where('slug', $slug)->published()->firstOrFail();
        return view('pages.public.course-detail', compact('course'));
    }
}
