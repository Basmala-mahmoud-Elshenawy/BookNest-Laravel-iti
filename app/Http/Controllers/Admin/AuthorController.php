<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function index(Request $request): View
    {
        $authors = Author::withCount('books')
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.authors.index', compact('authors'));
    }

    public function create(): View { return view('admin.authors.create'); }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:authors,name'],
            'bio' => ['nullable', 'string', 'max:5000'],
        ]);
        Author::create([...$validated, 'slug' => $this->uniqueSlug($validated['name'])]);
        return redirect()->route('admin.authors.index')->with('status', 'Author created.');
    }

    public function edit(Author $author): View { return view('admin.authors.edit', compact('author')); }

    public function update(Request $request, Author $author): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:authors,name,'.$author->id],
            'bio' => ['nullable', 'string', 'max:5000'],
        ]);
        $slug = $author->name === $validated['name'] ? $author->slug : $this->uniqueSlug($validated['name'], $author->id);
        $author->update([...$validated, 'slug' => $slug]);
        return redirect()->route('admin.authors.index')->with('status', 'Author updated.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        if ($author->books()->exists()) {
            return back()->withErrors(['author' => 'This author is linked to books. Remove the author from those books before deleting them.']);
        }
        $author->delete();
        return redirect()->route('admin.authors.index')->with('status', 'Author deleted.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'author';
        $slug = $base;
        $i = 2;
        while (Author::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }
}
