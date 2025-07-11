<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class PurchasereturnCartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {

            return response(
                auth()->user()->cart->each(function ($product) {
                    $customer = Customer::find($product->pivot->customer_id);
                    $product->pivot->user_balance = $customer?->balance ?? 0;
                })
            );
        }
        $viewPath = auth()->user()->role === 'admin' ? 'admin.purchasereturn.purchasereturn-cart' : 'user.purchasereturn.purchasereturn-cart';
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
        $cart = auth()->user()->cart()->where('barcode', $barcode)->first();
        if ($cart) {
            // check product quantity
            if ($product->quantity <= $cart->pivot->quantity) {
                return response([
                    'message' => __('cart.available', ['quantity' => $product->quantity]),
                ], 400);
            }
            // update only quantity
            $cart->pivot->quantity = $cart->pivot->quantity + 1;
            $cart->pivot->save();
        } else {
            if ($product->quantity < 1) {
                return response([
                    'message' => __('cart.outstock'),
                ], 400);
            }
            auth()->user()->cart()->attach($product->id, ['quantity' => 1, 'customer_id' => $customer_id]);
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
        $cart = auth()->user()->cart()->where('id', $request->product_id)->first();

        if ($cart) {
            // check product quantity
            if ($product->quantity < $request->quantity) {
                return response([
                    'message' => __('cart.available', ['quantity' => $product->quantity]),
                ], 400);
            }
            $cart->pivot->quantity = $request->quantity;
            $cart->pivot->save();
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
        auth()->user()->cart()->detach($request->product_id);

        return response('', 204);
    }

    public function empty(Request $request)
    {
        auth()->user()->cart()->detach();

        return response('', 204);
    }
}
