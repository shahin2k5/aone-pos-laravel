@extends('user.layouts.user')

@section('title', __('Supplier List'))
@section('content-header', __('Supplier List'))
@section('content-actions')
<a href="{{route('user.suppliers.create')}}" class="btn btn-primary">{{ __('Add Supplier') }}</a>
@endsection
@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('supplier.Avatar') }}</th>
                    <th>{{ __('First Name') }}</th>
                    <th>{{ __('Last Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Address') }}</th>
                    <th>{{ __('Created At') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($suppliers as $supplier)
                <tr>
                    <td>{{$supplier->id}}</td>
                    <td>
                       <img width="50" src="{{$supplier->avatar_url}}" alt="">
                    </td>
                    <td>{{$supplier->first_name}}</td>
                    <td>{{$supplier->last_name}}</td>
                    <td>{{$supplier->email}}</td>
                    <td>{{$supplier->phone}}</td>
                    <td>{{$supplier->address}}</td>
                    <td>{{$supplier->created_at}}</td>
                    <td>
                        <a href="{{ route('user.suppliers.edit', $supplier) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                        <button class="btn btn-danger btn-delete" data-url="{{route('user.suppliers.destroy', $supplier)}}"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $suppliers->render() }}
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                e.preventDefault();
                const button = e.target.closest('.btn-delete');
                const url = button.getAttribute('data-url');
                const row = button.closest('tr');
                const supplierName = row.querySelector('td:nth-child(3)').textContent + ' ' + row.querySelector('td:nth-child(4)').textContent;

                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                });

                swalWithBootstrapButtons.fire({
                    title: 'Are you sure?',
                    text: `Do you really want to delete supplier "${supplierName}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                        fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (response.ok) {
                                return response.json();
                            }
                            throw new Error('Network response was not ok');
                        })
                        .then(data => {
                            // Success - remove row with animation
                            row.style.transition = 'opacity 0.5s ease-out';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                                // Show success message
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Supplier has been deleted successfully.',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }, 500);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            // Reset button state
                            button.disabled = false;
                            button.innerHTML = '<i class="fas fa-trash"></i>';

                            // Show error message
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to delete supplier. Please try again.',
                                icon: 'error'
                            });
                        });
                    }
                });
            }
        });
    });
</script>
@endsection
