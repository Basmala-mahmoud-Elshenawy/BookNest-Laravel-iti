@extends('layouts.app')
@section('title', 'New Category — BookNest')
@section('content')
<section class="section"><div class="container" style="max-width:520px;">
    <h1>New Category</h1>
    <form method="POST" action="{{ route('admin.categories.store') }}" class="card card-pad" style="margin-top:20px;">
        @csrf
        @include('admin.categories._form')
        <button class="btn btn-primary">Create Category</button>
    </form>
</div></section>
@endsection
