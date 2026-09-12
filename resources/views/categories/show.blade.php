@extends('layouts.app')
@section('title', $category->name.' — BookNest')
@section('content')
<section class="section">
    <div class="container">
        <div class="section-head"><h2>{{ $category->name }}</h2></div>
        <div class="book-grid">
            @forelse($books as $book)<x-book-card :book="$book" />
            @empty<p>No books in this category yet.</p>@endforelse
        </div>
        <div style="margin-top:28px;">{{ $books->links('vendor.pagination.booknest') }}</div>
    </div>
</section>
@endsection
