<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function register(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'balance' => 1000, // Starting bonus balance for new users
        ]);
    }

    public function login(array $credentials)
    {
        if (!\Illuminate\Support\Facades\Auth::attempt($credentials)) {
            return null;
        }

        return User::where('email', $credentials['email'])->first();
    }
}
