<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecommendationController extends Controller
{
    public function index(Request $request, RecommendationService $recommendations): View
    {
        $user = $request->user();
        $books = Book::with(['category', 'authors'])->get();
        $ranked = $recommendations->rank($user, $books)
            ->filter(fn (Book $book) => $recommendations->isRecommended((int) $book->match_percentage))
            ->values();

        $topBooks = $ranked->take(24);

        $explanations = $topBooks->mapWithKeys(
            fn (Book $book) => [$book->id => $recommendations->explainMatch($user, $book)]
        );

        return view('user.recommendations', [
            'books' => $topBooks,
            'explanations' => $explanations,
            'hasProfile' => (bool) $user->profile,
        ]);
    }
}
