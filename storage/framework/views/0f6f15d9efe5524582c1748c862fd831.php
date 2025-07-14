<?php $__env->startSection('content-header', 'Damaged Products Details'); ?>
<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('user.expense.index')); ?>" class="btn btn-secondary mb-3">&larr; Back to Report Summary</a>

<div class="container-fluid">
    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" action="<?php echo e(route('user.expense.damage-details')); ?>" class="form-inline">
                <div class="form-group mr-3">
                    <label for="start_date" class="mr-2">From:</label>
                    <input type="date" class="form-control" id="start_date" name="start_date"
                           value="<?php echo e(request('start_date', \Carbon\Carbon::parse($startDate)->format('Y-m-d'))); ?>">
                </div>
                <div class="form-group mr-3">
                    <label for="end_date" class="mr-2">To:</label>
                    <input type="date" class="form-control" id="end_date" name="end_date"
                           value="<?php echo e(request('end_date', \Carbon\Carbon::parse($endDate)->format('Y-m-d'))); ?>">
                </div>
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="<?php echo e(route('user.expense.damage-details')); ?>" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        <div class="col-md-4 text-right">
            <div class="text-muted">
                <small>Period: <?php echo e(\Carbon\Carbon::parse($startDate)->format('M d, Y')); ?> - <?php echo e(\Carbon\Carbon::parse($endDate)->format('M d, Y')); ?></small>
            </div>
        </div>
    </div>

    <!-- Damaged Products Table -->
    <div class="card">
        <div class="card-header font-weight-bold">Damaged Products</div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Purchase Price</th>
                        <th>Total Loss</th>
                        <th>Notes</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $damages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $damage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($damage->id); ?></td>
                            <td><?php echo e($damage->product->name ?? 'N/A'); ?></td>
                            <td><?php echo e(number_format($damage->qnty, 0)); ?></td>
                            <td><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($damage->purchase_price, 2)); ?></td>
                            <td><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($damage->purchase_price * $damage->qnty, 2)); ?></td>
                            <td><?php echo e($damage->notes); ?></td>
                            <td><?php echo e($damage->created_at); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center">No damaged products found for this period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-center">
            <?php echo e($damages->appends(request()->query())->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('user.layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/senpai/Work/laravel/aone-pos-laravel/resources/views/user/expense/damage-details.blade.php ENDPATH**/ ?>