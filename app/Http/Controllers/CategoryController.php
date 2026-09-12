<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('books')->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category): View
    {
        $books = $category->books()->with('authors')->paginate(12);

        return view('categories.show', compact('category', 'books'));
    }
}
