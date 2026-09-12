<?php $__env->startSection('title', 'Browse Books — BookNest'); ?>
<?php $__env->startSection('content'); ?>
<section class="section">
    <div class="container">
        <div class="section-head"><h2>Browse Books</h2></div>

        <form method="GET" class="card card-pad" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:12px;margin-bottom:28px;align-items:end;">
            <div>
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Title, author, ISBN..." value="<?php echo e($validated['q'] ?? ''); ?>">
            </div>
            <div>
                <label class="form-label">Category</label>
                <select name="category" class="form-control">
                    <option value="">All categories</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php if(($validated['category'] ?? null) == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="form-label">Availability</label>
                <select name="available" class="form-control">
                    <option value="">All</option>
                    <option value="1" <?php if(($validated['available'] ?? null) == 1): echo 'selected'; endif; ?>>Available only</option>
                </select>
            </div>
            <div>
                <label class="form-label">Sort</label>
                <select name="sort" class="form-control">
                    <option value="latest" <?php if(($validated['sort'] ?? 'latest') === 'latest'): echo 'selected'; endif; ?>>Newest</option>
                    <option value="title" <?php if(($validated['sort'] ?? '') === 'title'): echo 'selected'; endif; ?>>Title A–Z</option>
                    <?php if(auth()->guard()->check()): ?><option value="match" <?php if(($validated['sort'] ?? '') === 'match'): echo 'selected'; endif; ?>>Best match</option><?php endif; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <?php if($books->isEmpty()): ?>
            <div class="card card-pad">No books matched your search. Try a different term or clear the filters.</div>
        <?php else: ?>
            <div class="book-grid">
                <?php $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginalb2ad6158e46176d6a9dc77a41399ede1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb2ad6158e46176d6a9dc77a41399ede1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.book-card','data' => ['book' => $book]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('book-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['book' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($book)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb2ad6158e46176d6a9dc77a41399ede1)): ?>
<?php $attributes = $__attributesOriginalb2ad6158e46176d6a9dc77a41399ede1; ?>
<?php unset($__attributesOriginalb2ad6158e46176d6a9dc77a41399ede1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb2ad6158e46176d6a9dc77a41399ede1)): ?>
<?php $component = $__componentOriginalb2ad6158e46176d6a9dc77a41399ede1; ?>
<?php unset($__componentOriginalb2ad6158e46176d6a9dc77a41399ede1); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div style="margin-top:28px;"><?php echo e($books->links('vendor.pagination.booknest')); ?></div>
        <?php endif; ?>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\BASMALA\Downloads\BookNest-Final-Fixed-v2\booknest\resources\views/books/index.blade.php ENDPATH**/ ?>