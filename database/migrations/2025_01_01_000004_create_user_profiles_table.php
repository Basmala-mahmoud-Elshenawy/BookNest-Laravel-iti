<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // Stored as JSON arrays of free-text tags the matching engine tokenizes and compares to book metadata.
            $table->json('interests')->nullable();
            $table->json('favorite_topics')->nullable();
            $table->json('skills')->nullable();
            $table->json('learning_goals')->nullable();
            $table->json('preferred_category_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
