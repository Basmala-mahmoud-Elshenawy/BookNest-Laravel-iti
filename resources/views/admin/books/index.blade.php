@extends('layouts.app')
@section('title', 'Manage Books — BookNest')
@section('content')
<section class="section"><div class="container">
    <div class="section-head"><h2>Books</h2><a href="{{ route('admin.books.create') }}" class="btn btn-primary btn-sm">+ New Book</a></div>

    <form method="GET" class="card card-pad" style="display:flex;gap:12px;margin-bottom:20px;">
        <input type="text" name="q" class="form-control" placeholder="Search title, author, ISBN..." value="{{ request('q') }}">
        <button class="btn btn-primary">Search</button>
    </form>

    <div class="card">
        <table class="data-table">
            <thead><tr><th></th><th>Title</th><th>Category</th><th>Copies</th><th></th></tr></thead>
            <tbody>
            @foreach($books as $b)
                <tr>
                    <td><img src="{{ $b->coverUrl() }}" alt="{{ $b->title }}" style="width:36px;height:52px;object-fit:cover;border-radius:4px;"></td>
                    <td>{{ $b->title }}</td>
                    <td>{{ $b->category->name ?? '—' }}</td>
                    <td>{{ $b->available_copies }}/{{ $b->total_copies }}</td>
                    <td style="display:flex;gap:8px;">
                        <a href="{{ route('admin.books.edit', $b) }}" class="btn btn-outline btn-sm" style="border-color:var(--color-button);color:var(--color-button);">Edit</a>
                        <form action="{{ route('admin.books.destroy', $b) }}" method="POST" onsubmit="return confirm('Delete this book?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-top:20px;">{{ $books->links('vendor.pagination.booknest') }}</div>
</div></section>
@endsection
