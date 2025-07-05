<?php $__env->startSection('title', __('Expense')); ?>
<?php $__env->startSection('content-header', __('Expense')); ?>
<?php $__env->startSection('content-actions'); ?>
<a href="<?php echo e(route('expense.create')); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Add Expenses</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

<div class="card">
    <div class="card-body">
        <!-- Tab navigation -->
        <ul class="nav nav-tabs" id="expenseTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="report-tab" data-toggle="tab" href="#report" role="tab" aria-controls="report" aria-selected="true">Report</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="expense-tab" data-toggle="tab" href="#expense" role="tab" aria-controls="expense" aria-selected="false">Expense</a>
            </li>
        </ul>
        <div class="tab-content mt-3" id="expenseTabContent">
            <!-- Report Tab -->
            <div class="tab-pane fade show active" id="report" role="tabpanel" aria-labelledby="report-tab">
                <div class="report-summary-area p-3" style="background: #f8f9fa; border-radius: 8px;">
                    <h4 class="mb-4 font-weight-bold">Financial Summary Report</h4>

                    <!-- Period Display -->
                    <div class="text-right mb-3">
                        <div class="text-muted">
                            <small>Period: <?php echo e(\Carbon\Carbon::now()->startOfMonth()->format('M d, Y')); ?> - <?php echo e(\Carbon\Carbon::now()->endOfMonth()->format('M d, Y')); ?></small>
                        </div>
                    </div>

                    <div class="report-cards-row">
                        <div class="report-card mb-3">
                            <a href="<?php echo e(route('expense.sales-details')); ?>" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#007bff;"><i class="fa fa-shopping-cart"></i></div>
                                        <div class="font-weight-bold">Total Sales</div>
                                        <div class="h5 text-success mt-1"><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($netSales, 2)); ?>

                                            <small class="text-muted d-block" style="font-size: 0.8em;">Net (after returns, purchase returns)</small>
                                            <small class="text-muted d-block" style="font-size: 0.8em;">Gross: <?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($totalSales, 2)); ?>, Sales Returns: -<?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($returnsTotal, 2)); ?>, Purchase Returns: -<?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($purchaseReturnsTotal, 2)); ?></small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="<?php echo e(route('expense.purchase-details')); ?>" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#6f42c1;"><i class="fa fa-truck"></i></div>
                                        <div class="font-weight-bold">Total Purchase</div>
                                        <div class="h5 text-primary mt-1"><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($totalPurchase, 2)); ?></div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="<?php echo e(route('purchasereturn.index')); ?>" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#fd7e14;"><i class="fa fa-undo-alt"></i></div>
                                        <div class="font-weight-bold">Purchase Returns</div>
                                        <div class="h5 text-warning mt-1">-<?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($purchaseReturnsTotal, 2)); ?>

                                            <small class="text-muted d-block" style="font-size: 0.8em;">Profit Loss: -<?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($purchaseReturnsProfit, 2)); ?></small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="<?php echo e(route('expense.expense-details')); ?>" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#dc3545;"><i class="fa fa-credit-card"></i></div>
                                        <div class="font-weight-bold">Total Expenses</div>
                                        <div class="h5 text-danger mt-1"><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($totalExpenses, 2)); ?></div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="<?php echo e(route('damage.index')); ?>" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#343a40;"><i class="fa fa-exclamation-triangle"></i></div>
                                        <div class="font-weight-bold">Damaged Products</div>
                                        <div class="h5 text-dark mt-1">-<?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($damagesTotal, 2)); ?></div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="<?php echo e(route('expense.profit-details')); ?>" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#28a745;"><i class="fa fa-chart-line"></i></div>
                                        <div class="font-weight-bold">Total Profit</div>
                                        <div class="h5 text-success mt-1"><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($netProfit, 2)); ?>

                                            <small class="text-muted d-block" style="font-size: 0.8em;">Net (after all returns & damages)</small>
                                            <small class="text-muted d-block" style="font-size: 0.8em;">Gross: <?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($totalProfit, 2)); ?>, Sales Returns: -<?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($returnsProfit, 2)); ?>, Purchase Returns: -<?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($purchaseReturnsProfit, 2)); ?>, Damages: -<?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($damagesTotal, 2)); ?></small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="<?php echo e(route('expense.cash-details')); ?>" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#ffc107;"><i class="fa fa-wallet"></i></div>
                                        <div class="font-weight-bold">Cash in Hand</div>
                                        <div class="h5 text-warning mt-1"><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($cashInHand, 2)); ?>

                                            <small class="text-muted d-block" style="font-size: 0.8em;">Net (after all returns & damages)</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="<?php echo e(route('expense.purchase-details')); ?>" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#e83e8c;"><i class="fa fa-exclamation-circle"></i></div>
                                        <div class="font-weight-bold">Purchase Due</div>
                                        <div class="h5 text-danger mt-1"><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($totalPurchaseDue, 2)); ?></div>
                                        <small class="text-muted d-block" style="font-size: 0.8em;">Unpaid/Partially Paid Purchases</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Expense Tab -->
            <div class="tab-pane fade" id="expense" role="tabpanel" aria-labelledby="expense-tab">
                <!-- Existing expense filter and table -->
                <div class="row">
                    <div class="col-md-5"></div>
                    <div class="col-md-7">
                        <form action="<?php echo e(route('expense.index')); ?>">
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="date" name="start_date" class="form-control" value="<?php echo e(request('start_date')); ?>" />
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="end_date" class="form-control" value="<?php echo e(request('end_date')); ?>" />
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <select class="form-control" name="expense_head" id="expense_head" required>
                                            <option>:: Select expense head ::</option>
                                            <?php $__currentLoopData = $expense_heads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value='<?php echo e($exp->expense_head); ?>'><?php echo e($exp->expense_head); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <?php $__errorArgs = ['product_id'];
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
                                <div class="col-md-3">
                                    <button class="btn btn-outline-primary" type="submit"><?php echo e(__('Show')); ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php echo e('ID'); ?></th>
                            <th><?php echo e('Expense'); ?></th>
                            <th><?php echo e('Expense Amount.'); ?></th>
                            <th><?php echo e('Description'); ?></th>
                            <th><?php echo e('Created'); ?></th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td> <?php echo e($expense->id); ?></td>
                            <td><?php echo e($expense->expense_head); ?></td>
                            <td><?php echo e(number_format($expense->expense_amount,0)); ?></td>
                            <td><?php echo e($expense->expense_description); ?></td>
                            <td><?php echo e($expense->created_at); ?></td>
                            <td><form action="/admin/expense/<?php echo e($expense->id); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this?');"> <?php echo method_field('DELETE'); ?> <?php echo csrf_field(); ?> <button type="submit"><i class="fa fa-trash"></i></button></form></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th><?php echo e(config('settings.currency_symbol')); ?> <?php echo e(number_format($total, 2)); ?></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
                <div class="text-center"><?php echo e($expenses->render()); ?></div>
            </div>
        </div>
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
                          <strong>$<?php echo e(config('settings.currency_symbol')); ?> ${totalAmount}</strong>
                        </th>
                      </tr>

                      <tr>
                        <th class="text-right" colspan="5">
                          Paid
                        </th>
                        <th class="right">
                          <strong>$<?php echo e(config('settings.currency_symbol')); ?> ${receivedAmount}</strong>
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

<style>
.report-cards-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  justify-content: flex-start;
}
.report-card {
  flex: 0 0 220px;
  max-width: 220px;
  min-width: 220px;
  margin-bottom: 1.5rem;
}
.report-card a {
  display: block;
  border-radius: 12px;
  transition: box-shadow 0.2s, background 0.2s, transform 0.15s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  cursor: pointer;
  background: #fff;
}
.report-card a:hover, .report-card a:focus {
  box-shadow: 0 6px 24px rgba(0,0,0,0.12);
  background: #f5f7fa;
  text-decoration: none;
  transform: translateY(-2px) scale(1.03);
}
.report-card a:active {
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  background: #f0f0f0;
  transform: scale(0.98);
}
@media (max-width: 991.98px) {
  .report-cards-row {
    flex-wrap: wrap;
    justify-content: center;
  }
  .report-card {
    flex: 1 1 45%;
    max-width: 100%;
    min-width: 180px;
  }
}
@media (max-width: 767.98px) {
  .report-card {
    flex: 1 1 100%;
    max-width: 100%;
    min-width: 0;
  }
}
</style>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/senpai/Work/laravel/aone-pos-laravel/resources/views/expense/index.blade.php ENDPATH**/ ?>