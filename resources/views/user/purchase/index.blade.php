@extends('user.layouts.user')

@section('title', __('Purchase'))
@section('content-header', __('Purchase'))
@section('content-actions')
<a href="{{route('user.purchase.create')}}" class="btn btn-primary">{{ __('New Purchase') }}</a>
@endsection
@section('content')

<div class="card">

    <div class="card-body">
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <form action="{{route('user.purchase.index')}}">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="date" name="start_date" class="form-control" value="{{request('start_date')}}" />
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="end_date" class="form-control" value="{{request('end_date')}}" />
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary" type="submit">{{ __('Purchase submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Supplier' }}</th>
                    <th>{{ 'Invoice' }}</th>
                    <th>{{ 'Total' }}</th>
                    <th>{{ 'Discount' }}</th>
                    <th>{{ 'Gr. Total' }}</th>
                    <th>{{ 'Paid' }}</th>
                    <th>{{ 'Due' }}</th>
                    <th>{{ 'Created' }}</th>
                    <th>{{ 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchases as $purchase)
                <tr>
                    <td> {{$purchase->id}}</td>
                    <td>{{$purchase->supplier?$purchase->supplier->first_name:""}}</td>
                    <td>{{$purchase->invoice_no}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{number_format($purchase->sub_total)}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{number_format($purchase->discount_amount)}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{number_format($purchase->gr_total)}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{number_format($purchase->paid_amount)}}</td>
                    <td>{{$purchase->created_at}}</td>
                    <td>
                        <a href="/user/purchase/details/{{ $purchase->id }}" class="btn btn-success btn-sm" title="View Details">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="{{ route('user.purchase.print', $purchase->id) }}" class="btn btn-info btn-sm" target="_blank" title="Print Invoice">
                            <i class="fa fa-print"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</th>
                    <th>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>

        <div class="text-center"> </div>
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
