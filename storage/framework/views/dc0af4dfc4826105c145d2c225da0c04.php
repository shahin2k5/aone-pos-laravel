

<?php $__env->startSection('title', __('Supplier List')); ?>
<?php $__env->startSection('content-header', __('Supplier List')); ?>
<?php $__env->startSection('content-actions'); ?>
<a href="<?php echo e(route('admin.suppliers.create')); ?>" class="btn btn-primary"><?php echo e(__('Add Supplier')); ?></a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="<?php echo e(asset('plugins/sweetalert2/sweetalert2.min.css')); ?>">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th><?php echo e(__('ID')); ?></th>
                    <!-- <th><?php echo e(__('supplier.Avatar')); ?></th> -->
                    <th><?php echo e(__('First Name')); ?></th>
                    <th><?php echo e(__('Last Name')); ?></th>
                    <th><?php echo e(__('Email')); ?></th>
                    <th><?php echo e(__('Phone')); ?></th>
                    <th><?php echo e(__('Address')); ?></th>
                    <th><?php echo e(__('Created At')); ?></th>
                    <th><?php echo e(__('Actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($supplier->id); ?></td>
                    
                    <td><?php echo e($supplier->first_name); ?></td>
                    <td><?php echo e($supplier->last_name); ?></td>
                    <td><?php echo e($supplier->email); ?></td>
                    <td><?php echo e($supplier->phone); ?></td>
                    <td><?php echo e($supplier->address); ?></td>
                    <td><?php echo e($supplier->created_at); ?></td>
                    <td>
                        <a href="<?php echo e(route('admin.suppliers.edit', $supplier)); ?>" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                        <button class="btn btn-danger btn-delete" data-url="<?php echo e(route('admin.suppliers.destroy', $supplier)); ?>"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php echo e($suppliers->render()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="<?php echo e(asset('plugins/sweetalert2/sweetalert2.min.js')); ?>"></script>
<script type="module">
    $(document).ready(function() {
        $(document).on('click', '.btn-delete', function() {
            var $this = $(this);
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            })

            swalWithBootstrapButtons.fire({
                title: 'Are you sure?',
                text: 'Do you really want to delete this customer?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.post($this.data('url'), {
                        _method: 'DELETE',
                        _token: '<?php echo e(csrf_token()); ?>'
                    }, function(res) {
                        $this.closest('tr').fadeOut(500, function() {
                            $(this).remove();
                        })
                    })
                }
            })
        })
    })
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Shahin Files + Live Projects\LIVE PROJECTS\aone-pos-laravel\aone.qoyelxyz.com\resources\views/admin/suppliers/index.blade.php ENDPATH**/ ?>