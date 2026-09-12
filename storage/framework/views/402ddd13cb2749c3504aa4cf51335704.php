<header class="navbar">
    <div class="container">
        <a href="<?php echo e(route('home')); ?>" class="navbar-logo" aria-label="BookNest home">
            <img src="<?php echo e(asset('images/logo.png')); ?>" alt="BookNest logo">
        </a>

        <nav class="navbar-links" aria-label="Main navigation">
            <a href="<?php echo e(route('home')); ?>">Home</a>
            <a href="<?php echo e(route('books.index')); ?>">Books</a>
            <a href="<?php echo e(route('categories.index')); ?>">Categories</a>
            <a href="<?php echo e(route('home')); ?>#about">About</a>
        </nav>

        <div class="navbar-actions">
            <form action="<?php echo e(route('books.index')); ?>" method="GET" class="navbar-search">
                <span aria-hidden="true">⌕</span>
                <input type="text" name="q" placeholder="Search books..." value="<?php echo e(request('q')); ?>" aria-label="Search books">
            </form>

            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline btn-sm">Dashboard</a>
                <a href="<?php echo e(route('favorites.index')); ?>" class="btn btn-outline btn-sm">Favorites</a>
                <a href="<?php echo e(route('chatbot.show')); ?>" class="btn btn-outline btn-sm" title="BookNest AI Assistant" aria-label="BookNest AI Assistant">AI</a>
                <a href="<?php echo e(route('profile.edit')); ?>" class="nav-icon-link" title="Profile" aria-label="Profile">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"></circle><path d="M5 20c.8-3.5 3.1-5.2 7-5.2s6.2 1.7 7 5.2"></path></svg>
                </a>
                <?php if(auth()->user()->isAdmin()): ?>
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-outline btn-sm">Admin</a>
                <?php endif; ?>
                <form action="<?php echo e(route('logout')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-cream btn-sm">Logout</button>
                </form>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="btn btn-outline btn-sm">Login</a>
                <a href="<?php echo e(route('register')); ?>" class="btn btn-cream btn-sm">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php /**PATH C:\Users\BASMALA\Downloads\BookNest-Final-Fixed-v2\booknest\resources\views/partials/navbar.blade.php ENDPATH**/ ?>