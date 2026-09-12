<footer class="footer">
    <div class="container">
        <div style="display:flex;align-items:center;gap:12px;">
            <img src="{{ asset('images/logo.png') }}" alt="BookNest" style="height:26px;">
            <span>&copy; {{ date('Y') }} BookNest Library</span>
        </div>
        <div style="display:flex;gap:20px;">
            <a href="{{ route('books.index') }}">Books</a>
            <a href="{{ route('categories.index') }}">Categories</a>
            @auth<a href="{{ route('chatbot.show') }}">Ask BookNest AI</a>@endauth
        </div>
    </div>
</footer>
