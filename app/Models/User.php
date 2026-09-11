<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isModer(): bool
    {
        return $this->role === 'moder';
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function likedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_likes')->withTimestamps();
    }

    public function purchasedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_purchases')->withTimestamps();
    }
}
