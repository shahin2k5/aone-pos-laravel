<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PurchaseStoreRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        $suppliers = Supplier::all();
        $salesreturns = [];
        $total = 0;
        $purchases = new Purchase();
        if ($request->start_date) {
            $purchases = $purchases->where('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $purchases = $purchases->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        $purchases = $purchases->with(['items.product', 'supplierPayments', 'supplier'])->latest()->paginate(10);

        $total = $purchases->map(function ($i) {
            return 1;
        })->sum();
        $receivedAmount = $purchases->map(function ($i) {
            return 1;
        })->sum();


        return view('admin.purchase.index', compact('products', 'suppliers','purchases','total'));
    }


    public function purchaseDetails($purchase_id){
        $purchase_items = Purchase::where('id', $purchase_id)->with(['items','supplier'])->get();
        $total = 0;
        return view('purchase.details', compact('purchase_items','total'));
    }




    public function store(PurchaseStoreRequest $request)
    {
         $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        $cart_line = "";
        $invoice_no = "";
        if(!$request->paid_amount){
            
        }

        $cart = $request->user()->purchaseCart()->get();
         
        if(isset($cart[0]->pivot)){
 
            $invoice_no = $cart[0]->pivot?$cart[0]->pivot->supplier_invoice_id:'';
        }
 
        $purchase = Purchase::create([
            'supplier_id' => $request->supplier_id,
            'invoice_no' => $invoice_no,
            'paid_amount' => $request->paid_amount,
            'user_id' => $request->user()->id,
        ]);

        $sub_total = 0;
        $discount_amount = 0;
        $gr_total = 0;
        $paid_amount = 0;
        
        foreach ($cart as $item) {
            $product = Product::where('id', $item->product_id)->first();
            $purchase->items()->create([
                'purchase_price' => $item->purchase_price?$item->purchase_price: $item->pivot->purchase_price,
                'quantity' => $item->pivot->qnty,
                'product_id' => $item->id,
            ]);
            $item->quantity = $item->quantity + $item->pivot->qnty;
            $item->save();
            $sub_total+= $item->pivot->qnty * $item->pivot->purchase_price;

        }

        $discount_amount = $request->discount_amount;
        $gr_total = $sub_total- $discount_amount;
        $paid_amount = $request->paid_amount;

        $purchase->sub_total = $sub_total;
        $purchase->discount_amount = $discount_amount;
        $purchase->gr_total = $gr_total;
        $purchase->paid_amount = $paid_amount;
        $purchase->save();
 
        $request->user()->purchaseCart()->detach();

        $supplier = Supplier::where('id',$request->supplier_id)->first();
        $supplier->balance = ($supplier->balance + $purchase->gr_total) - $paid_amount;
        $supplier->save();
        if($request->paid_amount>0){
            $purchase->supplierPayments()->create([
                'amount' => $request->paid_amount,
                'user_id' => $request->user()->id,
            ]);
        }
        
        return $purchase;
    }

    public function partialPayment(Request $request)
    {
        // return $request;
        $purchaseId = $request->purchase_id;
        $amount = $request->paid_amount;

        // Find the purchase
        $purchase = Purchase::findOrFail($purchaseId);

        // Check if the amount exceeds the remaining balance
        $remainingAmount = $purchase->total() - $purchase->receivedAmount();
        if ($amount > $remainingAmount) {
            return redirect()->route('purchases.index')->withErrors('Amount exceeds remaining balance');
        }

        // Save the payment
        DB::transaction(function () use ($purchase, $amount) {
            $purchase->supplierPayments()->create([
                'amount' => $amount,
                'user_id' => auth()->user()->id,
            ]);
        });

        return redirect()->route('purchases.index')->with('success', 'Partial payment of ' . config('settings.currency_symbol') . number_format($amount, 2) . ' made successfully.');
    }

    public function print($id)
    {
        $order = Purchase::with(['supplier', 'items'])->findOrFail($id);
        return view('purchase.print', compact('order'));
    }
    
}
