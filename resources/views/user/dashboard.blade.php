@extends('user.layouts.user')

@section('title', __('Dashboard'))
@section('content-header', __('Dashboard'))

@section('css')
<style>
    .product-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }

    .table thead th {
        background-color: #343a40;
        color: white;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }

    .card-body {
        padding: 1.25rem;
    }

    .table td {
        vertical-align: middle;
        padding: 0.75rem;
    }

    .text-primary {
        color: #007bff !important;
    }

    .font-weight-bold {
        font-weight: 700 !important;
    }

    @media (max-width: 768px) {
        .col-lg-6 {
            margin-bottom: 2rem;
        }

        .table-responsive {
            font-size: 0.875rem;
        }

        .product-img {
            width: 30px;
            height: 30px;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
   <div class="row">

       <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-danger">
            <div class="inner">
               <h3>{{config('settings.currency_symbol')}} {{number_format(($payment_customer_today-$payment_supplier_today), 2)}}</h3>
               <p>{{ __('Today Cash') }}</p>
            </div>
            <div class="icon">
               <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{route('user.sales.index')}}" class="small-box-footer">{{ __('common.More_info') }} <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>

      <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-info">
            <div class="inner">
               <h3>{{$today_sales}}</h3>
               <p>{{ __('Today Sales') }}</p>
            </div>
            <div class="icon">
               <i class="ion ion-bag"></i>
            </div>
            <a href="{{route('user.sales.index')}}" class="small-box-footer">{{ __('common.More_info') }} <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>
      <!-- ./col -->
      {{-- Remove any purchase or purchase return blocks/cards --}}
      <!-- ./col -->

      <!-- ./col -->
      <div class="col-lg-3 col-6">
         <!-- small box -->
         <div class="small-box bg-warning">
            <div class="inner">
               <h3>{{$today_profit}}</h3>
               <p>{{ __('Today Profit') }}</p>
            </div>
            <div class="icon">
               <i class="ion ion-person-add"></i>
            </div>
            <a href="{{ route('user.customers.index') }}" class="small-box-footer">{{ __('common.More_info') }} <i class="fas fa-arrow-circle-right"></i></a>
         </div>
      </div>
      <!-- ./col -->
   </div>
</div>
<div class="container-fluid">
   <div class="row">

        <div class="col-lg-6 my-4">
         <h3>Best Selling Products (Last 30 Days)</h3>
         <section class="content">
            <div class="card product-list">
               <div class="card-body" style="max-height:400px; overflow-y: auto;">
                  <table class="table table-striped table-hover">
                     <thead class="thead-dark">
                        <tr>
                           <th>ID</th>
                           <th>Name</th>
                           <th>Image</th>
                           <th>Price</th>
                           <th>Sold Qty</th>
                           <th>Revenue</th>
                           <th>Status</th>
                        </tr>
                     </thead>
                     <tbody>
                        @forelse ($best_selling_products as $product)
                        <tr>
                           <td>{{$product->id}}</td>
                           <td>
                              <strong>{{$product->name}}</strong>
                              @if($product->barcode)
                                 <br><small class="text-muted">Barcode: {{$product->barcode}}</small>
                              @endif
                           </td>
                           <td>
                              @if($product->image)
                                 <img class="product-img" src="{{ Storage::url($product->image) }}" alt="{{$product->name}}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                              @else
                                 <img class="product-img" src="{{ asset('images/img-placeholder.jpg') }}" alt="No Image" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                              @endif
                           </td>
                           <td>{{ config('settings.currency_symbol', '৳') }} {{number_format($product->sell_price, 2)}}</td>
                           <td>
                              <span class="badge badge-success">{{$product->total_sold}}</span>
                           </td>
                           <td>
                              <span class="text-primary font-weight-bold">{{ config('settings.currency_symbol', '৳') }} {{number_format($product->total_revenue, 2)}}</span>
                           </td>
                           <td>
                              <span class="badge badge-{{ $product->status ? 'success' : 'danger' }}">{{$product->status ? 'Active' : 'Inactive'}}</span>
                           </td>
                        </tr>
                        @empty
                        <tr>
                           <td colspan="7" class="text-center text-muted">
                              <i class="fas fa-info-circle"></i> No sales data available for the last 30 days
                           </td>
                        </tr>
                        @endforelse
                     </tbody>
                  </table>
               </div>
            </div>
         </section>
      </div>

      <div class="col-lg-6 my-4">
         <h3>Low Stock Products</h3>
         <section class="content">
            <div class="card product-list">
               <div class="card-body" style="max-height:400px; overflow-y: auto;">
                  <table class="table table-striped table-hover">
                     <thead class="thead-dark">
                        <tr>
                           <th>ID</th>
                           <th>Name</th>
                           <th>Image</th>
                           <th>Barcode</th>
                           <th>Price</th>
                           <th>Stock</th>
                           <th>Status</th>
                        </tr>
                     </thead>
                     <tbody>
                        @forelse ($low_stock_products as $product)
                        <tr>
                           <td>{{$product->id}}</td>
                           <td>
                              <strong>{{$product->name}}</strong>
                           </td>
                           <td>
                              @if($product->image)
                                 <img class="product-img" src="{{ Storage::url($product->image) }}" alt="{{$product->name}}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                              @else
                                 <img class="product-img" src="{{ asset('images/img-placeholder.jpg') }}" alt="No Image" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                              @endif
                           </td>
                           <td>{{$product->barcode}}</td>
                           <td>{{ config('settings.currency_symbol', '৳') }} {{number_format($product->sell_price, 2)}}</td>
                           <td>
                              @php
                                 $stock = \App\Models\BranchProductStock::where('product_id', $product->id)
                                     ->where('branch_id', auth()->user()->branch_id)
                                     ->first();
                                 $quantity = $stock ? $stock->quantity : 0;
                              @endphp
                              @if($quantity == 0)
                                 <span class="badge badge-danger">Out of Stock</span>
                              @elseif($quantity < 5)
                                 <span class="badge badge-warning">{{$quantity}}</span>
                              @else
                                 <span class="badge badge-info">{{$quantity}}</span>
                              @endif
                           </td>
                           <td>
                              <span class="badge badge-{{ $product->status ? 'success' : 'danger' }}">{{$product->status ? 'Active' : 'Inactive'}}</span>
                           </td>
                        </tr>
                        @empty
                        <tr>
                           <td colspan="7" class="text-center text-muted">
                              <i class="fas fa-check-circle"></i> All products have sufficient stock (≥20 units)
                           </td>
                        </tr>
                        @endforelse
                     </tbody>
                  </table>
               </div>
            </div>
         </section>
      </div>

   </div>
</div>
@endsection
