@extends('user.layouts.user')

@section('title', __('Salesreturn'))
@section('content-header', __('Salesreturn'))
@section('content-actions')
<a href="{{route('user.salesreturns.index')}}" class="btn btn-primary">{{ __('+ Salesreturn') }}</a>
@endsection
@section('content')

<div class="card shadow-sm">

    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <h5 class="card-title text-primary mb-0">
                    <i class="fas fa-undo-alt mr-2"></i>{{ __('Sales Return History') }}
                </h5>
            </div>
            <div class="col-md-6">
                <form action="{{route('user.salesreturns.index')}}" class="form-inline justify-content-end">
                    <div class="form-group mr-2">
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{request('start_date')}}" placeholder="Start Date" />
                    </div>
                    <div class="form-group mr-2">
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{request('end_date')}}" placeholder="End Date" />
                    </div>
                    <div class="form-group">
                        <button class="btn btn-outline-primary btn-sm" type="submit">
                            <i class="fas fa-search mr-1"></i>{{ __('Filter') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-center" style="width: 5%">{{ 'ID' }}</th>
                        <th style="width: 15%">{{ 'Customer' }}</th>
                        <th style="width: 10%">{{ 'Invoice' }}</th>
                        <th class="text-center" style="width: 10%">{{ 'Return Qnty.' }}</th>
                        <th class="text-right" style="width: 15%">{{ 'Original Sale Total' }}</th>
                        <th class="text-right" style="width: 15%">{{ 'Return Amount' }}</th>
                        <th class="text-right" style="width: 15%">{{ 'Loss' }}</th>
                        <th style="width: 10%">{{ 'Created' }}</th>
                        <th class="text-center" style="width: 10%">{{ 'Actions' }}</th>
                    </tr>
                </thead>
            <tbody>
                @foreach ($salesreturns as $salesreturn)
                <tr>
                    <td class="text-center font-weight-bold"> {{$salesreturn->id}}</td>
                    <td>
                        <div class="font-weight-bold text-primary">{{$salesreturn->getCustomerName()}}</div>
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{$salesreturn->order_id}}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-info">{{number_format($salesreturn->total_qnty)}}</span>
                    </td>
                    <td class="text-right font-weight-bold">
                        {{ config('settings.currency_symbol') }} {{number_format($salesreturn->total_amount)}}
                    </td>
                    <td class="text-right font-weight-bold text-success">
                        {{ config('settings.currency_symbol') }} {{number_format($salesreturn->return_amount)}}
                    </td>
                    <td class="text-right font-weight-bold {{ $salesreturn->getLossClass() }}">
                        {{ config('settings.currency_symbol') }} {{ $salesreturn->getFormattedLoss() }}
                    </td>
                    <td>
                        <small class="text-muted">{{$salesreturn->created_at->format('M d, Y')}}</small>
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('user.salesreturn.details', $salesreturn->id) }}" class="btn btn-sm btn-outline-success" title="View Details">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="/user/salesreturn/print/{{ $salesreturn->id }}" target="_blank" class="btn btn-sm btn-outline-info" title="Print Invoice">
                                <i class="fa fa-print"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        </div>

        @if($salesreturns->count() > 0)
            <div class="d-flex justify-content-center mt-3">
                {{ $salesreturns->render() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Sales Returns Found</h5>
                <p class="text-muted">No sales returns have been created yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
@section('model')
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

@endsection

@section('css')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0,123,255,.075);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }

    .badge {
        font-size: 0.8em;
        padding: 0.4em 0.6em;
    }

    .btn-group .btn {
        margin: 0 1px;
    }

    .card {
        border: none;
        border-radius: 10px;
    }

    .thead-dark th {
        background-color: #343a40;
        border-color: #454d55;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85em;
        letter-spacing: 0.5px;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }

    .text-success {
        color: #28a745 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .text-warning {
        color: #ffc107 !important;
    }
</style>
@endsection

@section('js')
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
                            '<span class="badge badge-danger">{{ __('salesreturn.Not_Paid') }}</span>':
                        receivedAmount < totalAmount ?
                            '<span class="badge badge-warning">{{ __('salesreturn.Partial') }}</span>':
                        receivedAmount == totalAmount?
                            '<span class="badge badge-success">{{ __('salesreturn.Paid') }}</span>':
                        receivedAmount > totalAmount?
                            '<span class="badge badge-info">{{ __('salesreturn.Change') }}</span>':''
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
                          <strong>{{config('settings.currency_symbol')}} ${totalAmount}</strong>
                        </th>
                      </tr>

                      <tr>
                        <th class="text-right" colspan="5">
                          Paid
                        </th>
                        <th class="right">
                          <strong>{{config('settings.currency_symbol')}} ${receivedAmount}</strong>
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
@endsection
