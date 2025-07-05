<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use App\Models\SupplierPayment;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(){
        
        $today_sales = Sale::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                                        ->sum('gr_total');
        $customer_payment = Payment::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                                        ->sum('amount');
    
        $supplier_payment = SupplierPayment::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                                        ->sum('amount');

        $today_purchase = Purchase::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                                        ->sum('gr_total');

        $today_profit = Sale::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                                        ->sum('profit_amount');

        $orders = Sale::with(['items', 'payments'])->get();
        $customers_count = Customer::count();

        $low_stock_products = Product::where('quantity', '<', 20)->get();

        $bestSellingProducts = DB::table('products')
            ->select('products.*', DB::raw('SUM(sale_items.quantity) AS total_sold'))
            ->join('sale_items', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->groupBy('products.id')
            ->havingRaw('SUM(sale_items.quantity) > 10')
            ->get();

        $currentMonthBestSelling = DB::table('products')
            ->select('products.*', DB::raw('SUM(sale_items.quantity) AS total_sold'))
            ->join('sale_items', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereYear('sales.created_at', date('Y'))
            ->whereMonth('sales.created_at', date('m'))
            ->groupBy('products.id')
            ->havingRaw('SUM(sale_items.quantity) > 500')  // Best-selling threshold for the current month
            ->get();

        $pastSixMonthsHotProducts = DB::table('products')
            ->select('products.*', DB::raw('SUM(sale_items.quantity) AS total_sold'))
            ->join('sale_items', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.created_at', '>=', now()->subMonths(6))  // Filter for the past 6 months
            ->groupBy('products.id')
            ->havingRaw('SUM(sale_items.quantity) > 1000')  // Hot product threshold for past 6 months
            ->get();



        

        return view('user.dashboard', [
            'orders_count' => $orders->count(),
            'income' => $orders->map(function ($i) {
                return $i->receivedAmount() > $i->total() ? $i->total() : $i->receivedAmount();
            })->sum(),
            'income_today' => $orders->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->map(function ($i) {
                return $i->receivedAmount() > $i->total() ? $i->total() : $i->receivedAmount();
            })->sum(),
            'customers_count' => $customers_count,
            'low_stock_products' => $low_stock_products,
            'best_selling_products' => $bestSellingProducts,
            'current_month_products' => $currentMonthBestSelling,
            'past_months_products' => $pastSixMonthsHotProducts,
            'payment_customer_today' => $customer_payment,
            'payment_supplier_today' => $supplier_payment,
            'today_sales' => $today_sales,
            'today_purchase' => $today_purchase,
            'today_profit' => $today_profit,
        ]);
    }
}
