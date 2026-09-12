@extends('layouts.app')
@section('title', 'Manage Categories — BookNest')
@section('content')
<section class="section"><div class="container">
    <div class="section-head"><h2>Categories</h2><a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">+ New Category</a></div>
    <div class="card">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Books</th><th></th></tr></thead>
            <tbody>
            @foreach($categories as $c)
                <tr>
                    <td>{{ $c->name }}</td><td>{{ $c->books_count }}</td>
                    <td style="display:flex;gap:8px;">
                        <a href="{{ route('admin.categories.edit', $c) }}" class="btn btn-outline btn-sm" style="border-color:var(--color-button);color:var(--color-button);">Edit</a>
                        <form action="{{ route('admin.categories.destroy', $c) }}" method="POST" onsubmit="return confirm('Delete this category? Books will become uncategorized.');">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-top:20px;">{{ $categories->links() }}</div>
</div></section>
@endsection
