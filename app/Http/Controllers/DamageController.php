<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use App\Models\SalesreturnItemCart;
use App\Models\Salesreturn;
use App\Models\DamageItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DamageController extends Controller
{
    public function index(Request $request)
    {
        $damages = new DamageItem();
        if ($request->start_date) {
            $damages = $damages->where('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $damages = $damages->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        $damages = $damages->with([ 'product'])->latest()->paginate(10);

        $total = 0;
        
 
        return view('damage.index', compact('damages', 'total'));
    }


    public function create(Request $request)
    {
        $products = new Product;
        $products = $products->get();
        return view('damage.create', compact('products'));
    }

    public function salesreturnDetails($salesreturn_id){
        $salesreturns = Salesreturn::where('id', $salesreturn_id)->with(['items','customer'])->get();
         $total = 0;
        return view('salesreturn.details', compact('salesreturns','total'));
    }

    public function findOrderID($order_id){
        $order = Sale::where('id',$order_id)->with(['items','customer','items.product'])->first();
        if($order){
            $items = $order->items;
            SalesreturnItemCart::truncate();
            foreach($items as $item){
                $data = [
                    'purchase_price' => $item->purchase_price,
                    'total_price' => $item->sell_price * $item->quantity,
                    'sell_price' => $item->sell_price,
                    'qnty' => $item->quantity,
                    'product_id' => $item->product_id,
                    'order_id' => $item->order_id,
                    'customer_id' => $order->customer_id,
                    'user_id' => auth()->user()->id,
                ];
                SalesreturnItemCart::create($data);
            }
            $salesreturn_item = SalesreturnItemCart::with(['product','customer','product'])->get();
            return response()->json(['order'=>$order, 'salesreturn_item'=>$salesreturn_item]);
        }
    }

    public function changeQnty(Request $request){
        $salesreturn_item = SalesreturnItemCart::where('product_id',$request->product_id )->first();
        if($salesreturn_item){
            $salesreturn_item->qnty = $request->qnty;
            $salesreturn_item->sell_price = $request->sell_price;
            $salesreturn_item->total_price = $request->sell_price * $request->qnty;
            $salesreturn_item->save();
        }
        
        return response()->json($request->all());
    }

    public function addProductToCart(Request $request){
         $request->validate([
            'barcode' => 'required',
            'customer_id' => 'required|exists:customers,id',
        ]);
        $barcode = $request->barcode;
        $product_id = $request->product_id;
        $customer_id = $request->customer_id;

       
        $salesreturn_cart = SalesreturnItemCart::where('product_id', $product_id)->first();
        if ($salesreturn_cart) {
          
            // update only quantity
            $salesreturn_cart->qnty = $salesreturn_cart->qnty + 1;
            $salesreturn_cart->total_price = $salesreturn_cart->qnty * $salesreturn_cart->sell_price;
            $salesreturn_cart->save();
        }  

        return response('', 204);
    }
 
    public function finalSave(Request $request)
    {
        try{
            $order = Sale::where('id', $request->order_id)->first();
            $return_items = SalesreturnItemCart::where('order_id', $request->order_id)->get();
        
            if(!!$order){
                $total_price = $return_items->sum('total_price');
                $return_amount = $request->amount;
                $profit_amount = $total_price - $return_amount;
                $salesreturn = Salesreturn::create([
                    'customer_id' => $request->customer_id,
                    'user_id' => $request->user()->id,
                    'order_id'=>$request->order_id,
                    'total_qnty'=>$return_items->sum('qnty'),
                    'total_amount'=> $total_price,
                    'return_amount'=>$return_amount,
                    'profit_amount'=>$profit_amount,
                    'notes'=>$request->notes,
                ]);

                
                
                foreach ($return_items as $item) {
                    $data = [
                        'salesreturn_id'=> $salesreturn->id,
                        'order_id'=>$request->order_id,
                        'product_id'=>$item->product_id,
                        'purchase_price'=>$item->purchase_price,
                        'sell_price'=>$item->sell_price,
                        'qnty'=>$item->qnty,
                        'customer_id'=>$item->customer_id,
                        'user_id' => $request->user()->id,
                    ];
                    SalesreturnItems::create($data);

                    $product = Product::where('id', $item->product_id)->first();
                    $product->quantity = $product->quantity + $item->qnty;
                    $product->save();
                
                }
 

                if($request->customer_id){
                    $customer = Customer::where('id', $request->customer_id)->first();
                    $customer->balance = $customer->balance + ($request->amount);
                    $customer->save();
                }

                SalesreturnItemCart::truncate();

                return $salesreturn;
            }
        }catch(\Exception $ex){
            return response()->json($ex->getMessage(), 500);
        }
        
       
    }

    public function handleDelete(Request $request){
        $product_id = $request->product_id;
        SalesreturnItemCart::where('product_id',$product_id)->delete();
        return response()->json('success');

    }

    public function store(Request $request)
    {
        $product = new Product;
        if($request->product_id){
            $product = $product->where('id', $request->product_id)->first();
            $product->quantity = $product->quantity - $request->damage_qnty;
            $product->save();
        }
        
        $damage = DamageItem::create([
            'product_id' => $request->product_id,
            'purchase_price' => $product->purchase_price,
            'sell_price' => $product->sell_price,
            'qnty' => $request->damage_qnty,
            'total_price' => ($product->purchase_price) * ($request->damage_qnty),
            'notes' => $request->damage_notes,
            'user_id' => $request->user()->id,
        ]);
 
    
        return back()->with('success', 'Data saved successfully!');
    }


    public function show(DamageItem $damage){
        dd('show');
    }

    public function destroy(DamageItem $damage)
    {
        $damage->delete();
        return back()->with('success','Damage deleted successfully');
    }
 
}
