@extends('layouts.app')
@section('title', 'Admin Dashboard — BookNest')
@section('content')
<section class="section">
    <div class="container">
        <h1>Admin Dashboard</h1>
        <div class="stat-grid" style="margin-top:24px;">
            <div class="card stat-card"><div class="stat-label">Total Book Copies</div><div class="stat-value">{{ $stats['total_books'] }}</div></div>
            <div class="card stat-card"><div class="stat-label">Available Copies</div><div class="stat-value">{{ $stats['available_books'] }}</div></div>
            <div class="card stat-card"><div class="stat-label">Registered Users</div><div class="stat-value">{{ $stats['total_users'] }}</div></div>
            <div class="card stat-card"><div class="stat-label">Categories</div><div class="stat-value">{{ $stats['total_categories'] }}</div></div>
            <div class="card stat-card"><div class="stat-label">Active Loans</div><div class="stat-value">{{ $stats['active_borrowings'] }}</div></div>
            <div class="card stat-card"><div class="stat-label">Overdue Loans</div><div class="stat-value">{{ $stats['overdue_borrowings'] }}</div></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:32px;">
            <div class="card card-pad">
                <h3 style="margin-top:0;">Books per Category</h3>
                <table class="data-table">
                    <tbody>
                    @foreach($perCategory as $c)
                        <tr><td>{{ $c->name }}{{ $topCategory && $topCategory->id === $c->id ? ' 👑' : '' }}</td><td>{{ $c->books_count }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card card-pad">
                <h3 style="margin-top:0;">Low Availability</h3>
                <table class="data-table">
                    <tbody>
                    @forelse($lowAvailability as $b)
                        <tr><td>{{ $b->title }}</td><td>{{ $b->available_copies }}/{{ $b->total_copies }}</td></tr>
                    @empty
                        <tr><td class="book-author">Nothing running low.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display:flex;gap:14px;margin-top:32px;">
            <a href="{{ route('admin.books.index') }}" class="btn btn-primary">Manage Books</a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Manage Categories</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Manage Users</a>
            <a href="{{ route('admin.authors.index') }}" class="btn btn-secondary">Manage Authors</a>
            <a href="{{ route('admin.borrowings.index') }}" class="btn btn-secondary">Borrowings</a>
            <a href="{{ route('chatbot.show') }}" class="btn btn-outline" style="border-color:var(--color-button);color:var(--color-button);">Ask AI</a>
        </div>
    </div>
</section>
@endsection
