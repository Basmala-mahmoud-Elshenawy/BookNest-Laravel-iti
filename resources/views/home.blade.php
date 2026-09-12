@extends('layouts.app')
@section('title', 'BookNest — Find Your Next Great Book')

@section('content')
<section class="hero">
    <div class="container hero-grid">
        <div>
            <div class="hero-eyebrow">Welcome to our library</div>
            <h1 class="hero-heading">Find Your Next<br>Great Book</h1>
            <p class="hero-desc">Explore thousands of books, discover new interests, and build your own reading journey.</p>
            <div class="hero-actions">
                <a href="{{ route('books.index') }}" class="btn btn-primary">Browse Books</a>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
        <div class="hero-image-wrap">
            <img src="{{ asset('images/hero.jpg') }}" alt="Library shelves">
        </div>
    </div>
</section>

<section class="section" id="about">
    <div class="container">
        <div class="feature-grid">
            <div class="feature-item">
                <div class="feature-icon">📚</div>
                <h3>Wide Selection</h3>
                <p>Access a vast collection of books in various genres.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">⏱️</div>
                <h3>Easy Borrowing</h3>
                <p>Simple and fast book borrowing process.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🔒</div>
                <h3>Secure & Reliable</h3>
                <p>Your information is always safe with us.</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🤝</div>
                <h3>A Better Community</h3>
                <p>Join a community of book lovers.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Latest Books</h2>
            <a href="{{ route('books.index') }}" class="view-all">View All <span class="link-chevron" aria-hidden="true">›</span></a>
        </div>
        <div class="book-grid">
            @forelse($latestBooks as $book)
                <x-book-card :book="$book" />
            @empty
                <p style="color:var(--color-text-muted);">No books yet — run the database seeder to populate the catalog.</p>
            @endforelse
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head"><h2>Browse Categories</h2></div>
        <div class="feature-grid">
            @foreach($categories as $category)
                <a href="{{ route('categories.show', $category) }}" class="card card-pad">
                    <strong>{{ $category->name }}</strong>
                    <p class="book-author" style="margin-top:6px;">{{ $category->books_count }} books</p>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection
