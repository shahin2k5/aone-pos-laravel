@extends('admin.layouts.admin')

@section('title', __('Purchase Details'))
@section('content-header', __('Purchase Details#'.$purchase->id))
@section('content-actions')
<a href="/admin/purchase" class="btn btn-primary">{{ __('<< Purchases') }}</a>
<a href="{{ route('admin.purchase.print', $purchase->id) }}" class="btn btn-info" target="_blank">
    <i class="fa fa-print"></i> {{ __('Print') }}
</a>
@endsection
@section('content')

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <form action="{{route('admin.salesreturns.index')}}">
                </form>
            </div>
        </div>
        <hr>

        @if($purchase)
            <div class="row">
                <div class="col-md-4"><b>Invoice no: </b> {{$purchase->invoice_no }}</div>
                <div class="col-md-4"><b>Supplier: </b> {{$purchase->supplier?$purchase->supplier->first_name:'' }}</div>
                <div class="col-md-4"><b>Sub Total:</b> {{number_format($purchase->sub_total,0) }}</div>
                <div class="col-md-4"><b>Discount:</b> {{$purchase->discount_amount }}</div>
                <div class="col-md-4"><b>Gr. Amount:</b> {{$purchase->gr_total }}</div>
                <div class="col-md-4"><b>Paid Amount:</b> {{$purchase->paid_amount }}</div>
                <div class="col-md-4"></div>
            </div>
        @endif

        <hr>

        <table class="table">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Product' }}</th>
                    <th>{{ 'Branch' }}</th>
                    <th>{{ 'Purchase Rate' }}</th>
                    <th>{{ 'Qnty.' }}</th>
                    <th>{{ 'Total' }}</th>
                    <th>{{ 'Created' }}</th>
                </tr>
            </thead>
            <tbody>
                @if($purchase && $purchase->items)
                @foreach ($purchase->items as $purchase_item)
                <tr>
                    <td>{{$loop->index+1}}</td>
                    <td>
                        <img class="product-img" src="{{ $purchase_item->product ? $purchase_item->product->image_url : asset('images/img-placeholder.jpg') }}" alt="" style="width:55px;height:55px">
                        {{$purchase_item->product->name ?? '-'}}
                    </td>
                    <td>{{$purchase_item->branch ? $purchase_item->branch->branch_name : '-'}}</td>
                    <td>{{ number_format($purchase_item->purchase_price, 2) }}</td>
                    <td>{{ number_format($purchase_item->quantity, 0) }}</td>
                    <td>{{ number_format($purchase_item->purchase_price * $purchase_item->quantity, 2) }}</td>
                    <td>{{$purchase_item->created_at}}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>

        <div class="text-center"></div>
    </div>
</div>
@endsection
