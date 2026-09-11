<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()
            ->whereIn('email', [
                'aaa@mail.ru',
                'bbb@mail.ru',
                'ddd@mail.ru',
                'fff@mail.ru',
            ])
            ->get();

        foreach (range(1, 10) as $number) {
            $users->random()->courses()->create([
                'title' => fake()->sentence(3),
                'description' => fake()->paragraph(),
                'price' => fake()->randomFloat(2, 0, 500),
            ]);
        }
    }
}
