<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // If it's a JSON request (from React component), return suppliers using global scope
        if ($request->wantsJson() || $request->ajax()) {
            $suppliers = new Supplier();
            $suppliers = $suppliers->latest()->get();
            return response()->json($suppliers);
        }

        // For regular view requests, return paginated suppliers using global scope
        $suppliers = new Supplier();
        $suppliers = $suppliers->latest()->paginate(10);
        $viewPath = $user->role === 'admin' ? 'admin.suppliers.index' : 'user.suppliers.index';
        return view($viewPath, compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.suppliers.create' : 'user.suppliers.create';
        return view($viewPath);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['company_id'] = Auth::user()->company_id;
        $data['branch_id'] = Auth::user()->branch_id;
        $data['user_id'] = Auth::id();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            Log::info('Avatar upload detected', [
                'original_name' => $request->file('avatar')->getClientOriginalName(),
                'size' => $request->file('avatar')->getSize(),
                'mime_type' => $request->file('avatar')->getMimeType()
            ]);
            $data['avatar'] = $request->file('avatar')->store('suppliers', 'public');
            Log::info('Avatar stored at: ' . $data['avatar']);
        } else {
            Log::info('No avatar file in request');
        }

        $supplier = Supplier::create($data);
        $routeName = Auth::user()->role === 'admin' ? 'admin.suppliers.index' : 'user.suppliers.index';
        return redirect()->route($routeName)->with('success', 'Supplier saved successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.suppliers.show' : 'user.suppliers.show';
        return view($viewPath, compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function edit(Supplier $supplier)
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.suppliers.edit' : 'user.suppliers.edit';
        return view($viewPath, compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->all();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            Log::info('Avatar upload detected for update', [
                'original_name' => $request->file('avatar')->getClientOriginalName(),
                'size' => $request->file('avatar')->getSize(),
                'mime_type' => $request->file('avatar')->getMimeType()
            ]);
            $data['avatar'] = $request->file('avatar')->store('suppliers', 'public');
            Log::info('Avatar stored at: ' . $data['avatar']);
        } else {
            Log::info('No avatar file in update request');
        }

        $supplier->update($data);
        $routeName = Auth::user()->role === 'admin' ? 'admin.suppliers.index' : 'user.suppliers.index';
        return redirect()->route($routeName)->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        try {
            // Delete the avatar file if it exists
            if ($supplier->avatar) {
                Storage::delete($supplier->avatar);
            }

            $supplier->delete();

            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Supplier deleted successfully!'
                ]);
            }

            // Return redirect for regular requests
            $routeName = Auth::user()->role === 'admin' ? 'admin.suppliers.index' : 'user.suppliers.index';
            return redirect()->route($routeName)->with('success', 'Supplier deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting supplier: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete supplier. Please try again.'
                ], 500);
            }

            $routeName = Auth::user()->role === 'admin' ? 'admin.suppliers.index' : 'user.suppliers.index';
            return redirect()->route($routeName)->with('error', 'Failed to delete supplier. Please try again.');
        }
    }

    public function showPayForm(Supplier $supplier)
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.suppliers.pay' : 'user.suppliers.pay';
        return view($viewPath, compact('supplier'));
    }

    public function pay(Request $request, Supplier $supplier)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        // Paying a supplier increases the balance (debt decreases)
        $supplier->balance = $supplier->balance + $request->amount;
        $supplier->save();
        // Optionally, log the payment in SupplierPayment model
        $routeName = Auth::user()->role === 'admin' ? 'admin.suppliers.index' : 'user.suppliers.index';
        return redirect()->route($routeName)->with('success', 'Payment successful!');
    }
}
