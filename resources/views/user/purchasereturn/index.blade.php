@extends('user.layouts.user')

@section('title', __('Purchase Return'))
@section('content-header', __('Purchase Return'))
@section('content-actions')
<a href="{{route('user.purchasereturn.cart')}}" class="btn btn-primary">{{ __('+ Purchase Return') }}</a>
@endsection
@section('content')

<div class="card">

    <div class="card-body">
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <form action="{{route('user.purchasereturn.index')}}">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="date" name="start_date" class="form-control" value="{{request('start_date')}}" />
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="end_date" class="form-control" value="{{request('end_date')}}" />
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary" type="submit">{{ __('Purchase Return') }}</button>
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
                    <th>{{ 'Return Quantity' }}</th>
                    <th>{{ 'Return Total' }}</th>
                    <th>{{ 'Return' }}</th>
                    <th>{{ 'Profit' }}</th>
                    <th>{{ 'Created' }}</th>
                    <th>{{ 'Actions' }}</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($purchase_returns as $purchase)
                <tr>
                    <td> {{$purchase->id}}</td>
                    <td>{{$purchase->supplier ? $purchase->supplier->first_name . ' ' . $purchase->supplier->last_name : ''}}</td>
                    <td>{{$purchase->purchase ? $purchase->purchase->invoice_no : ''}}</td>
                    <td>{{number_format($purchase->total_qnty)}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{number_format($purchase->total_amount)}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{number_format($purchase->return_amount)}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{number_format($purchase->profit_amount)}}</td>
                    <td>{{$purchase->created_at}}</td>
                    <td>
                        <a href="/user/purchasereturn/details/{{ $purchase->id }}" class="btn btn-success"><i class="fa fa-eye"></i></a>
                        <a href="/user/purchasereturn/print/{{ $purchase->id }}" target="_blank" class="btn btn-info"><i class="fa fa-print"></i></a>
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

        <div class="text-center">{{ $purchase_returns->render() }}</div>
    </div>
</div>
@endsection
