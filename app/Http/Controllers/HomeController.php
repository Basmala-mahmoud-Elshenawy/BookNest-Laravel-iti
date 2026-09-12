<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $latestBooks = Book::with(['category', 'authors'])->latest()->limit(8)->get();
        $categories = Category::withCount('books')->orderByDesc('books_count')->limit(8)->get();

        return view('home', compact('latestBooks', 'categories'));
    }
}
