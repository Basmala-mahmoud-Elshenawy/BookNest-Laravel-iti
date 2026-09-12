<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@booknest.test'],
            ['name' => 'BookNest Admin', 'password' => Hash::make('password'), 'role' => 'admin']
        );
        UserProfile::firstOrCreate(['user_id' => $admin->id]);

        $programming = Category::where('name', 'Programming')->first();
        $ai = Category::where('name', 'Artificial Intelligence')->first();

        $demo = User::firstOrCreate(
            ['email' => 'demo@booknest.test'],
            ['name' => 'Demo Reader', 'password' => Hash::make('password'), 'role' => 'user']
        );
        UserProfile::updateOrCreate(['user_id' => $demo->id], [
            'interests' => ['python', 'javascript', 'machine learning', 'web development'],
            'favorite_topics' => ['clean code', 'algorithms', 'ai'],
            'skills' => ['git', 'sql', 'java'],
            'learning_goals' => ['learn deep learning', 'improve system design'],
            'preferred_category_ids' => array_filter([$programming?->id, $ai?->id]),
        ]);

        $reader = User::firstOrCreate(
            ['email' => 'reader@booknest.test'],
            ['name' => 'Layla Hassan', 'password' => Hash::make('password'), 'role' => 'user']
        );
        $arabicLit = Category::where('name', 'Arabic Literature')->first();
        UserProfile::updateOrCreate(['user_id' => $reader->id], [
            'interests' => ['novels', 'poetry', 'arabic literature'],
            'favorite_topics' => ['fiction', 'short stories'],
            'skills' => [],
            'learning_goals' => ['read more classic arabic novels'],
            'preferred_category_ids' => array_filter([$arabicLit?->id]),
        ]);
    }
}
