@extends('layouts.app')
@section('title', 'Categories — BookNest')
@section('content')
<section class="section">
    <div class="container">
        <div class="section-head"><h2>Categories</h2></div>
        <div class="feature-grid">
            @foreach($categories as $category)
                <a href="{{ route('categories.show', $category) }}" class="card card-pad">
                    <strong>{{ $category->name }}</strong>
                    <p class="book-author" style="margin:6px 0 0;">{{ $category->books_count }} books</p>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection
