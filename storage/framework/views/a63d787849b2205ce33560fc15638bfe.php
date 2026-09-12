<?php if($paginator->hasPages()): ?>
    <nav class="pagination" aria-label="Pagination">
        
        <?php if($paginator->onFirstPage()): ?>
            <span class="pagination-link disabled" aria-disabled="true" aria-label="Previous page">‹</span>
        <?php else: ?>
            <a class="pagination-link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" aria-label="Previous page">‹</a>
        <?php endif; ?>

        <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(is_string($element)): ?>
                <span class="pagination-ellipsis"><?php echo e($element); ?></span>
            <?php endif; ?>
            <?php if(is_array($element)): ?>
                <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <span class="pagination-link active" aria-current="page"><?php echo e($page); ?></span>
                    <?php else: ?>
                        <a class="pagination-link" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if($paginator->hasMorePages()): ?>
            <a class="pagination-link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" aria-label="Next page">›</a>
        <?php else: ?>
            <span class="pagination-link disabled" aria-disabled="true" aria-label="Next page">›</span>
        <?php endif; ?>
    </nav>
<?php endif; ?>
<?php /**PATH C:\Users\BASMALA\Downloads\BookNest-Final-Fixed-v2\booknest\resources\views/vendor/pagination/booknest.blade.php ENDPATH**/ ?>