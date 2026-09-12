<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['book']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['book']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $user = auth()->user();
    $status = $book->status();
    $activeBorrowing = $user?->borrowings()->where('book_id', $book->id)->currentlyActive()->first();
    $isFavorite = $user ? $user->favoriteBooks()->whereKey($book->id)->exists() : false;
?>
<div class="card book-card">
    <a href="<?php echo e(route('books.show', $book)); ?>" class="book-cover-link">
        <img src="<?php echo e($book->coverUrl()); ?>" alt="<?php echo e($book->title); ?>" class="book-cover" loading="lazy">
    </a>
    <div class="book-card-body">
        <a href="<?php echo e(route('books.show', $book)); ?>"><p class="book-title"><?php echo e($book->title); ?></p></a>
        <p class="book-author"><?php echo e($book->authors->pluck('name')->implode(', ') ?: ($book->category->name ?? 'Uncategorized')); ?></p>
        <div class="book-meta-row">
            <span class="badge badge-<?php echo e($status); ?>"><?php echo e(ucfirst($status)); ?></span>
            <?php if(isset($book->match_percentage)): ?>
                <span class="match-pill"><?php echo e($book->match_percentage); ?>% match</span>
                <span class="badge badge-reserved"><?php echo e($book->match_label ?? ($book->match_percentage >= 45 ? 'Recommended' : 'Explore')); ?></span>
            <?php endif; ?>
        </div>
        <p class="availability-text"><?php echo e($book->available_copies); ?> of <?php echo e($book->total_copies); ?> copies available</p>

        <div class="book-actions book-actions-stacked">
            <?php if(auth()->guard()->check()): ?>
                <?php if($activeBorrowing): ?>
                    <form method="POST" action="<?php echo e(route('borrowings.return', $activeBorrowing)); ?>" class="book-primary-action">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-secondary btn-sm btn-full" type="submit">Return Book</button>
                    </form>
                <?php elseif($book->isAvailable()): ?>
                    <form method="POST" action="<?php echo e(route('borrowings.borrow', $book)); ?>" class="book-primary-action">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-primary btn-sm btn-full" type="submit">Borrow Book</button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-secondary btn-sm btn-full" type="button" disabled>Borrow Book</button>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('favorites.toggle', $book)); ?>" class="favorite-action">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-outline btn-sm favorite-btn" type="submit" title="<?php echo e($isFavorite ? 'Remove from favorites' : 'Add to favorites'); ?>" aria-label="<?php echo e($isFavorite ? 'Remove from favorites' : 'Add to favorites'); ?>">
                        <span aria-hidden="true"><?php echo e($isFavorite ? '♥' : '♡'); ?></span>
                    </button>
                </form>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-sm btn-full">Login to Borrow</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\BASMALA\Downloads\BookNest-Final-Fixed-v2\booknest\resources\views/components/book-card.blade.php ENDPATH**/ ?>