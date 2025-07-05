<?php $__env->startSection('content-header', 'Sales Details Report'); ?>
<?php $__env->startSection('content'); ?>

<a href="<?php echo e(route('expense.index')); ?>" class="btn btn-secondary mb-3">&larr; Back</a>

<div class="container-fluid">
    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" action="<?php echo e(route('expense.sales-details')); ?>" class="form-inline">
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
                <a href="<?php echo e(route('expense.sales-details')); ?>" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        <div class="col-md-4 text-right">
            <div class="text-muted">
                <small>Period: <?php echo e(\Carbon\Carbon::parse($startDate)->format('M d, Y')); ?> - <?php echo e(\Carbon\Carbon::parse($endDate)->format('M d, Y')); ?></small>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="d-flex flex-wrap gap-3 mb-4">
        <div class="card bg-primary text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h5 class="summary-card-title">Total Sales</h5>
                <div class="summary-card-value"><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($totalSales, 2)); ?></div>
            </div>
        </div>
        <div class="card bg-danger text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h5 class="summary-card-title">Returns</h5>
                <div class="summary-card-value"><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($returnsTotal, 2)); ?></div>
            </div>
        </div>
        <div class="card bg-success text-white summary-card" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h5 class="summary-card-title">Net Sales</h5>
                <div class="summary-card-value"><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($netSales, 2)); ?></div>
            </div>
        </div>
    </div>
    <style>
        .summary-card-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .summary-card-value {
            font-size: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .card-body.text-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
    </style>

    <!-- Sales Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Sales Details</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>#<?php echo e($order->id); ?></td>
                            <td><?php echo e($order->created_at->format('M d, Y H:i')); ?></td>
                            <td><?php echo e($order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'Walk-in Customer'); ?></td>
                            <td>
                                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="small">
                                        <?php echo e($item->product->name); ?> x <?php echo e($item->quantity); ?> = <?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($item->sell_price, 2)); ?>

                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </td>
                            <td><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($order->total(), 2)); ?></td>
                            <td>
                                <?php if($order->receivedAmount() >= $order->total()): ?>
                                    <span class="badge badge-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Partial</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center">No sales found for the selected period.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                <?php echo e($orders->appends(request()->query())->links()); ?>

            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/senpai/Work/laravel/aone-pos-laravel/resources/views/expense/sales-details.blade.php ENDPATH**/ ?>