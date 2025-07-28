@extends('admin.layouts.admin')

@section('title', __('Create Purchase Return'))
@section('content-header', __('Create Purchase Return'))
@section('content-actions')
<a href="{{route('admin.purchasereturn.index')}}" class="btn btn-secondary">{{ __('← Back to Purchase Returns') }}</a>
@endsection

@section('content')
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Instructions:</strong> Enter a purchase ID to load the purchase details, then select products to return.
    </div>

    <div id="purchasereturn-cart"></div>
@endsection
