<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\User;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $courses = $this->courseService->getAllCourses($request->user());

        return response()->json([
            'courses' => CourseResource::collection($courses)
        ]);
    }

    public function store(CourseRequest $request): JsonResponse
    {
        $course = $this->courseService->createCourse(
            $request->user(),
            $request->validated(),
            $request->file('image')
        );

        return response()->json([
            'message' => 'Course created successfully.',
            'course' => new CourseResource($this->courseService->getCourseForUser($course, $request->user())),
        ], 201);
    }

    public function destroy(Course $course): JsonResponse
    {
        $this->courseService->deleteCourse(auth()->user(), $course);

        return response()->json(null, 204);
    }

    public function like(Request $request, Course $course): JsonResponse
    {
        $isLiked = $course->likedByUsers()->where('user_id', $request->user()->id)->exists();
        
        $updatedCourse = $this->courseService->toggleLike($request->user(), $course, !$isLiked);

        return response()->json([
            'message' => $isLiked ? 'Like removed.' : 'Course liked successfully.',
            'course' => new CourseResource($updatedCourse),
        ]);
    }

    public function unlike(Request $request, Course $course): JsonResponse
    {
        // Integrated into the like method, but keeping for backward compatibility with routes
        $updatedCourse = $this->courseService->toggleLike($request->user(), $course, false);

        return response()->json([
            'message' => 'Course unliked successfully.',
            'course' => new CourseResource($updatedCourse),
        ]);
    }

    public function purchase(Request $request, Course $course): JsonResponse
    {
        try {
            $updatedCourse = $this->courseService->purchaseCourse($request->user(), $course);

            return response()->json([
                'message' => 'Course purchased successfully.',
                'course' => new CourseResource($updatedCourse),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
