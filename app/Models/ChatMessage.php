<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['user_id', 'role_at_time', 'prompt', 'response', 'was_rejected', 'rejection_reason'];

    protected function casts(): array
    {
        return [
            'was_rejected' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
