@extends('layouts.app')
@section('title', 'Manage Authors — BookNest')
@section('content')
<section class="section"><div class="container">
    <div class="section-head"><h2>Authors</h2><a href="{{ route('admin.authors.create') }}" class="btn btn-primary btn-sm">+ New Author</a></div>
    <form method="GET" class="card card-pad" style="display:flex;gap:12px;margin-bottom:20px;"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search authors..."><button class="btn btn-primary">Search</button></form>
    <div class="card"><table class="data-table"><thead><tr><th>Name</th><th>Slug</th><th>Books</th><th></th></tr></thead><tbody>
    @forelse($authors as $author)<tr><td>{{ $author->name }}</td><td>{{ $author->slug }}</td><td>{{ $author->books_count }}</td><td class="table-actions"><a class="btn btn-outline btn-sm" style="border-color:var(--color-button);color:var(--color-button);" href="{{ route('admin.authors.edit',$author) }}">Edit</a><form method="POST" action="{{ route('admin.authors.destroy',$author) }}" onsubmit="return confirm('Delete this author?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Delete</button></form></td></tr>
    @empty<tr><td colspan="4">No authors found.</td></tr>@endforelse
    </tbody></table></div><div style="margin-top:20px;">{{ $authors->links('vendor.pagination.booknest') }}</div>
</div></section>
@endsection
