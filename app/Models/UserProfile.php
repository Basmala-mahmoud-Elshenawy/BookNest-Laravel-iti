<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'interests',
        'favorite_topics',
        'skills',
        'learning_goals',
        'preferred_category_ids',
    ];

    protected function casts(): array
    {
        return [
            'interests' => 'array',
            'favorite_topics' => 'array',
            'skills' => 'array',
            'learning_goals' => 'array',
            'preferred_category_ids' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Flatten every free-text preference field into one lowercase token bag
     * for the recommendation engine to compare against book metadata.
     */
    public function tokenBag(): array
    {
        $fields = array_merge(
            $this->interests ?? [],
            $this->favorite_topics ?? [],
            $this->skills ?? [],
            $this->learning_goals ?? [],
        );

        return collect($fields)
            ->flatMap(fn ($value) => preg_split('/[\s,]+/', (string) $value))
            ->map(fn ($token) => strtolower(trim($token)))
            ->filter(fn ($token) => preg_match('/[^\x00-\x7F]/', $token) ? preg_match_all('/./us', $token) > 1 : strlen($token) > 1)
            ->unique()
            ->values()
            ->all();
    }
}
