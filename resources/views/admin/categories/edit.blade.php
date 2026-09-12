@extends('layouts.app')
@section('title', 'Edit Category — BookNest')
@section('content')
<section class="section"><div class="container" style="max-width:520px;">
    <h1>Edit Category</h1>
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="card card-pad" style="margin-top:20px;">
        @csrf @method('PUT')
        @include('admin.categories._form')
        <button class="btn btn-primary">Save Changes</button>
    </form>
</div></section>
@endsection
