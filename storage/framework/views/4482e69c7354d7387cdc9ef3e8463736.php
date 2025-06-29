

<?php $__env->startSection('title', __('Add Expense')); ?>
<?php $__env->startSection('content-header', __('Add Expense')); ?>

<?php $__env->startSection('content-actions'); ?>
<a href="<?php echo e(route('expense.index')); ?>" class="btn btn-primary"><i class="fa fa-chevron-left"></i> Expenses</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">
        <div class="card">
            <div class="card-body">

                <form action="<?php echo e(route('expense.store')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>

                    <div class="row">
                        <div class="col-sm-9">
                            <div class="form-group">
                                <label for="expense_head"><?php echo e('Product'); ?></label>
                                <select class="form-control" name="expense_head" id="expense_head" required>
                                    <option>:: Select expense head ::</option>
                                    <?php $__currentLoopData = $expense_heads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value='<?php echo e($exp->expense_head); ?>'><?php echo e($exp->expense_head); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    
                                </select>
                                

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
                        </div>
                        <div class="col-sm-3">
                            <label for="damage_notes">Expense Head</label><br>

                            <a href="<?php echo e(route('expense.head.create')); ?>" role="button" class="btn btn-success">+ Expense Item</a>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="damage_notes">Expense Description</label>
                        <textarea name="expense_description" class="form-control <?php $__errorArgs = ['expense_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            id="expense_description" placeholder="Expense Description" required><?php echo e(old('expense_description')); ?></textarea>
                        <?php $__errorArgs = ['expense_description'];
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


                     

                    

                    <div class="form-group">
                        <label for="expense_amount">Expense Amount</label>
                        <input type="text" name="expense_amount" class="form-control <?php $__errorArgs = ['expense_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            id="expense_amount" placeholder="Expense Amount" value="<?php echo e(old('expense_amount', '')); ?>" required>
                        <?php $__errorArgs = ['expense_amount'];
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
                        <button class="btn btn-primary" type="submit"><?php echo e('Save Expense'); ?></button>
                    </div>
                </form>
            </div>
        </div>
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
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Shahin Files + Live Projects\LIVE PROJECTS\aone-pos-laravel\aone.qoyelxyz.com\resources\views\expense\create.blade.php ENDPATH**/ ?>