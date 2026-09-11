<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $courses = $this->courseQueryFor($request->user())
            ->latest()
            ->get();

        return response()->json(['courses' => $courses]);
    }

    public function store(CourseRequest $request): JsonResponse
    {
        $courseData = $request->validated();
        $courseData['image'] = $request->file('image')->store('courses', 'public');

        $course = $request->user()->courses()->create($courseData);

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => $this->courseForUser($course, $request->user()),
        ], 201);
    }

    public function destroy(Course $course): JsonResponse
    {
        Gate::authorize('delete', $course);

        $imagePath = $course->image;
        $course->delete();

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return response()->json(null, 204);
    }

    public function like(Request $request, Course $course): JsonResponse
    {
        $changes = $course->likedByUsers()->syncWithoutDetaching([$request->user()->id]);

        return response()->json([
            'message' => empty($changes['attached']) ? 'Course is already liked.' : 'Course liked successfully.',
            'course' => $this->courseForUser($course, $request->user()),
        ], empty($changes['attached']) ? 200 : 201);
    }

    public function unlike(Request $request, Course $course): JsonResponse
    {
        $course->likedByUsers()->detach($request->user()->id);

        return response()->json([
            'message' => 'Course unliked successfully.',
            'course' => $this->courseForUser($course, $request->user()),
        ]);
    }

    public function purchase(Request $request, Course $course): JsonResponse
    {
        $changes = $course->purchasers()->syncWithoutDetaching([$request->user()->id]);

        return response()->json([
            'message' => empty($changes['attached']) ? 'Course is already purchased.' : 'Course purchased successfully.',
            'course' => $this->courseForUser($course, $request->user()),
        ], empty($changes['attached']) ? 200 : 201);
    }

    private function courseForUser(Course $course, User $user): Course
    {
        return $this->courseQueryFor($user)
            ->whereKey($course->getKey())
            ->firstOrFail();
    }

    private function courseQueryFor(User $user): Builder
    {
        return Course::query()
            ->with('author:id,name')
            ->withCount(['likedByUsers as likes_count'])
            ->withExists([
                'likedByUsers as liked_by_current_user' => fn (Builder $query) => $query->where('users.id', $user->id),
                'purchasers as purchased_by_current_user' => fn (Builder $query) => $query->where('users.id', $user->id),
            ]);
    }
}
