

<?php $__env->startSection('title', __('Salesreturn')); ?>
<?php $__env->startSection('content-header', __('Salesreturn#'.$salesreturns[0]->id)); ?>
<?php $__env->startSection('content-actions'); ?>
<a href="/admin/salesreturn" class="btn btn-primary"><?php echo e(__('<< Salesreturn')); ?></a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

<div class="card">
    
    <div class="card-body">
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <form action="<?php echo e(route('salesreturns.index')); ?>">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="date" name="start_date" class="form-control" value="<?php echo e(request('start_date')); ?>" />
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="end_date" class="form-control" value="<?php echo e(request('end_date')); ?>" />
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary" type="submit"><?php echo e(__('Salesreturn submit')); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <hr>

        <?php if($salesreturns): ?>
            <div class="row">
                <div class="col-md-4"><b>Order id: </b> <?php echo e($salesreturns[0]->order_id); ?></div>
                <div class="col-md-4"><b>Customer: </b> <?php echo e($salesreturns[0]->customer->first_name); ?></div>
                <div class="col-md-4"><b>Total items:</b> <?php echo e(number_format($salesreturns[0]->total_qnty,0)); ?></div>
                <div class="col-md-4"><b>Total Amount:</b> <?php echo e($salesreturns[0]->total_amount); ?></div>
                <div class="col-md-4"><b>Return Amount:</b> <?php echo e($salesreturns[0]->return_amount); ?></div>
                <div class="col-md-4"></div>
            </div>
        <?php endif; ?>
         
        <hr>
 
        <table class="table">
            <thead>
                <tr>
                    <th><?php echo e('ID'); ?></th>
                    <th><?php echo e('Product'); ?></th>
                    <th><?php echo e('Rate'); ?></th>
                    <th><?php echo e('Return Qnty.'); ?></th>
                    <th><?php echo e('Total'); ?></th>
                    <th><?php echo e('Created'); ?></th>
           
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $salesreturns[0]->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salesreturn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                  
                    <td><?php echo e($salesreturn->id); ?></td>
                    <td><img class="product-img" src="<?php echo e(Storage::url($salesreturn->product->image)); ?>" alt="" style="width:75px;height:75px"></td>
                    <td><?php echo e($salesreturn->product->name); ?></td>
                    <td><?php echo e($salesreturn->sell_price); ?></td>
                    <td><?php echo e(number_format($salesreturn->qnty)); ?></td>
                    <td><?php echo e($salesreturn->total_price); ?></td>
                    <td><?php echo e($salesreturn->created_at); ?></td>
                     
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($total, 2)); ?></th>
                    <th><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($total, 2)); ?></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    
        <div class="text-center"></div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('model'); ?>
<!-- Modal -->
<div class="modal fade" id="modalInvoice" tabindex="-1" role="dialog" aria-labelledby="modalInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInvoiceLabel">Next Gen POS</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Placeholder for dynamic content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Use event delegation to bind to the document for dynamically generated elements
    $(document).on('click', '.btnShowInvoice', function(event) {
        console.log("Modal show event triggered!");

        // Fetch data from the clicked button
        var button = $(this); // Button that triggered the modal
        var salesreturnId = button.data('salesreturn-id');
        var customerName = button.data('customer-name');
        var totalAmount = button.data('total');
        var receivedAmount = button.data('received');
        var payment = button.data('payment');
        var createdAt = button.data('created-at');
        var items = button.data('items'); // Ensure this is correctly passed as a JSON

        // Log the data to ensure it's being captured correctly
        console.log({
            salesreturnId,
            customerName,
            totalAmount,
            receivedAmount,
            createdAt,
            items
        });

        // Open the modal
        $('#modalInvoice').modal('show');

        // Populate the modal body with dynamic data (you can extend this part)
        var modalBody = $('#modalInvoice').find('.modal-body');

        // Construct items HTML if items exist
        var itemsHTML = '';
        if (items) {
            items.forEach(function(item, index) {
                itemsHTML += `
            <tr>
                <td>${index + 1}</td>
                <td>${item.product.name}</td>
                <td>${item.description || 'N/A'}</td>
                <td>${parseFloat(item.product.sell_price).toFixed(2)}</td>
                <td>${item.quantity}</td>
                <td>${(parseFloat(item.product.sell_price) * item.quantity).toFixed(2)}</td>
            </tr>
        `;
            });
        }

        // Update the modal body content
        modalBody.html(`
    <div class="card">
        <div class="card-header">
            Invoice <strong>${createdAt.split('T')[0]}</strong>
            <span class="float-right"> <strong>Status:</strong> ${

                        receivedAmount == 0?
                            '<span class="badge badge-danger"><?php echo e(__('salesreturn.Not_Paid')); ?></span>':
                        receivedAmount < totalAmount ?
                            '<span class="badge badge-warning"><?php echo e(__('salesreturn.Partial')); ?></span>':
                        receivedAmount == totalAmount?
                            '<span class="badge badge-success"><?php echo e(__('salesreturn.Paid')); ?></span>':
                        receivedAmount > totalAmount?
                            '<span class="badge badge-info"><?php echo e(__('salesreturn.Change')); ?></span>':''
            }</span>


        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="mb-3">To: <strong>${customerName || 'N/A'}</strong></h6>
                </div>
            </div>
            <div class="table-responsive-sm">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th>Unit Cost</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHTML}
                    </tbody>
                    <tfoot>
                      <tr>
                        <th class="text-right" colspan="5">
                          Total
                        </th>
                        <th class="right">
                          <strong><?php echo e(config('settings.currency_symbol')); ?> ${totalAmount}</strong>
                        </th>
                      </tr>

                      <tr>
                        <th class="text-right" colspan="5">
                          Paid
                        </th>
                        <th class="right">
                          <strong><?php echo e(config('settings.currency_symbol')); ?> ${receivedAmount}</strong>
                        </th>
                      </tr>
                    </tfood>
                </table>
            </div>
        </div>
    </div>
  </div>
</div>
`);
    });
    $(document).ready(function() {
    // Event handler when the partial payment modal is triggered
    $('#partialPaymentModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal

        // Get the salesreturn ID from data-attributes
        var salesreturnId = button.data('salesreturns-id');
        var remainingAmount = button.data('remaining-amount');

        // Find modal and set the salesreturn ID in the hidden field
        var modal = $(this);
        modal.find('#modalsalesreturnId').val(salesreturnId);
        modal.find('#partialAmount').attr('max', remainingAmount); // Set max value for partial payment
    });
});

</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Shahin Files + Live Projects\LIVE PROJECTS\aone-pos-laravel\aone.qoyelxyz.com\resources\views\user\expense\details.blade.php ENDPATH**/ ?>