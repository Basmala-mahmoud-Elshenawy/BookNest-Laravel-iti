@extends('layouts.app')
@section('title', 'Manage Users — BookNest')
@section('content')
<section class="section"><div class="container">
    <div class="section-head"><h2>Users</h2><a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">+ New User</a></div>
    <div class="card card-pad admin-note" style="margin-bottom:20px;">
        <strong>Account roles</strong>
        <p class="book-author" style="margin:5px 0 0;">Public Sign Up always creates a User. Use this protected page to create an Admin or User account. Both roles can browse the catalog, view details, use their profile, favorites and the BookNest AI assistant; Admins additionally get library management and statistics.</p>
    </div>
    <div class="card">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th></th></tr></thead>
            <tbody>
            @foreach($users as $u)
                <tr>
                    <td>{{ $u->name }}</td><td>{{ $u->email }}</td>
                    <td><span class="badge badge-{{ $u->role === 'admin' ? 'reserved' : 'available' }}">{{ ucfirst($u->role) }}</span></td>
                    <td style="display:flex;gap:8px;">
                        <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-outline btn-sm" style="border-color:var(--color-button);color:var(--color-button);">Edit</a>
                        <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Delete this user?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-top:20px;">{{ $users->links('vendor.pagination.booknest') }}</div>
</div></section>
@endsection
