<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Book extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'description',
        'isbn',
        'published_at',
        'language',
        'total_copies',
        'available_copies',
        'cover_path',
        'cover_source',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Book $book) {
            if (empty($book->slug)) {
                $book->slug = Str::slug($book->title).'-'.Str::lower(Str::random(5));
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_book');
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    public function favoredBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function isAvailable(): bool
    {
        return $this->available_copies > 0;
    }

    public function status(): string
    {
        if ($this->available_copies > 0) {
            return 'available';
        }

        return $this->borrowings()->where('status', 'active')->exists() ? 'borrowed' : 'reserved';
    }

    public function coverUrl(): string
    {
        if ($this->cover_path && file_exists(public_path('storage/'.$this->cover_path))) {
            return asset('storage/'.$this->cover_path);
        }

        return asset('images/book-placeholder.svg');
    }

    /**
     * Backend-only search across authorized, searchable fields.
     * Used by both the public search page and the AI book-search flow so
     * both go through the exact same authorized query.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('isbn', 'like', $like)
                ->orWhereHas('authors', fn ($a) => $a->where('name', 'like', $like))
                ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like));
        });
    }

    public function scopeInCategory(Builder $query, ?int $categoryId): Builder
    {
        return $categoryId ? $query->where('category_id', $categoryId) : $query;
    }

    public function scopeAvailableOnly(Builder $query, bool $onlyAvailable): Builder
    {
        return $onlyAvailable ? $query->where('available_copies', '>', 0) : $query;
    }

    /** Flatten book metadata into a lowercase token bag for matching. */
    public function tokenBag(): array
    {
        // Include the category description as well as its name. This lets
        // profile preferences such as "novels", "fiction", "AI", or
        // "machine learning" match the meaning of a category even when
        // the individual book has no description of its own.
        $text = collect([
            $this->title,
            $this->description,
            $this->category?->name,
            $this->category?->description,
            $this->authors->pluck('name')->implode(' '),
        ])->filter()->implode(' ');

        return collect(preg_split('/[\s,]+/', $text))
            ->map(fn ($token) => strtolower(trim($token)))
            ->filter(fn ($token) => strlen($token) > 1)
            ->unique()
            ->values()
            ->all();
    }
}
