<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\BranchProductStock;
use App\Models\Branch;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // If it's a JSON request (from React component), return products
        if ($request->wantsJson()) {
            if ($user->role === 'admin') {
                // For admin, use global scope (company_id only)
                $query = new Product();

                // Handle search parameter
                if ($request->has('search') && !empty($request->search)) {
                    $search = $request->search;
                    $query = $query->where('name', 'like', "%{$search}%");
                }

                $products = $query->get();

                // Add all branch stocks as an associative array
                $products->each(function ($product) {
                    $branchStocks = BranchProductStock::where('product_id', $product->id)->get();
                    $product->branch_stocks = $branchStocks->pluck('quantity', 'branch_id');
                });
            } else {
                // For users, show all products that have stock in their branch
                $query = Product::withoutGlobalScopes()
                    ->where('products.company_id', $user->company_id);

                // Handle search parameter
                if ($request->has('search') && !empty($request->search)) {
                    $search = $request->search;
                    $query = $query->where('products.name', 'like', "%{$search}%");
                }

                // Join with branch_product_stock to get products with stock in user's branch
                $products = $query->join('branch_product_stock', 'products.id', '=', 'branch_product_stock.product_id')
                    ->where('branch_product_stock.branch_id', $user->branch_id)
                    ->where('branch_product_stock.quantity', '>', 0) // Only products with stock > 0
                    ->select('products.*', 'branch_product_stock.quantity')
                    ->get();
            }

            // Add debugging
            Log::info('Products loaded for user: ' . $user->id . ', company_id: ' . $user->company_id . ', count: ' . $products->count());

            return response()->json(['data' => $products]);
        }

        // For regular view requests, return paginated products using global scope
        $products = new Product();
        $products = $products->latest()->paginate(10);
        $viewPath = $user->role === 'admin' ? 'admin.products.index' : 'user.products.index';
        return view($viewPath, compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        $viewPath = $user->role === 'admin' ? 'admin.products.create' : 'user.products.create';
        if ($user->role === 'admin') {
            $branches = \App\Models\Branch::all();
            return view($viewPath, compact('branches'));
        }
        return view($viewPath);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductStoreRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = Auth::user()->company_id;
        $data['branch_id'] = Auth::user()->branch_id;
        $data['user_id'] = Auth::id();

        // Handle image upload
        if ($request->hasFile('image')) {
            Log::info('Image upload detected', [
                'original_name' => $request->file('image')->getClientOriginalName(),
                'size' => $request->file('image')->getSize(),
                'mime_type' => $request->file('image')->getMimeType()
            ]);
            $data['image'] = $request->file('image')->store('products', 'public');
            Log::info('Image stored at: ' . $data['image']);
        } else {
            Log::info('No image file in request');
        }

        $product = Product::create($data);
        $user = Auth::user();
        if ($user->role === 'admin' && $request->has('branch_stock')) {
            foreach ($request->input('branch_stock') as $branch_id => $quantity) {
                \App\Models\BranchProductStock::create([
                    'product_id' => $product->id,
                    'branch_id' => $branch_id,
                    'quantity' => $quantity,
                ]);
            }
        } else {
            // User: only their branch
            $initialQuantity = $data['quantity'] ?? 0;
            \App\Models\BranchProductStock::create([
                'product_id' => $product->id,
                'branch_id' => $data['branch_id'],
                'quantity' => $initialQuantity,
            ]);
        }
        $routeName = $user->role === 'admin' ? 'admin.products.index' : 'user.products.index';
        return redirect()->route($routeName)->with('success', 'Product saved successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.products.show' : 'user.products.show';
        return view($viewPath, compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $user = Auth::user();
        $viewPath = $user->role === 'admin' ? 'admin.products.edit' : 'user.products.edit';
        if ($user->role === 'admin') {
            // Get all branches for the company
            $branches = Branch::all();
            // Get all branch stocks for this product, keyed by branch_id
            $branchStocks = $product->branchStocks()->get()->keyBy('branch_id');
            return view($viewPath, compact('product', 'branches', 'branchStocks'));
        }
        return view($viewPath, compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            Log::info('Image upload detected in update', [
                'original_name' => $request->file('image')->getClientOriginalName(),
                'size' => $request->file('image')->getSize(),
                'mime_type' => $request->file('image')->getMimeType()
            ]);
            $data['image'] = $request->file('image')->store('products', 'public');
            Log::info('Image stored at: ' . $data['image']);
        } else {
            Log::info('No image file in update request');
        }

        $product->update($data);
        $user = Auth::user();
        // Admin: update all branch stocks
        if ($user->role === 'admin' && $request->has('branch_stock')) {
            foreach ($request->input('branch_stock') as $branch_id => $quantity) {
                $stock = \App\Models\BranchProductStock::firstOrCreate([
                    'product_id' => $product->id,
                    'branch_id' => $branch_id,
                ]);
                $stock->quantity = $quantity;
                $stock->save();
            }
        } else if (isset($data['quantity'])) {
            // User: update only their branch stock
            $branch_id = $user->branch_id;
            $stock = \App\Models\BranchProductStock::firstOrCreate([
                'product_id' => $product->id,
                'branch_id' => $branch_id,
            ]);
            $stock->quantity = $data['quantity'];
            $stock->save();
        }
        $routeName = $user->role === 'admin' ? 'admin.products.index' : 'user.products.index';
        return redirect()->route($routeName)->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
        $routeName = Auth::user()->role === 'admin' ? 'admin.products.index' : 'user.products.index';
        return redirect()->route($routeName)->with('success', 'Product deleted successfully!');
    }
}
