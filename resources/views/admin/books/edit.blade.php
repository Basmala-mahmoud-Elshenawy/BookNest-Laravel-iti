@extends('layouts.app')
@section('title', 'Edit Book — BookNest')
@section('content')
<section class="section"><div class="container" style="max-width:640px;">
    <h1>Edit Book</h1>
    <form method="POST" action="{{ route('admin.books.update', $book) }}" enctype="multipart/form-data" class="card card-pad" style="margin-top:20px;">
        @csrf @method('PUT')
        @include('admin.books._form')
        <button class="btn btn-primary">Save Changes</button>
    </form>
</div></section>
@endsection
