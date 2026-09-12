<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Borrowing extends Model
{
    protected $fillable = ['user_id', 'book_id', 'borrowed_at', 'due_at', 'returned_at', 'status'];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function book() { return $this->belongsTo(Book::class); }

    /** Effective status is derived from dates so an active record cannot stay stale after its due date. */
    public function getStatusAttribute($value): string
    {
        if ($this->returned_at !== null || $value === 'returned') {
            return 'returned';
        }

        if ($this->due_at && $this->due_at->isPast()) {
            return 'overdue';
        }

        return 'active';
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $query->whereNull('returned_at')->whereIn('status', ['active', 'overdue']);
    }

    public function isOverdue(): bool
    {
        return $this->returned_at === null && $this->due_at?->isPast() === true;
    }
}
