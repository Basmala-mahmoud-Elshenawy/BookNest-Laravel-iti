<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $books = Book::query()
            ->with(['category', 'authors'])
            ->search($request->string('q')->toString() ?: null)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.books.index', compact('books'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.books.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $book = Book::create($validated['book']);
        $this->syncAuthors($book, $validated['author_names']);
        $this->handleCoverUpload($request, $book);

        return redirect()->route('admin.books.index')->with('status', 'Book created.');
    }

    public function edit(Book $book): View
    {
        $categories = Category::orderBy('name')->get();
        $book->load('authors');

        return view('admin.books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $validated = $this->validated($request, $book->id);

        $book->update($validated['book']);
        $this->syncAuthors($book, $validated['author_names']);
        $this->handleCoverUpload($request, $book);

        return redirect()->route('admin.books.index')->with('status', 'Book updated.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return redirect()->route('admin.books.index')->with('status', 'Book deleted.');
    }

    private function validated(Request $request, ?int $bookId = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'isbn' => ['nullable', 'string', 'max:32', 'unique:books,isbn'.($bookId ? ",$bookId" : '')],
            'published_at' => ['nullable', 'date'],
            'total_copies' => ['required', 'integer', 'min:0'],
            'available_copies' => ['required', 'integer', 'min:0', 'lte:total_copies'],
            'authors' => ['required', 'string', 'max:500'],
            'cover' => ['nullable', 'image', 'max:4096'],
        ]);

        $authorNames = collect(explode(',', $data['authors']))->map(fn ($n) => trim($n))->filter()->values()->all();
        unset($data['authors'], $data['cover']);

        return ['book' => $data, 'author_names' => $authorNames];
    }

    private function syncAuthors(Book $book, array $authorNames): void
    {
        $ids = collect($authorNames)->map(function (string $name) {
            return Author::firstOrCreate(['name' => $name], ['slug' => Str::slug($name).'-'.Str::random(4)])->id;
        });

        $book->authors()->sync($ids);
    }

    private function handleCoverUpload(Request $request, Book $book): void
    {
        if (! $request->hasFile('cover')) {
            return;
        }

        $path = $request->file('cover')->store('books/uploads', 'public');
        $book->update(['cover_path' => $path, 'cover_source' => $request->file('cover')->getClientOriginalName()]);
    }
}
