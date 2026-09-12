@extends('layouts.app')
@section('title', 'Dashboard — BookNest')
@section('content')
<section class="section"><div class="container">
<h1>Welcome back, {{ auth()->user()->name }}</h1><p class="book-author">Your personal library at a glance.</p>
<div class="stat-grid" style="margin-top:24px;">
<div class="card stat-card"><div class="stat-label">Current Loans</div><div class="stat-value">{{ $currentBorrowings->count() }}</div></div>
<div class="card stat-card"><div class="stat-label">Overdue</div><div class="stat-value">{{ $overdueCount }}</div></div>
<div class="card stat-card"><div class="stat-label">Favorites</div><div class="stat-value">{{ $favoritesCount }}</div></div>
</div>
<div class="section-head" style="margin-top:40px;"><h2>Current Borrowings</h2><a href="{{ route('borrowings.index') }}" class="view-all">View history <span class="link-chevron" aria-hidden="true">›</span></a></div>
<div class="card"><table class="data-table"><thead><tr><th>Book</th><th>Due</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($currentBorrowings as $b)<tr><td>{{ $b->book?->title ?? 'Book removed' }}</td><td>{{ $b->due_at?->format('Y-m-d') ?? '—' }}</td><td><span class="badge badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td><td><form method="POST" action="{{ route('borrowings.return',$b) }}">@csrf<button class="btn btn-secondary btn-sm">Return</button></form></td></tr>@empty<tr><td colspan="4">No current loans. <a class="view-all" href="{{ route('books.index') }}">Browse books <span class="link-chevron" aria-hidden="true">›</span></a></td></tr>@endforelse
</tbody></table></div>
<div class="section-head" style="margin-top:40px;"><div><h2>Recommended For You</h2><p class="book-author" style="margin:4px 0 0;">Match percentages are recalculated from your latest saved profile.</p></div><a href="{{ route('recommendations.index') }}" class="view-all">See all <span class="link-chevron" aria-hidden="true">›</span></a></div>
<div class="book-grid">@forelse($recommended as $book)<x-book-card :book="$book" />@empty<div class="card card-pad">Fill in your <a class="view-all" href="{{ route('profile.edit') }}">profile</a> to get personalized matches.</div>@endforelse</div>
<div class="section-head" style="margin-top:40px;"><h2>Recent Borrowing History</h2></div>
<div class="card"><table class="data-table"><thead><tr><th>Book</th><th>Borrowed</th><th>Returned</th><th>Status</th></tr></thead><tbody>@forelse($recentBorrowings as $b)<tr><td>{{ $b->book?->title ?? 'Book removed' }}</td><td>{{ $b->borrowed_at?->format('Y-m-d') }}</td><td>{{ $b->returned_at?->format('Y-m-d')??'—' }}</td><td><span class="badge badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td></tr>@empty<tr><td colspan="4">No history yet.</td></tr>@endforelse</tbody></table></div>
<div class="quick-links" style="margin-top:32px;"><a class="btn btn-secondary" href="{{ route('favorites.index') }}">♥ My Favorites</a><a class="btn btn-secondary" href="{{ route('profile.edit') }}">Profile</a><a class="btn btn-primary" href="{{ route('chatbot.show') }}">Open Chatbot</a></div>
</div></section>
@endsection
