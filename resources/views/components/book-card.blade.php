@props(['book'])
@php
    $user = auth()->user();
    $status = $book->status();
    $activeBorrowing = $user?->borrowings()->where('book_id', $book->id)->currentlyActive()->first();
    $isFavorite = $user ? $user->favoriteBooks()->whereKey($book->id)->exists() : false;
@endphp
<div class="card book-card">
    <a href="{{ route('books.show', $book) }}" class="book-cover-link">
        <img src="{{ $book->coverUrl() }}" alt="{{ $book->title }}" class="book-cover" loading="lazy">
    </a>
    <div class="book-card-body">
        <a href="{{ route('books.show', $book) }}"><p class="book-title">{{ $book->title }}</p></a>
        <p class="book-author">{{ $book->authors->pluck('name')->implode(', ') ?: ($book->category->name ?? 'Uncategorized') }}</p>
        <div class="book-meta-row">
            <span class="badge badge-{{ $status }}">{{ ucfirst($status) }}</span>
            @if(isset($book->match_percentage))
                <span class="match-pill">{{ $book->match_percentage }}% match</span>
                <span class="badge badge-reserved">{{ $book->match_label ?? ($book->match_percentage >= 45 ? 'Recommended' : 'Explore') }}</span>
            @endif
        </div>
        <p class="availability-text">{{ $book->available_copies }} of {{ $book->total_copies }} copies available</p>

        <div class="book-actions book-actions-stacked">
            @auth
                @if($activeBorrowing)
                    <form method="POST" action="{{ route('borrowings.return', $activeBorrowing) }}" class="book-primary-action">
                        @csrf
                        <button class="btn btn-secondary btn-sm btn-full" type="submit">Return Book</button>
                    </form>
                @elseif($book->isAvailable())
                    <form method="POST" action="{{ route('borrowings.borrow', $book) }}" class="book-primary-action">
                        @csrf
                        <button class="btn btn-primary btn-sm btn-full" type="submit">Borrow Book</button>
                    </form>
                @else
                    <button class="btn btn-secondary btn-sm btn-full" type="button" disabled>Borrow Book</button>
                @endif

                <form method="POST" action="{{ route('favorites.toggle', $book) }}" class="favorite-action">
                    @csrf
                    <button class="btn btn-outline btn-sm favorite-btn" type="submit" title="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}" aria-label="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}">
                        <span aria-hidden="true">{{ $isFavorite ? '♥' : '♡' }}</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm btn-full">Login to Borrow</a>
            @endauth
        </div>
    </div>
</div>
