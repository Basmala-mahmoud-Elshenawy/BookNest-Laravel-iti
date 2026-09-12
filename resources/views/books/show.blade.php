@extends('layouts.app')
@section('title', $book->title.' — BookNest')
@section('content')
<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:minmax(220px,280px) 1fr;gap:40px;align-items:start;">
            <div>
                <img src="{{ $book->coverUrl() }}" alt="{{ $book->title }}" style="width:100%;border-radius:var(--radius-md);box-shadow:var(--shadow-soft);">
            </div>

            <div>
                <h1 style="margin-bottom:8px;">{{ $book->title }}</h1>
                <p class="book-author" style="font-size:1.05rem;margin-bottom:14px;">
                    <strong>Author:</strong> {{ $book->authors->pluck('name')->implode(', ') ?: 'Author not specified' }}
                </p>

                <div class="book-meta-row" style="margin:16px 0;">
                    <span class="badge badge-{{ $book->status() }}">{{ $book->isAvailable() ? 'Available now' : 'Currently unavailable' }}</span>
                    @if($book->category)
                        <span class="badge badge-reserved">{{ $book->category->name }}</span>
                    @endif
                    @if(!is_null($matchPercentage))
                        <span class="match-pill">{{ $matchPercentage }}% match</span>
                        <span class="badge badge-reserved">
                            {{ $matchPercentage >= 70 ? 'Strong match' : ($matchPercentage >= 45 ? 'Recommended' : ($matchPercentage >= 25 ? 'Possible match' : 'Low match')) }}
                        </span>
                    @endif
                </div>

                <div class="card card-pad" style="margin:20px 0;">
                    <h3 style="margin-bottom:14px;">Book Information</h3>
                    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 28px;">
                        <div><span class="book-author">Author</span><br><strong>{{ $book->authors->pluck('name')->implode(', ') ?: 'Not specified' }}</strong></div>
                        <div><span class="book-author">Category</span><br><strong>{{ $book->category?->name ?? 'Uncategorized' }}</strong></div>
                        <div><span class="book-author">Total copies</span><br><strong>{{ $book->total_copies }}</strong></div>
                        <div><span class="book-author">Available copies</span><br><strong>{{ $book->available_copies }}</strong></div>
                        <div><span class="book-author">Currently borrowed</span><br><strong>{{ max(0, $book->total_copies - $book->available_copies) }}</strong></div>
                        <div><span class="book-author">Availability</span><br><strong>{{ $book->isAvailable() ? 'Available now' : 'Not available right now' }}</strong></div>
                        <div><span class="book-author">ISBN</span><br><strong>{{ $book->isbn ?? '—' }}</strong></div>
                        <div><span class="book-author">Language</span><br><strong>{{ strtoupper($book->language ?? '—') }}</strong></div>
                        <div><span class="book-author">Published</span><br><strong>{{ optional($book->published_at)->format('Y-m-d') ?? '—' }}</strong></div>
                    </div>
                </div>

                <div>
                    <h3 style="margin-bottom:8px;">About this book</h3>
                    <p>{{ $book->description ?? 'No description available for this title yet.' }}</p>
                </div>

                @auth
                    @if(!is_null($matchPercentage))
                        <div class="card card-pad" style="margin-top:20px;">
                            <h3 style="margin-bottom:8px;">Your Recommendation</h3>
                            <p style="margin:0 0 8px;"><strong>{{ $matchPercentage }}% match</strong> — this score is calculated from your saved profile and this book's category/content.</p>
                            <p class="book-author" style="margin:0;">{{ app(App\Services\RecommendationService::class)->explainMatch(auth()->user(), $book) }}</p>
                            <p class="book-author" style="margin:10px 0 0;font-size:.85rem;">The percentage changes after you save changes to your profile; opening the AI does not randomly change it.</p>
                        </div>
                    @endif

                    @php
                        $activeBorrowing = auth()->user()->borrowings()->where('book_id', $book->id)->currentlyActive()->first();
                        $isFavorite = auth()->user()->favoriteBooks()->whereKey($book->id)->exists();
                    @endphp
                    <div class="book-actions" style="margin-top:20px;">
                        @if($activeBorrowing)
                            <form method="POST" action="{{ route('borrowings.return', $activeBorrowing) }}">@csrf<button class="btn btn-secondary" type="submit">Return Book</button></form>
                        @elseif($book->isAvailable())
                            <form method="POST" action="{{ route('borrowings.borrow', $book) }}">@csrf<button class="btn btn-primary" type="submit">Borrow Book</button></form>
                        @else
                            <span class="book-author">Currently unavailable</span>
                        @endif
                        <form method="POST" action="{{ route('favorites.toggle', $book) }}">@csrf<button class="btn btn-outline" style="border-color:var(--color-button);color:var(--color-button);" type="submit">{{ $isFavorite ? '♥ Unfavorite' : '♡ Favorite' }}</button></form>
                    </div>
                @endauth
            </div>
        </div>

        @if($related->isNotEmpty())
            <div style="margin-top:48px;">
                <div class="section-head"><h2>Related Books</h2></div>
                <div class="book-grid">
                    @foreach($related as $r)
                        <x-book-card :book="$r" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
