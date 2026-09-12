<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->index(['user_id', 'book_id', 'returned_at']);
            $table->index(['book_id', 'status']);
            $table->index(['due_at', 'returned_at']);
        });
    }

    public function down(): void
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'book_id', 'returned_at']);
            $table->dropIndex(['book_id', 'status']);
            $table->dropIndex(['due_at', 'returned_at']);
        });
    }
};
