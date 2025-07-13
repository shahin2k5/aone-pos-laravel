<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use App\Models\SupplierPayment;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
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
    public function index()
    {

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

        $today_purchase_due = Purchase::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->whereColumn('paid_amount', '<', 'gr_total')
            ->sum(DB::raw('gr_total - paid_amount'));

        $sales = Sale::with(['items', 'payments'])->get();
        $customers_count = Customer::count();

        $low_stock_products = new Product();
        $low_stock_products = $low_stock_products->where('quantity', '<', 20)->get();

        // Note: The best selling products queries below use raw DB queries and may need to be updated
        // to respect company/branch filtering. For now, they will show all products.
        $bestSellingProducts = DB::table('products')
            ->joinSub(
                DB::table('sale_items')
                    ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
                    ->groupBy('product_id')
                    ->having('total_sold', '>', 10),
                'totals',
                'products.id',
                '=',
                'totals.product_id'
            )
            ->select('products.*', 'totals.total_sold')
            ->get();

        $currentMonthBestSelling = DB::table('products')
            ->joinSub(
                DB::table('sale_items')
                    ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as total_sold'))
                    ->whereYear('sales.created_at', date('Y'))
                    ->whereMonth('sales.created_at', date('m'))
                    ->groupBy('sale_items.product_id')
                    ->having('total_sold', '>', 500),
                'totals',
                'products.id',
                '=',
                'totals.product_id'
            )
            ->select('products.*', 'totals.total_sold')
            ->get();

        $pastSixMonthsHotProducts = DB::table('products')
            ->joinSub(
                DB::table('sale_items')
                    ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as total_sold'))
                    ->where('sales.created_at', '>=', now()->subMonths(6))
                    ->groupBy('sale_items.product_id')
                    ->having('total_sold', '>', 1000),
                'totals',
                'products.id',
                '=',
                'totals.product_id'
            )
            ->select('products.*', 'totals.total_sold')
            ->get();





        return view('admin.dashboard', [
            'orders_count' => $sales->count(),
            'income' => $sales->map(function ($i) {
                return $i->receivedAmount() > $i->total() ? $i->total() : $i->receivedAmount();
            })->sum(),
            'income_today' => $sales->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->map(function ($i) {
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
            'today_purchase_due' => $today_purchase_due,
            'today_profit' => $today_profit,
        ]);
    }
}
