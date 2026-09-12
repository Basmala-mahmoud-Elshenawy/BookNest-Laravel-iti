<footer class="footer">
    <div class="container">
        <div style="display:flex;align-items:center;gap:12px;">
            <img src="<?php echo e(asset('images/logo.png')); ?>" alt="BookNest" style="height:26px;">
            <span>&copy; <?php echo e(date('Y')); ?> BookNest Library</span>
        </div>
        <div style="display:flex;gap:20px;">
            <a href="<?php echo e(route('books.index')); ?>">Books</a>
            <a href="<?php echo e(route('categories.index')); ?>">Categories</a>
            <?php if(auth()->guard()->check()): ?><a href="<?php echo e(route('chatbot.show')); ?>">Ask BookNest AI</a><?php endif; ?>
        </div>
    </div>
</footer>
<?php /**PATH C:\Users\BASMALA\Downloads\BookNest-Final-Fixed-v2\booknest\resources\views/partials/footer.blade.php ENDPATH**/ ?>