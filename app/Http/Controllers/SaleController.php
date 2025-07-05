<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleStoreRequest;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = new Sale();
        if ($request->start_date) {
            $sales = $sales->where('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $sales = $sales->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        $sales = $sales->with(['items.product', 'payments', 'customer'])->latest()->paginate(10);

        $total = $sales->map(function ($i) {
            return $i->total();
        })->sum();
        $receivedAmount = $sales->map(function ($i) {
            return $i->receivedAmount();
        })->sum();

 

        return view('admin.sales.index', compact('sales', 'total', 'receivedAmount'));
    }

    public function show(Sale $sale){
        $sales = $sale->load(['items','customer','items.product']);
        return response()->json($sales);
    }

    public function store(SaleStoreRequest $request)
    {
        $user = auth()->user();
        $company_id = $user->company_id;
        $branch_id = "";
        $role = $user->role;
        if($role=="admin"){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = $user->branch_id;
        }
        $sale = Sale::create([
            'customer_id' => $request->customer_id,
            'sub_total' => 0,
            'discount_amount' => 0,
            'gr_total' => 0,
            'paid_amount' => 0,
            'profit_amount' => 0,
            'user_id' => $request->user()->id,
            'branch_id' => $branch_id,
            'company_id' => $company_id,
        ]);

        $cart = $request->user()->cart()->get();
        $sum_cart = $cart->sum('sell_price');

        $totalProfit = 0; // Initialize profit calculation
        $subTotal = 0; // Initialize subtotal calculation

        foreach ($cart as $item) {
            $sale->items()->create([
                'purchase_price' => $item->purchase_price,
                'sell_price' => $item->sell_price * $item->pivot->quantity,
                'quantity' => $item->pivot->quantity,
                'product_id' => $item->id,
                'user_id' => $request->user()->id,
                'branch_id' => $branch_id,
                'company_id' => $company_id,
            ]);

            // Calculate profit for this item: (sell_price - purchase_price) * quantity
            $itemProfit = ($item->sell_price - $item->purchase_price) * $item->pivot->quantity;
            $totalProfit += $itemProfit;

            // Calculate subtotal
            $subTotal += $item->sell_price * $item->pivot->quantity;

            $item->quantity = $item->quantity - $item->pivot->quantity;
            $item->user_id = $request->user()->id;
            $item->branch_id = $branch_id;
            $item->company_id = $company_id;
            $item->save();
        }

        // Update the order with calculated values
        $sale->sub_total = $subTotal;
        $sale->gr_total = $subTotal; // Assuming no discount for now
        $sale->paid_amount = $request->amount;
        $sale->profit_amount = $totalProfit;
        $sale->save();

        $request->user()->cart()->detach();
        $sale->payments()->create([
            'amount' => $request->amount,
            'user_id' => $request->user()->id,
        ]);

        if ($request->customer_id) {
            $customer = Customer::where('id', $request->customer_id)->first();
            $customer->balance = $customer->balance + ($sum_cart - $request->amount);
            $customer->save();
        }
        return $sale;
    }
    public function partialPayment(Request $request)
    {
        // return $request;
        $orderId = $request->order_id;
        $amount = $request->amount;
        $user = auth()->user();
        if($user->role=="admin"){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = $user->branch_id;
        }

        // Find the order
        $order = Sale::findOrFail($orderId);

        if ($order->customer_id) {
            $customer = Customer::where('id', $order->customer_id)->first();
            $customer->balance = $customer->balance - $request->amount;
            $customer->save();
        }

        // Check if the amount exceeds the remaining balance
        $remainingAmount = $order->total() - $order->receivedAmount();
        if ($amount > $remainingAmount) {
            return redirect()->route('sales.index')->withErrors('Amount exceeds remaining balance');
        }

        // Save the payment
        DB::transaction(function () use ($order, $amount, $user,$branch_id) {
            $order->payments()->create([
                'amount' => $amount,
                'user_id' => auth()->user()->id,
                'branch_id' => $branch_id,
                'company_id' => $user->company_id,
            ]);
        });

        return redirect()->route('sales.index')->with('success', 'Partial payment of ' . config('settings.currency_symbol') . number_format($amount, 2) . ' made successfully.');
    }

    public function print($id)
    {
        $order = Sale::with(['customer', 'items'])->findOrFail($id);
        return view('orders.print', compact('order'));
    }
}
