

<?php $__env->startSection('title', __('Add Expense Head')); ?>
<?php $__env->startSection('content-header', __('Add Expense Head')); ?>
<?php $__env->startSection('content-actions'); ?>
<a href="<?php echo e(route('user.expense.create')); ?>" class="btn btn-primary"><i class="fa fa-chevron-left"></i> Add Expenses</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">
        <div class="card">
            <div class="card-body">

                <form action="<?php echo e(route('user.expense.head.store')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>


                    <div class="form-group">
                        <label for="damage_notes">Expense Head</label>
                        <textarea name="expense_head" class="form-control <?php $__errorArgs = ['expense_head'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            id="expense_head" placeholder="House Rent" required><?php echo e(old('expense_head')); ?></textarea>
                        <?php $__errorArgs = ['expense_head'];
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

                    <div class="text-right">
                        <button class="btn btn-primary" type="submit"><?php echo e('Save Expense Head'); ?></button>
                    </div>
                </form>
            </div>
        </div>


         <table class="table">
            <thead>
                <tr>
                    <th><?php echo e('ID'); ?></th>
                    <th><?php echo e('Expense Head'); ?></th>
                    <th><?php echo e('Created'); ?></th>
                    <th><?php echo e('Actions'); ?></th>

                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $expense_heads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td> <?php echo e($expense->id); ?></td>
                    <td><?php echo e($expense->expense_head); ?></td>

                    <td><?php echo e($expense->created_at); ?></td>
                    <td class="text-center"><form action="/admin/expense-head/<?php echo e($expense->id); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this?');"> <?php echo method_field('DELETE'); ?> <?php echo csrf_field(); ?> <button type="submit"><i class="fa fa-trash"></i></button></form></td>

                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>

                    <th></th>
                    <th></th>

                </tr>
            </tfoot>
        </table>

    </div>
</div>



<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="<?php echo e(asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js')); ?>"></script>
<script>

     function selectProduct($this){
           const $product = $this.value.split('=');
           document.getElementById('product_id').value = $product[0]
           document.getElementById('purchase_price').value = $product[2]
           document.getElementById('sell_price').value = $product[3]
           document.getElementById('stock_qnty').value = $product[4]

         }

    $(document).ready(function () {

    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Shahin Files + Live Projects\LIVE PROJECTS\aone-pos-laravel\aone.qoyelxyz.com\resources\views\user\expense\create-head.blade.php ENDPATH**/ ?>