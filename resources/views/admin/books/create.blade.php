@extends('layouts.app')
@section('title', 'New Book — BookNest')
@section('content')
<section class="section"><div class="container" style="max-width:640px;">
    <h1>New Book</h1>
    <form method="POST" action="{{ route('admin.books.store') }}" enctype="multipart/form-data" class="card card-pad" style="margin-top:20px;">
        @csrf
        @include('admin.books._form')
        <button class="btn btn-primary">Create Book</button>
    </form>
</div></section>
@endsection
