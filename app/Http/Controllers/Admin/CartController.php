<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\BranchProductStock;

class CartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return response(
                Auth::user()->cart->each(function ($product) {
                    $customer = Customer::find($product->pivot->customer_id);
                    $product->pivot->user_balance = $customer?->balance ?? 0;
                })
            );
        }
        $branch_id = Auth::user()->branch_id;
        return view('admin.cart.index', compact('branch_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'required|exists:products,barcode',
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $barcode = $request->barcode;
        $customer_id = $request->customer_id;
        $branch_id = $request->branch_id;
        $user = Auth::user();
        $company_id = $user->company_id;
        $user_id = $user->id;

        $product = Product::where('barcode', $barcode)->where('products.company_id', $company_id)->first();

        if (!$product) {
            return response([
                'message' => 'Product not found or not available for your company.',
            ], 404);
        }

        $cart = $request->user()->cart()
            ->where('barcode', $barcode)
            ->where('user_cart.branch_id', $branch_id)
            ->where('user_cart.company_id', $company_id)
            ->first();

        $stock = BranchProductStock::where('product_id', $product->id)
            ->where('branch_id', $branch_id)
            ->first();

        if ($cart) {
            // check branch stock quantity
            if ($stock && $stock->quantity <= $cart->pivot->quantity) {
                return response([
                    'message' => __('cart.available', ['quantity' => $stock->quantity]),
                ], 400);
            }
            // update only quantity
            $cart->pivot->quantity = $cart->pivot->quantity + 1;
            $cart->pivot->save();
            if (!$stock || $stock->quantity < 1) {
                return response([
                    'message' => __('cart.outstock'),
                ], 400);
            }
            $request->user()->cart()->attach($product->id, [
                'quantity' => 1,
                'customer_id' => $customer_id,
                'branch_id' => $branch_id,
                'company_id' => $company_id,
                'user_id' => $user_id,
            ]);
        } else {
            if (!$stock || $stock->quantity < 1) {
                return response([
                    'message' => __('cart.outstock'),
                ], 400);
            }
            $request->user()->cart()->attach($product->id, [
                'quantity' => 1,
                'customer_id' => $customer_id,
                'branch_id' => $branch_id,
                'company_id' => $company_id,
                'user_id' => $user_id,
            ]);
        }

        return response('', 204);
    }

    public function changeQty(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'branch_id' => 'required|integer|min:1',
            'customer_id' => 'required|integer|min:1',
        ]);

        $branch_id = $request->branch_id;
        $company_id = Auth::user()->company_id;
        $user_id = Auth::id();

        $product = Product::find($request->product_id);
        // For admins, find cart item regardless of branch_id to allow branch switching
        $cart = $request->user()->cart()
            ->where('id', $request->product_id)
            ->where('user_cart.company_id', $company_id)
            ->first();

        $stock = BranchProductStock::where('product_id', $product->id)
            ->where('branch_id', $branch_id)
            ->first();



        if ($cart) {
            // check branch stock quantity
            if ($stock && $stock->quantity < $request->quantity) {
                return response([
                    'message' => __('cart.available', ['quantity' => $stock->quantity]),
                ], 400);
            }
            $cart->pivot->quantity = $request->quantity;
            // Update the branch_id to reflect the current selected branch
            $cart->pivot->branch_id = $branch_id;
            $cart->pivot->save();
        }

        return response([
            'success' => true
        ]);
    }



    public function delete(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'branch_id' => 'required|integer|exists:branches,id'
        ]);

        $company_id = Auth::user()->company_id;

        $request->user()->cart()
            ->where('user_cart.company_id', $company_id)
            ->where('user_cart.branch_id', $request->branch_id)
            ->detach($request->product_id);

        return response('', 204);
    }

    public function empty(Request $request)
    {
        $company_id = Auth::user()->company_id;

        $request->user()->cart()
            ->where('user_cart.company_id', $company_id)
            ->detach();

        return response('', 204);
    }

    public function getBranchStocks(Request $request)
    {
        $company_id = Auth::user()->company_id;

        $branchStocks = BranchProductStock::with(['branch', 'product'])
            ->whereHas('product', function ($query) use ($company_id) {
                $query->where('company_id', $company_id);
            })
            ->get()
            ->groupBy('product_id')
            ->map(function ($stocks) {
                return $stocks->map(function ($stock) {
                    return [
                        'branch_id' => $stock->branch_id,
                        'branch_name' => $stock->branch->branch_name,
                        'quantity' => $stock->quantity,
                        'product_id' => $stock->product_id
                    ];
                });
            });

        return response()->json($branchStocks);
    }

    public function loadBranches(Request $request)
    {
        $company_id = Auth::user()->company_id;

        $branches = \App\Models\Branch::where('company_id', $company_id)
            ->select('id', 'branch_name')
            ->get();

        return response()->json($branches);
    }

    public function getBranchStock($product_id, $branch_id)
    {
        $stock = BranchProductStock::where('product_id', $product_id)
            ->where('branch_id', $branch_id)
            ->first();

        return response()->json([
            'quantity' => $stock ? $stock->quantity : 0
        ]);
    }
}
