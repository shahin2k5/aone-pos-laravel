<?php if(Session::has('error')): ?>
<div class="alert alert-danger">
    <?php echo e(Session::get('error')); ?>

</div>
<?php endif; ?><?php /**PATH /home/senpai/Work/laravel/aone-pos-laravel/resources/views/layouts/partials/alert/error.blade.php ENDPATH**/ ?>