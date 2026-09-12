@extends('layouts.app')
@section('title', 'Edit User — BookNest')
@section('content')
<section class="section"><div class="container" style="max-width:520px;">
    <h1>Edit User</h1>
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card card-pad" style="margin-top:20px;">
        @csrf @method('PUT')
        @include('admin.users._form')
        <button class="btn btn-primary">Save Changes</button>
    </form>
</div></section>
@endsection
