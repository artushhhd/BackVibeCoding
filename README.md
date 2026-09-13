# BackVibeCoding

REST API for a course marketplace, built with Laravel + Sanctum. Users can list courses, like and purchase them. Frontend counterpart: [Frontvibecoding](https://github.com/yourname/Frontvibecoding-).

## Stack

- PHP 8.3, Laravel 13
- Sanctum (token auth)
- Eloquent + migrations, feature tests with Sanctum + fake storage

## What's implemented

**Auth**
- Register / login / logout with Sanctum tokens
- `/profile` returns the current user + role

**Courses**
- Create a course with a required image upload (stored on the `public` disk)
- List courses with `likes_count`, `liked_by_current_user` and `purchased_by_current_user` computed per request via `withCount`/`withExists` (no N+1)
- Delete — only the author can delete their own course, enforced through a real `CoursePolicy` (`Gate::authorize('delete', $course)`)
- Like / unlike (`course_likes` pivot)
- Purchase (`course_purchases` pivot) — currently just records the purchase, no payment integration

**Roles**
`User` has `role` (`user`/`moder`/`admin`/`superadmin`) and helper methods (`isAdmin()`, `isModer()`, etc.), but there's no admin panel or route using them yet — the groundwork is there for moderation features, not wired up.

## Structure

```
app/
├── Http/
│   ├── Controllers/   # UserController, CourseController
│   └── Requests/      # RegisterRequest, LoginRequest, CourseRequest
├── Models/             # User, Course
└── Policies/            # CoursePolicy
routes/api.php
database/migrations/
tests/Feature/CourseApiTest.php
```

## Endpoints

```
POST   /api/register
POST   /api/login
POST   /api/logout                (auth)
GET    /api/profile               (auth)

GET    /api/courses               (auth)
POST   /api/courses               (auth)
DELETE /api/courses/{id}          (auth, owner only)
POST   /api/courses/{id}/like     (auth)
DELETE /api/courses/{id}/like     (auth)
POST   /api/courses/{id}/purchase (auth)
```

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Runs on `http://127.0.0.1:8000`.

## Tests

```bash
php artisan test
```

Covers course creation/listing (including image validation and storage) and the author-only delete rule.

## Known gaps

- no pagination on `GET /api/courses`
- role fields exist but nothing actually restricts anything by role yet
- purchase is a plain DB record, no real payment flow
