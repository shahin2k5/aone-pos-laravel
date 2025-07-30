<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesreturnCartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            // Use SalesreturnItemCart instead of regular cart
            $cart = \App\Models\SalesreturnItemCart::with(['product', 'customer'])->get();
            return response($cart);
        }

        $viewPath = Auth::user()->role === 'admin' ? 'admin.salesreturn.salesreturn-cart' : 'user.salesreturn.salesreturn-cart';
        return view($viewPath);
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'required|exists:products,barcode',
            'customer_id' => 'required|exists:customers,id',
        ]);
        $barcode = $request->barcode;
        $customer_id = $request->customer_id;

        $product = Product::where('barcode', $barcode)->first();
        $cart = \App\Models\SalesreturnItemCart::where('product_id', $product->id)->first();
        if ($cart) {
            // check product quantity
            if ($product->quantity <= $cart->qnty) {
                return response([
                    'message' => __('cart.available', ['quantity' => $product->quantity]),
                ], 400);
            }
            // update only quantity
            $cart->qnty = $cart->qnty + 1;
            $cart->total_price = $cart->qnty * $cart->sell_price;
            $cart->save();
        } else {
            if ($product->quantity < 1) {
                return response([
                    'message' => __('cart.outstock'),
                ], 400);
            }
            // Create new cart item
            $data = [
                'purchase_price' => $product->purchase_price ?? 0,
                'total_price' => $product->sell_price,
                'sell_price' => $product->sell_price,
                'qnty' => 1,
                'product_id' => $product->id,
                'order_id' => 0,
                'customer_id' => $customer_id,
                'user_id' => Auth::user()->id,
                'branch_id' => Auth::user()->branch_id,
                'company_id' => Auth::user()->company_id,
            ];
            \App\Models\SalesreturnItemCart::create($data);
        }

        return response('', 204);
    }

    public function changeQty(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);
        $cart = \App\Models\SalesreturnItemCart::where('product_id', $request->product_id)->first();

        if ($cart) {
            // check product quantity
            if ($product->quantity < $request->quantity) {
                return response([
                    'message' => __('cart.available', ['quantity' => $product->quantity]),
                ], 400);
            }
            $cart->qnty = $request->quantity;
            $cart->total_price = $cart->qnty * $cart->sell_price;
            $cart->save();
        }

        return response([
            'success' => true
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id'
        ]);
        \App\Models\SalesreturnItemCart::where('product_id', $request->product_id)->delete();

        return response('', 204);
    }

    public function empty(Request $request)
    {
        \App\Models\SalesreturnItemCart::truncate();

        return response('', 204);
    }
}
