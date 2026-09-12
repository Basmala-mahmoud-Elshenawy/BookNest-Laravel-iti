@extends('layouts.app')
@section('title','Manage Borrowings — BookNest')
@section('content')<section class="section"><div class="container">
<div class="section-head"><div><h1>Borrowings</h1><p class="book-author">Monitor loans, due dates and returns.</p></div></div>
<form method="GET" class="card card-pad" style="display:grid;grid-template-columns:2fr 1fr auto;gap:12px;margin-bottom:20px;"><input class="form-control" name="q" value="{{ $validated['q']??'' }}" placeholder="Search user or book..."><select class="form-control" name="status"><option value="">All statuses</option>@foreach(['active','overdue','returned'] as $s)<option value="{{$s}}" @selected(($validated['status']??'')===$s)>{{ ucfirst($s) }}</option>@endforeach</select><button class="btn btn-primary">Filter</button></form>
<div class="card"><table class="data-table"><thead><tr><th>User</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($borrowings as $b)<tr><td>{{ $b->user?->name }}<br><span class="book-author">{{ $b->user?->email }}</span></td><td>{{ $b->book?->title ?? 'Book removed' }}</td><td>{{ $b->borrowed_at?->format('Y-m-d') }}</td><td>{{ $b->due_at?->format('Y-m-d')??'—' }}</td><td>{{ $b->returned_at?->format('Y-m-d')??'—' }}</td><td><span class="badge badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td><td>@if(in_array($b->status,['active','overdue'],true))<form method="POST" action="{{ route('admin.borrowings.return',$b) }}">@csrf<button class="btn btn-secondary btn-sm">Record Return</button></form>@endif</td></tr>@empty<tr><td colspan="7">No borrowings found.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:20px;">{{ $borrowings->links('vendor.pagination.booknest') }}</div>
</div></section>@endsection
