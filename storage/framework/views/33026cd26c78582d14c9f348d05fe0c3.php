<?php $__env->startSection('title', __('Pay Supplier')); ?>
<?php $__env->startSection('content-header', __('Pay Supplier')); ?>
<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <form action="<?php echo e(route('admin.suppliers.pay.submit', $supplier)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="amount"><?php echo e(__('Amount')); ?></label>
                <input type="number" name="amount" class="form-control <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="amount" min="1" required>
                <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="invalid-feedback" role="alert">
                    <strong><?php echo e($message); ?></strong>
                </span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <button class="btn btn-success" type="submit"><?php echo e(__('Pay')); ?></button>
            <a href="<?php echo e(route('admin.suppliers.index')); ?>" class="btn btn-secondary"><?php echo e(__('Cancel')); ?></a>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/senpai/Work/laravel/aone-pos-laravel/resources/views/admin/suppliers/pay.blade.php ENDPATH**/ ?>