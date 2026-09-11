<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'aaa',
                'email' => 'aaa@mail.ru',
                'password' => 'aaa',
                'role' => 'superadmin',
            ],
            [
                'name' => 'bbb',
                'email' => 'bbb@mail.ru',
                'password' => 'bbb',
                'role' => 'admin',
            ],
            [
                'name' => 'ddd',
                'email' => 'ddd@mail.ru',
                'password' => 'ddd',
                'role' => 'admin',
            ],
            [
                'name' => 'fff',
                'email' => 'fff@mail.ru',
                'password' => 'fff',
                'role' => 'admin',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                ]
            );
        }
    }
}
