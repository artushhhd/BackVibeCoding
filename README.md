# BackVibeCoding

> Previous version of the Course Platform API.  
> The actively maintained version is now available in **[junior-backend-api](https://github.com/artushhhd/junior-backend-api)**.

This repository contains the earlier backend implementation of a course marketplace built with Laravel and Sanctum. It is preserved as part of the project's development history.

## Frontend 
> frontend  **[frontendVibeCoding](https://github.com/artushhhd/Frontvibecoding)**

## Project Overview

The API provides the core backend functionality for a course marketplace:

- User registration and authentication
- Course management
- Image uploads
- Course likes
- Course purchases
- Ownership-based authorization
- API feature tests

The current version extends this foundation with a broader authorization system, administration and moderation features.

## Tech Stack

| Technology | Purpose |
|---|---|
| PHP 8.3 | Backend language |
| Laravel 13 | REST API framework |
| Laravel Sanctum | Token authentication |
| Eloquent ORM | Database access |
| MySQL / SQLite | Database |
| PHPUnit | Feature testing |

## Implemented Features

### Authentication

- Registration
- Login
- Logout
- Sanctum bearer tokens
- Authenticated profile endpoint

### Courses

- Create courses with image uploads
- List courses
- Delete courses with ownership authorization
- Like / unlike courses
- Record course purchases

The course listing uses Eloquent relationship aggregates such as `withCount` and `withExists` to avoid unnecessary N+1 queries for like and purchase state.

### Authorization

Course deletion is protected through Laravel's authorization layer and `CoursePolicy`.

The application also contains the foundation for multiple user roles:

- `user`
- `moder`
- `admin`
- `superadmin`

In this previous version, role-based administration was not yet connected to administrative routes.

## API Overview

```text
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/profile

GET    /api/courses
POST   /api/courses
DELETE /api/courses/{id}

POST   /api/courses/{id}/like
DELETE /api/courses/{id}/like
POST   /api/courses/{id}/purchase
```

## Project Structure

```text
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
└── Policies/

routes/
└── api.php

database/
└── migrations/

tests/
└── Feature/
    └── CourseApiTest.php
```

## Testing

Run the test suite with:

```bash
php artisan test
```

The tests cover course creation and validation, image storage, course listing and ownership-based deletion.

## Installation

```bash
git clone https://github.com/artushhhd/BackVibeCoding.git
cd BackVibeCoding

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

The API runs by default at:

```text
http://127.0.0.1:8000
```

## Current Version

For the latest implementation, including expanded authorization, administration, moderation and the current API architecture, see:

**[junior-backend-api](https://github.com/artushhhd/junior-backend-api)**

The corresponding frontend is:

**[junior-frontend-app](https://github.com/artushhhd/junior-frontend-app)**

## Project History

This repository represents an earlier stage of the project and is intentionally preserved to show how the application evolved from a smaller course API into a more complete full-stack system.
