<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, RecommendationService $recommendations): View
    {
        $user = $request->user();
        // Rank against the full catalog (not just the newest books) so the
        // dashboard actually surfaces the best matches, then drop 0% matches
        // -- an unrelated book padded in at 0% isn't a "recommendation".
        $candidateBooks = Book::with(['category', 'authors'])->get();
        $recommended = $recommendations->rank($user, $candidateBooks)
            ->filter(fn (Book $book) => $recommendations->isRecommended((int) $book->match_percentage))
            ->take(6)
            ->values();
        $currentBorrowings = $user->borrowings()->with('book')->currentlyActive()->latest('borrowed_at')->get();
        $recentBorrowings = $user->borrowings()->with('book')->latest('borrowed_at')->limit(6)->get();
        $favoritesCount = $user->favorites()->count();
        $overdueCount = $currentBorrowings->filter(fn ($b) => $b->isOverdue())->count();

        return view('user.dashboard', compact('recommended','currentBorrowings','recentBorrowings','favoritesCount','overdueCount'));
    }
}
