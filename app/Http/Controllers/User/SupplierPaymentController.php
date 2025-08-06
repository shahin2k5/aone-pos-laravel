<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date . ' 00:00:00';
            $endDate = $request->end_date . ' 23:59:59';
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }

        $supplierPayments = SupplierPayment::withoutGlobalScopes()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['purchase.supplier'])
            ->latest()
            ->paginate(15);

        $totalAmount = $supplierPayments->sum('amount');

        return view('user.supplier-payments.index', compact('supplierPayments', 'totalAmount', 'startDate', 'endDate'));
    }
}
