@extends('layouts.app')
@section('title', 'My Favorites — BookNest')
@section('content')
<section class="section"><div class="container">
    <div class="section-head"><div><h1>My Favorites</h1><p class="book-author">Books you saved for later.</p></div></div>
    @if($favorites->isEmpty())
        <div class="card card-pad">You have no favorites yet. <a class="view-all" href="{{ route('books.index') }}">Browse books <span class="link-chevron" aria-hidden="true">›</span></a></div>
    @else
        <div class="book-grid">@foreach($favorites as $book)<x-book-card :book="$book" />@endforeach</div>
        <div style="margin-top:28px;">{{ $favorites->links() }}</div>
    @endif
</div></section>
@endsection
