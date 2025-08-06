@extends('user.layouts.user')

@section('title', 'Supplier Payments')
@section('content-header', 'Supplier Payments')
@section('content-actions')
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Supplier Payment Transactions</h3>
    </div>
    <div class="card-body">
        <!-- Date Range Filter -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" action="{{ route('user.supplier-payments.index') }}" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="start_date" class="mr-2">From:</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="{{ request('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group mr-3">
                        <label for="end_date" class="mr-2">To:</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="{{ request('end_date', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')) }}">
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">Filter</button>
                    <a href="{{ route('user.supplier-payments.index') }}" class="btn btn-secondary">Reset</a>
                </form>
            </div>
            <div class="col-md-4 text-right">
                <div class="text-muted">
                    <small>Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</small>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5>Total Payments</h5>
                        <h3>{{ $supplierPayments->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5>Total Amount</h5>
                        <h3>{{ config('settings.currency_symbol') }} {{ number_format($totalAmount, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5>Average Payment</h5>
                        <h3>{{ config('settings.currency_symbol') }} {{ $supplierPayments->count() > 0 ? number_format($totalAmount / $supplierPayments->count(), 2) : '0.00' }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Payment ID</th>
                        <th>Date & Time</th>
                        <th>Supplier Name</th>
                        <th>Purchase ID</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplierPayments as $index => $payment)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>#{{ $payment->id }}</td>
                        <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $payment->purchase && $payment->purchase->supplier ? $payment->purchase->supplier->first_name . ' ' . $payment->purchase->supplier->last_name : 'N/A' }}</td>
                        <td>#{{ $payment->purchase_id }}</td>
                        <td>{{ config('settings.currency_symbol') }} {{ number_format($payment->amount, 2) }}</td>
                        <td>
                            <a href="{{ route('user.purchase.show', ['purchase' => $payment->purchase_id]) }}" class="btn btn-sm btn-info">
                                <i class="fa fa-eye"></i> View Purchase
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No supplier payments found for the selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $supplierPayments->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
