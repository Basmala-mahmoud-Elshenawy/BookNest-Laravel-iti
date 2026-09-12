<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Programming', 'description' => 'General software development and programming languages.'],
            ['name' => 'Artificial Intelligence', 'description' => 'Machine learning, deep learning, and AI theory.'],
            ['name' => 'Database', 'description' => 'Database systems, SQL, and data modeling.'],
            ['name' => 'Web Development', 'description' => 'Front-end and back-end web technologies.'],
            ['name' => 'Cyber Security', 'description' => 'Security, penetration testing, and Linux hardening.'],
            ['name' => 'Networking', 'description' => 'Computer networks and systems.'],
            ['name' => 'Business', 'description' => 'Business, productivity, and personal development.'],
            ['name' => 'Science', 'description' => 'Science, mathematics, and technical non-fiction.'],
            ['name' => 'Literature', 'description' => 'Novels, fiction, and English-language literature.'],
            ['name' => 'Arabic Literature', 'description' => 'Novels and literary works in Arabic.'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
