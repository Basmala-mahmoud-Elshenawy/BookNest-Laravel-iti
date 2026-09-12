@extends('layouts.app')
@section('title', 'Recommended For You — BookNest')
@section('content')
<section class="section">
    <div class="container">
        <div class="section-head"><div><h1>Recommended For You</h1><p class="book-author">Sorted by highest match, based on your profile.</p></div></div>

        @if(! $hasProfile)
            <div class="card card-pad">
                Complete your <a class="view-all" href="{{ route('profile.edit') }}">profile</a> (interests, topics, skills, goals) so BookNest can compute personalized matches for you.
            </div>
        @elseif($books->isEmpty())
            <div class="card card-pad">
                No books matched your profile yet. Try adding a few more interests or preferred categories to your <a class="view-all" href="{{ route('profile.edit') }}">profile</a>, or <a class="view-all" href="{{ route('books.index') }}">browse the catalog</a>.
            </div>
        @else
            <div class="book-grid" style="margin-top:24px;">
                @foreach($books as $book)
                    <div>
                        <x-book-card :book="$book" />
                        @if(! empty($explanations[$book->id]))
                            <p class="book-author" style="margin-top:8px;">{{ $explanations[$book->id] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
