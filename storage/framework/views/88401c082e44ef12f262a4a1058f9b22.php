<?php $__env->startSection('title', __('Sales Return')); ?>
<?php $__env->startSection('content-header', __('Sales Return')); ?>

<?php $__env->startSection('content'); ?>

    <div id="salesreturn-cart">
        <!-- Loading placeholder while React component initializes -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-undo-alt mr-2"></i>
                            Sales Return - Add Items
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left side - Order and Cart -->
                            <div class="col-md-6 col-lg-6">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle"></i> How to Process Sales Return:</h5>
                                    <ol>
                                        <li><strong>Enter Order ID:</strong> Type the original order ID in the search field</li>
                                        <li><strong>Select Products:</strong> Click on products from the right panel to add to return cart</li>
                                        <li><strong>Adjust Quantities:</strong> Modify return quantities as needed</li>
                                        <li><strong>Set Return Amount:</strong> Enter the amount to be returned to customer</li>
                                        <li><strong>Confirm Return:</strong> Click "Confirm Sales Return" to complete</li>
                                    </ol>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Order Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="order-input-placeholder">Order ID:</label>
                                            <input type="text" class="form-control" id="order-input-placeholder" placeholder="Enter order ID to search..." disabled>
                                            <small class="form-text text-muted">Enter the original order ID to load customer and order details</small>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <strong>Customer:</strong> <span class="text-muted">Will appear after order search</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Phone:</strong> <span class="text-muted">Will appear after order search</span>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <strong>Address:</strong> <span class="text-muted">Will appear after order search</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Balance:</strong> <span class="text-muted">Will appear after order search</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Return Cart</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                            <p>Return cart is empty. Add products from the right panel.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right side - Product Search and Selection -->
                            <div class="col-md-6 col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Product Search & Selection</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="barcode-placeholder">Barcode Scanner:</label>
                                            <input type="text" class="form-control" id="barcode-placeholder" placeholder="Scan barcode or enter product code..." disabled>
                                            <small class="form-text text-muted">Scan product barcode or enter product code manually</small>
                                        </div>

                                        <div class="form-group mt-3">
                                            <label for="search-placeholder">Product Search:</label>
                                            <input type="text" class="form-control" id="search-placeholder" placeholder="Search products by name..." disabled>
                                            <small class="form-text text-muted">Type product name to search and filter products</small>
                                        </div>

                                        <div class="mt-4">
                                            <h6>Available Products:</h6>
                                            <div class="text-center text-muted">
                                                <i class="fas fa-boxes fa-3x mb-3"></i>
                                                <p>Product grid will appear here. Click on products to add to return cart.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-warning mt-3">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Important Notes:</h6>
                                    <ul class="mb-0">
                                        <li>Only products from the original order can be returned</li>
                                        <li>Return quantity cannot exceed original order quantity</li>
                                        <li>Return amount will be credited to customer's account</li>
                                        <li>Stock will be updated automatically after return confirmation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-secondary" disabled>
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                    <button type="button" class="btn btn-primary" disabled>
                                        <i class="fas fa-check"></i> Confirm Sales Return
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    .card {
        border: 1px solid #dee2e6;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .alert {
        border-radius: 0.375rem;
    }

    .btn-group .btn {
        margin: 0 2px;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .fa-3x {
        font-size: 3em;
    }

    .mb-3 {
        margin-bottom: 1rem !important;
    }

    .mt-2 {
        margin-top: 0.5rem !important;
    }

    .mt-3 {
        margin-top: 1rem !important;
    }

    .mt-4 {
        margin-top: 1.5rem !important;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/senpai/Work/laravel/aone-pos-laravel/resources/views/salesreturn/salesreturn-cart.blade.php ENDPATH**/ ?>