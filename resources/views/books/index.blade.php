@extends('layouts.app')
@section('title', 'Browse Books — BookNest')
@section('content')
<section class="section">
    <div class="container">
        <div class="section-head"><h2>Browse Books</h2></div>

        <form method="GET" class="card card-pad" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:12px;margin-bottom:28px;align-items:end;">
            <div>
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Title, author, ISBN..." value="{{ $validated['q'] ?? '' }}">
            </div>
            <div>
                <label class="form-label">Category</label>
                <select name="category" class="form-control">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(($validated['category'] ?? null) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Availability</label>
                <select name="available" class="form-control">
                    <option value="">All</option>
                    <option value="1" @selected(($validated['available'] ?? null) == 1)>Available only</option>
                </select>
            </div>
            <div>
                <label class="form-label">Sort</label>
                <select name="sort" class="form-control">
                    <option value="latest" @selected(($validated['sort'] ?? 'latest') === 'latest')>Newest</option>
                    <option value="title" @selected(($validated['sort'] ?? '') === 'title')>Title A–Z</option>
                    @auth<option value="match" @selected(($validated['sort'] ?? '') === 'match')>Best match</option>@endauth
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        @if($books->isEmpty())
            <div class="card card-pad">No books matched your search. Try a different term or clear the filters.</div>
        @else
            <div class="book-grid">
                @foreach($books as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
            <div style="margin-top:28px;">{{ $books->links('vendor.pagination.booknest') }}</div>
        @endif
    </div>
</section>
@endsection
