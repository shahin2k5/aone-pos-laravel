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
        $company_id = $user->company_id;

        // If it's a JSON request (from React component), return products for the company
        if ($request->wantsJson()) {
            $query = Product::where('company_id', $company_id);

            // Handle search parameter - only search by product name since barcode has its own input
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            $products = $query->get();

            // Add debugging
            Log::info('Products loaded for company_id: ' . $company_id . ', count: ' . $products->count());

            return response()->json(['data' => $products]);
        }

        // For regular view requests, return paginated products
        $products = Product::where('company_id', $company_id)->latest()->paginate(10);
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
        $viewPath = Auth::user()->role === 'admin' ? 'admin.products.create' : 'user.products.create';
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
        $product = Product::create($data);
        $routeName = Auth::user()->role === 'admin' ? 'admin.products.index' : 'user.products.index';
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
        $viewPath = Auth::user()->role === 'admin' ? 'admin.products.edit' : 'user.products.edit';
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
        $product->update($request->validated());
        $routeName = Auth::user()->role === 'admin' ? 'admin.products.index' : 'user.products.index';
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
