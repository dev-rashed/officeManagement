<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('cms.manage');

        if ($request->ajax()) {
            $draw = $request->input('draw');
            $start = $request->input('start');
            $length = $request->input('length');
            $search = $request->input('search.value');
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = in_array($request->input('order.0.dir'), ['asc', 'desc'], true) ? $request->input('order.0.dir') : 'asc';

            $query = Course::with('category');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('short_description', 'like', "%{$search}%")
                      ->orWhere('full_description', 'like', "%{$search}%");
                });
            }

            $totalRecords = Course::count();
            $filteredRecords = $query->count();

            $columns = ['id', 'title', 'category.name', 'duration', 'status', 'sort_order'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';
            if ($orderColumn === 'category.name') {
                $query->orderBy('category.name', $orderDirection);
            } else {
                $query->orderBy($orderColumn, $orderDirection);
            }

            $courses = $query->skip($start)->take($length)->get();

            $data = $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'category' => $course->category ? $course->category->name : '-',
                    'duration' => $course->duration ?? '-',
                    'fee' => $course->fee ?? '-',
                    'status' => $course->status === 'published' ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>',
                    'sort_order' => $course->sort_order,
                    'actions' => '<div class="btn-group btn-group-sm" role="group">
                        <a href="' . route('admin.courses.edit', $course->id) . '" class="btn btn-outline-primary btn-sm">
                            <i class="fi fi-rr-edit"></i>
                        </a>
                        <form action="' . route('admin.courses.destroy', $course->id) . '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this course?\');" style="display: inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fi fi-rr-trash"></i>
                            </button>
                        </form>
                    </div>',
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        $categories = CourseCategory::published()->pluck('name', 'id');
        return view('pages.admin.courses.index', compact('categories'));
    }

    public function create()
    {
        Gate::authorize('cms.manage');
        $categories = CourseCategory::published()->pluck('name', 'id');
        return view('pages.admin.courses.form', [
            'course' => null,
            'mode' => 'create',
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:courses',
            'short_description' => 'required|string',
            'full_description' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'category_id' => 'nullable|exists:course_categories,id',
            'duration' => 'nullable|string|max:50',
            'class_type' => 'required|in:offline,online,hybrid',
            'location' => 'nullable|string|max:100',
            'schedule_batch' => 'nullable|string|max:100',
            'fee' => 'nullable|string|max:50',
            'instructor_name' => 'nullable|string|max:100',
            'course_outline' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
        ]);

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('uploads/courses', 'public');
            $validated['featured_image'] = $path;
        }

        Course::create($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function edit(Course $course)
    {
        Gate::authorize('cms.manage');
        $categories = CourseCategory::published()->pluck('name', 'id');
        return view('pages.admin.courses.form', [
            'course' => $course,
            'mode' => 'edit',
            'categories' => $categories
        ]);
    }

    public function update(Request $request, Course $course)
    {
        Gate::authorize('cms.manage');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:courses,slug,' . $course->id,
            'short_description' => 'required|string',
            'full_description' => 'nullable|string',
            'featured_image' => 'nullable|image|max:5120',
            'category_id' => 'nullable|exists:course_categories,id',
            'duration' => 'nullable|string|max:50',
            'class_type' => 'required|in:offline,online,hybrid',
            'location' => 'nullable|string|max:100',
            'schedule_batch' => 'nullable|string|max:100',
            'fee' => 'nullable|string|max:50',
            'instructor_name' => 'nullable|string|max:100',
            'course_outline' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($course->featured_image) {
                Storage::disk('public')->delete($course->featured_image);
            }
            $path = $request->file('featured_image')->store('uploads/courses', 'public');
            $validated['featured_image'] = $path;
        }

        $course->update($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        Gate::authorize('cms.manage');

        if ($course->featured_image) {
            Storage::disk('public')->delete($course->featured_image);
        }

        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    public function data(Request $request)
    {
        return $this->index($request);
    }

    // Public methods
    public function publicIndex()
    {
        $courses = Course::published()->sorted()->get();
        return view('pages.public.courses', compact('courses'));
    }

    public function publicShow($slug)
    {
        $course = Course::where('slug', $slug)->published()->firstOrFail();
        return view('pages.public.course-detail', compact('course'));
    }
}
