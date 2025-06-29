<!DOCTYPE html>
<html>
<head>
    <title>Invoice - Order #<?php echo e($order->id); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-box { width: 100%; padding: 30px; border: 1px solid #eee; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        table td { padding: 5px; vertical-align: top; }
        table th { padding: 10px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <h2>Invoice</h2>
        <p><strong>Customer:</strong> <?php echo e($order->customer->name); ?></p>
        <p><strong>Address:</strong> <?php echo e($order->customer->address); ?></p>
        <p><strong>Order Date:</strong> <?php echo e($order->created_at->format('Y-m-d')); ?></p>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Sell Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($item->product_name); ?></td>
                        <td><?php echo e($item->quantity); ?></td>
                        <td><?php echo e(number_format($item->sell_price, 2)); ?></td>
                        <td><?php echo e(number_format($item->quantity * $item->sell_price, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <p><strong>Grand Total:</strong> <?php echo e(number_format($order->items->sum(function($item) {
            return $item->sell_price * $item->quantity;
        }), 2)); ?></p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html><?php /**PATH D:\Shahin Files + Live Projects\LIVE PROJECTS\aone-pos-laravel\aone.qoyelxyz.com\resources\views\purchasereturn\print.blade.php ENDPATH**/ ?>