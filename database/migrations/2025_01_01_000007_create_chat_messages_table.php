<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stores chatbot turns for audit purposes: what a role asked and what
        // (already-authorized) context it was answered with. Never used by the
        // app to grant permissions -- purely a log.
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_at_time', 20);
            $table->text('prompt');
            $table->text('response');
            $table->boolean('was_rejected')->default(false);
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
