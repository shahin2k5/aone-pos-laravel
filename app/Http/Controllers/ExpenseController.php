<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use App\Models\SalesreturnItemCart;
use App\Models\Salesreturn;
use App\Models\DamageItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Expense;
use App\Models\ExpenseHead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = new Expense();
        $expense_heads = new ExpenseHead();
        $expense_heads = $expense_heads->get();
        if ($request->start_date) {
            $expenses = $expenses->where('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $expenses = $expenses->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        $expenses = $expenses->latest()->paginate(10);

        $total = $expenses->sum('expense_amount');
        
 
        return view('expense.index', compact('expenses', 'expense_heads','total'));
    }


    public function create(Request $request)
    {
        $expense_heads = new ExpenseHead;
        $expense_heads = $expense_heads->get();
        return view('expense.create', compact('expense_heads'));
    }

    public function createExpenseHead(Request $request)
    {
        $expense_heads = new ExpenseHead;
        $expense_heads = $expense_heads->get();
        return view('expense.create-head', compact('expense_heads'));
    }

    public function storeExpenseHead(Request $request){
 
        $request->validate([
            'expense_head' => 'required|unique:expense_heads,expense_head'
        ]);

        try{
            $expense = ExpenseHead::firstOrCreate([
                'expense_head' => $request->expense_head,
                'user_id' => $request->user()->id,
            ]);
 
            return back()->with('success', 'Expense head saved successfully!');
        }catch(\Exception $ex){
            return back()->withInput()->withErrors(['expense_head'=>$ex->getMessage()]);
        }
    }

    public function deleteExpenseHead($exp_head_id){

        try{
            $head = ExpenseHead::where('id', $exp_head_id)->first();
            $head->delete();
            return back()->with('success', 'Expense head deleted successfully!');
        }catch(\Exception $ex){
            return back()->withInput()->withErrors(['expense_head'=>$ex->getMessage()]);
        }
    }

    public function store(Request $request)
    {
 
        $expense = Expense::create([
            'expense_head' => $request->expense_head,
            'expense_description' => $request->expense_description,
            'expense_amount' => $request->expense_amount,
            'user_id' => $request->user()->id,
        ]);
 
    
        return redirect()->route('expense.index')->with('success', 'Expense saved successfully!');
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
