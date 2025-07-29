<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use App\Models\SupplierPayment;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\BranchProductStock;

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
    public function index()
    {
        $user = Auth::user();
        $company_id = $user->company_id;
        $branch_id = $user->branch_id;

        $today_sales = Sale::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->with('items')
            ->get()
            ->flatMap(function ($order) {
                return $order->items;
            })
            ->reduce(function ($carry, $item) {
                return $carry + ($item->sell_price);
            }, 0);
        $customer_payment = Payment::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('amount');

        $supplier_payment = SupplierPayment::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('amount');

        $today_purchase = Purchase::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('gr_total');

        $today_purchase_due = Purchase::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->whereColumn('paid_amount', '<', 'gr_total')
            ->sum(DB::raw('gr_total - paid_amount'));

        $today_profit = Sale::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('profit_amount');

        $orders = Sale::with(['items', 'payments'])->get();
        $customers_count = Customer::count();

        // Fix: Get low stock products with proper product data
        $low_stock_products = BranchProductStock::where('branch_id', $branch_id)
            ->where('quantity', '<', 20)
            ->with(['product' => function ($query) use ($company_id) {
                $query->withoutGlobalScopes()->where('company_id', $company_id);
            }])
            ->get()
            ->map(function ($stock) {
                return $stock->product;
            })
            ->filter() // Remove null products
            ->take(10);

        // Fix: Get best selling products based on actual sales data
        $best_selling_products = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.company_id', $company_id)
            ->where('sales.branch_id', $branch_id)
            ->whereBetween('sales.created_at', [now()->subDays(30), now()]) // Last 30 days
            ->select(
                'products.id',
                'products.name',
                'products.image',
                'products.barcode',
                'products.sell_price',
                'products.status',
                'products.updated_at',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.quantity * sale_items.sell_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.image', 'products.barcode', 'products.sell_price', 'products.status', 'products.updated_at')
            ->orderByDesc('total_sold')
            ->limit(10)
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
            'best_selling_products' => $best_selling_products,
            'payment_customer_today' => $customer_payment,
            'payment_supplier_today' => $supplier_payment,
            'today_sales' => $today_sales,
            'today_purchase' => $today_purchase,
            'today_purchase_due' => $today_purchase_due,
            'today_profit' => $today_profit,
        ]);
    }
}
