
<?php $__env->startSection('content-header', 'Admin Dashboard - '. now()); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid">
   <div class="row">
 
       <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-danger">
            <div class="inner">
               <h3><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format(($payment_customer_today-$payment_supplier_today), 2)); ?></h3>
               <p><?php echo e(__('Today Cash')); ?></p>
            </div>
            <div class="icon">
               <i class="ion ion-pie-graph"></i>
            </div>
            <a href="<?php echo e(route('sales.index')); ?>" class="small-box-footer"><?php echo e(__('common.More_info')); ?> <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>

      <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-info">
            <div class="inner">
               <h3><?php echo e($today_sales); ?></h3>
               <p><?php echo e(__('Today Sales')); ?></p>
            </div>
            <div class="icon">
               <i class="ion ion-bag"></i>
            </div>
            <a href="<?php echo e(route('sales.index')); ?>" class="small-box-footer"><?php echo e(__('common.More_info')); ?> <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-success">
            <div class="inner">
               <h3><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($today_purchase, 2)); ?></h3>
               <p><?php echo e(__('Today Purchase')); ?></p>
            </div>
            <div class="icon">
               <i class="ion ion-stats-bars"></i>
            </div>
            <a href="<?php echo e(route('sales.index')); ?>" class="small-box-footer"><?php echo e(__('common.More_info')); ?> <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>
      <!-- ./col -->
     
      <!-- ./col -->
      <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-warning">
            <div class="inner">
               <h3><?php echo e($today_profit); ?></h3>
               <p><?php echo e(__('Today Profit')); ?></p>
            </div>
            <div class="icon">
               <i class="ion ion-person-add"></i>
            </div>
            <a href="<?php echo e(route('customers.index')); ?>" class="small-box-footer"><?php echo e(__('common.More_info')); ?> <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>
      <!-- ./col -->
   </div>
</div>
<div class="container-fluid">
   <div class="row">

        <div class="col-6 my-4">
         <h3>Best Selling Products</h3>
         <section class="content">
            <div class="card product-list">
               <div class="card-body">
                  <table class="table">
                     <thead>
                        <tr>
                           <th>ID</th>
                           <th>Name</th>
                           <th>Image</th>
                 
                           <th>Price</th>
                           <th>Quantity</th>
                           
                           <th>Updated At</th>
                           <!-- <th>Actions</th> -->
                        </tr>
                     </thead>
                     <tbody>
                        <?php $__currentLoopData = $best_selling_products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                           <td><?php echo e($product->id); ?></td>
                           <td><?php echo e($product->name); ?></td>
                           <td><img class="product-img" src="<?php echo e(Storage::url($product->image)); ?>" alt=""></td>
                  
                           <td><?php echo e($product->sell_price); ?></td>
                           <td><?php echo e($product->quantity); ?></td>
                           
                           <td><?php echo e($product->updated_at); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </section>
      </div>

      <div class="col-6 my-4">
         <h3>Low Stock Product</h3>
         <section class="content">
            <div class="card product-list">
               <div class="card-body">
                  <table class="table">
                     <thead>
                        <tr>
                           <th>ID</th>
                           <th>Name</th>
                           <th>Image</th>
                           <th>Barcode</th>
                           <th>Price</th>
                           <th>Quantity</th>
                           <th>Status</th>
                           <th>Updated At</th>
                           <!-- <th>Actions</th> -->
                        </tr>
                     </thead>
                     <tbody>
                        <?php $__currentLoopData = $low_stock_products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                           <td><?php echo e($product->id); ?></td>
                           <td><?php echo e($product->name); ?></td>
                           <td><img class="product-img" src="<?php echo e(Storage::url($product->image)); ?>" alt=""></td>
                           <td><?php echo e($product->barcode); ?></td>
                           <td><?php echo e($product->sell_price); ?></td>
                           <td class="text-danger"><?php echo e($product->quantity); ?></td>
                           <td>
                              <span class="right badge badge-<?php echo e($product->status ? 'success' : 'danger'); ?>"><?php echo e($product->status ? __('common.Active') : __('common.Inactive')); ?></span>
                           </td>
                           <td><?php echo e($product->updated_at); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </section>
      </div>
 
     
    
   </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Shahin Files + Live Projects\LIVE PROJECTS\aone-pos-laravel\aone.qoyelxyz.com\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>