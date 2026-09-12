@extends('layouts.app')
@section('title', 'My Borrowings — BookNest')
@section('content')
<section class="section"><div class="container">
    <div class="section-head"><div><h1>My Borrowings</h1><p class="book-author">Current loans and your borrowing history.</p></div></div>
    <div class="card"><table class="data-table"><thead><tr><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($borrowings as $b)
        <tr><td>{{ $b->book?->title ?? 'Book removed' }}</td><td>{{ $b->borrowed_at?->format('Y-m-d') }}</td><td>{{ $b->due_at?->format('Y-m-d') ?? '—' }}</td><td>{{ $b->returned_at?->format('Y-m-d') ?? '—' }}</td><td><span class="badge badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td><td>@if(in_array($b->status, ['active','overdue'], true))<form method="POST" action="{{ route('borrowings.return', $b) }}">@csrf<button class="btn btn-secondary btn-sm">Return</button></form>@endif</td></tr>
    @empty <tr><td colspan="6">No borrowing history yet.</td></tr>@endforelse
    </tbody></table></div>
    <div style="margin-top:20px;">{{ $borrowings->links('vendor.pagination.booknest') }}</div>
</div></section>
@endsection
