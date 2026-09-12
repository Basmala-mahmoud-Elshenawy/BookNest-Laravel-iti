<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public, read-only book browsing/search/filter. Every query here is safe
 * for any authenticated or guest user; nothing admin-only is exposed.
 */
class BookController extends Controller
{
    public function index(Request $request, RecommendationService $recommendations): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'available' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:latest,match,title'],
        ]);

        $books = Book::query()
            ->with(['category', 'authors'])
            ->search($validated['q'] ?? null)
            ->inCategory($validated['category'] ?? null)
            ->availableOnly((bool) ($validated['available'] ?? false))
            ->when(($validated['sort'] ?? 'latest') === 'title', fn ($q) => $q->orderBy('title'))
            ->when(($validated['sort'] ?? 'latest') === 'latest', fn ($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();

        if (($validated['sort'] ?? null) === 'match' && $request->user()) {
            $ranked = $recommendations->rank($request->user(), collect($books->items()));
            $books->setCollection($ranked);
        }

        // Expose a personalized match on every catalog card for logged-in users.
        if ($request->user()) {
            $books->getCollection()->each(function (Book $book) use ($request, $recommendations) {
                $book->setAttribute('match_percentage', $recommendations->matchPercentage($request->user(), $book));
                $book->setAttribute('match_label', $recommendations->matchLabel($book->match_percentage));
            });
        }

        $categories = Category::orderBy('name')->get();

        return view('books.index', compact('books', 'categories', 'validated'));
    }

    public function show(Book $book, RecommendationService $recommendations): View
    {
        $book->load(['category', 'authors']);

        $matchPercentage = null;
        if ($user = request()->user()) {
            $matchPercentage = $recommendations->matchPercentage($user, $book);
        }

        $related = Book::with(['category', 'authors'])->where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->limit(4)
            ->get();

        return view('books.show', compact('book', 'matchPercentage', 'related'));
    }
}
