<header class="navbar">
    <div class="container">
        <a href="{{ route('home') }}" class="navbar-logo" aria-label="BookNest home">
            <img src="{{ asset('images/logo.png') }}" alt="BookNest logo">
        </a>

        <nav class="navbar-links" aria-label="Main navigation">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('books.index') }}">Books</a>
            <a href="{{ route('categories.index') }}">Categories</a>
            <a href="{{ route('home') }}#about">About</a>
        </nav>

        <div class="navbar-actions">
            <form action="{{ route('books.index') }}" method="GET" class="navbar-search">
                <span aria-hidden="true">⌕</span>
                <input type="text" name="q" placeholder="Search books..." value="{{ request('q') }}" aria-label="Search books">
            </form>

            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-outline btn-sm">Dashboard</a>
                <a href="{{ route('favorites.index') }}" class="btn btn-outline btn-sm">Favorites</a>
                <a href="{{ route('chatbot.show') }}" class="btn btn-outline btn-sm" title="BookNest AI Assistant" aria-label="BookNest AI Assistant">AI</a>
                <a href="{{ route('profile.edit') }}" class="nav-icon-link" title="Profile" aria-label="Profile">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"></circle><path d="M5 20c.8-3.5 3.1-5.2 7-5.2s6.2 1.7 7 5.2"></path></svg>
                </a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm">Admin</a>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-cream btn-sm">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Login</a>
                <a href="{{ route('register') }}" class="btn btn-cream btn-sm">Sign Up</a>
            @endauth
        </div>
    </div>
</header>
