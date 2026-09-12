<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Favorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $favorites = $request->user()->favoriteBooks()->with(['category', 'authors'])->latest('favorites.created_at')->paginate(12)->withQueryString();
        return view('user.favorites', compact('favorites'));
    }

    public function toggle(Request $request, Book $book): RedirectResponse
    {
        $favorite = Favorite::firstWhere(['user_id' => $request->user()->id, 'book_id' => $book->id]);

        if ($favorite) {
            $favorite->delete();
            return back()->with('status', 'Removed from favorites.');
        }

        Favorite::create(['user_id' => $request->user()->id, 'book_id' => $book->id]);
        return back()->with('status', 'Added to favorites.');
    }

    public function destroy(Request $request, Favorite $favorite): RedirectResponse
    {
        abort_unless($favorite->user_id === $request->user()->id, 403);
        $favorite->delete();
        return back()->with('status', 'Removed from favorites.');
    }
}
