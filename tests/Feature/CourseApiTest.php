<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_and_list_courses(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Storage::fake('public');

        $this->postJson('/api/courses', [
            'title' => 'Laravel fundamentals',
            'description' => 'Build a course platform with Laravel.',
            'price' => '19.99',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');

        $course = $this->post('/api/courses', [
            'title' => 'Laravel fundamentals',
            'description' => 'Build a course platform with Laravel.',
            'price' => '19.99',
            'image' => UploadedFile::fake()->image('course.jpg', 600, 400),
        ])
            ->assertCreated()
            ->assertJsonPath('course.user_id', $user->id)
            ->assertJsonPath('course.likes_count', 0)
            ->assertJsonPath('course.liked_by_current_user', false)
            ->assertJsonPath('course.purchased_by_current_user', false)
            ->json('course');

        Storage::disk('public')->assertExists($course['image']);
        $this->assertStringContainsString("/storage/{$course['image']}", $course['image_url']);

        $this->assertDatabaseHas('courses', [
            'id' => $course['id'],
            'user_id' => $user->id,
            'title' => 'Laravel fundamentals',
        ]);

        $this->getJson('/api/courses')
            ->assertOk()
            ->assertJsonPath('courses.0.id', $course['id']);
    }

    public function test_only_course_author_can_delete_course(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        Storage::fake('public');
        $imagePath = 'courses/course-to-delete.jpg';
        Storage::disk('public')->put($imagePath, 'course image');
        $course = $author->courses()->create([
            'title' => 'Course owned by author',
            'description' => 'Only its author can delete this course.',
            'price' => '10.00',
            'image' => $imagePath,
        ]);

        Sanctum::actingAs($otherUser);

        $this->deleteJson("/api/courses/{$course->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
        Storage::disk('public')->assertExists($imagePath);

        Sanctum::actingAs($author);

        $this->deleteJson("/api/courses/{$course->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_likes_and_purchases_are_unique_per_user_and_course(): void
    {
        $user = User::factory()->create();
        $courseAuthor = User::factory()->create();
        $course = $courseAuthor->courses()->create([
            'title' => 'Course available to buy',
            'description' => 'A user can like and buy this course once.',
            'price' => '10.00',
        ]);
        Sanctum::actingAs($user);

        $this->postJson("/api/courses/{$course->id}/like")
            ->assertCreated()
            ->assertJsonPath('course.liked_by_current_user', true)
            ->assertJsonPath('course.likes_count', 1);

        $this->postJson("/api/courses/{$course->id}/like")
            ->assertOk();

        $this->assertDatabaseCount('course_likes', 1);

        $this->deleteJson("/api/courses/{$course->id}/like")
            ->assertOk()
            ->assertJsonPath('course.liked_by_current_user', false)
            ->assertJsonPath('course.likes_count', 0);

        $this->postJson("/api/courses/{$course->id}/purchase")
            ->assertCreated()
            ->assertJsonPath('course.purchased_by_current_user', true);

        $this->postJson("/api/courses/{$course->id}/purchase")
            ->assertOk();

        $this->assertDatabaseCount('course_purchases', 1);
    }
}
