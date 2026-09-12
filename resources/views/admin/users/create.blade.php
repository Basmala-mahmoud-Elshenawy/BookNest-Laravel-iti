@extends('layouts.app')
@section('title', 'New User — BookNest')
@section('content')
<section class="section"><div class="container" style="max-width:520px;">
    <h1>New User</h1>
    <form method="POST" action="{{ route('admin.users.store') }}" class="card card-pad" style="margin-top:20px;">
        @csrf
        @include('admin.users._form')
        <button class="btn btn-primary">Create User</button>
    </form>
</div></section>
@endsection
