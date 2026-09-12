<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Author extends Model
{
    protected $fillable = ['name', 'slug', 'bio'];

    protected static function booted(): void
    {
        static::saving(function (Author $author) {
            if (empty($author->slug)) {
                $author->slug = Str::slug($author->name).'-'.Str::random(4);
            }
        });
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'author_book');
    }
}
