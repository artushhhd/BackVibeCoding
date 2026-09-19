<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class CourseService
{
    public function getAllCourses(User $user)
    {
        return Course::query()
            ->with('author:id,name')
            ->withCount(['likedByUsers as likes_count'])
            ->withExists([
                'likedByUsers as liked_by_current_user' => fn ($query) => $query->where('users.id', $user->id),
                'purchasers as purchased_by_current_user' => fn ($query) => $query->where('users.id', $user->id),
            ])
            ->latest()
            ->get();
    }

    public function createCourse(User $user, array $data, $imageFile)
    {
        $data['image'] = $imageFile->store('courses', 'public');
        return $user->courses()->create($data);
    }

    public function deleteCourse(User $user, Course $course)
    {
        Gate::authorize('delete', $course);

        $imagePath = $course->image;
        $course->delete();

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return true;
    }

    public function toggleLike(User $user, Course $course, bool $like = true)
    {
        if ($like) {
            $course->likedByUsers()->syncWithoutDetaching([$user->id]);
        } else {
            $course->likedByUsers()->detach($user->id);
        }

        return $this->getCourseForUser($course, $user);
    }

    public function purchaseCourse(User $user, Course $course)
    {
        if ($user->purchasedCourses()->where('course_id', $course->id)->exists()) {
            throw new \Exception('Course is already purchased.');
        }

        if ($user->balance < $course->price) {
            throw new \Exception('Insufficient balance to purchase this course.');
        }

        // Subtract balance
        $user->decrement('balance', $course->price);
        
        // Add to purchases
        $course->purchasers()->attach($user->id);

        return $this->getCourseForUser($course, $user);
    }

    public function getCourseForUser(Course $course, User $user)
    {
        return Course::query()
            ->with('author:id,name')
            ->withCount(['likedByUsers as likes_count'])
            ->withExists([
                'likedByUsers as liked_by_current_user' => fn ($query) => $query->where('users.id', $user->id),
                'purchasers as purchased_by_current_user' => fn ($query) => $query->where('users.id', $user->id),
            ])
            ->whereKey($course->getKey())
            ->firstOrFail();
    }
}
