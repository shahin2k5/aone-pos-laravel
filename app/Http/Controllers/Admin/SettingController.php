<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function index()
    {
        $company_id = auth()->user()->company_id;
        $branch_list = Branch::where('company_id', $company_id)->get();
        $user_list = User::where('company_id', $company_id)->get();
        return view('admin.settings.edit',compact('user_list', 'branch_list'));
    }

    public function store(Request $request)
    {
        $data = $request->except('_token');
        foreach ($data as $key => $value) {
            $setting = Setting::firstOrCreate(['key' => $key]);
            $setting->value = $value;
            $setting->save();
        }

        return redirect()->route('settings.index');
    }

    public function loadBranches(){
        if (request()->wantsJson()) {
            return response(
                Branch::all()
            );
        }

    }

    public function branchStore(Request $request){
        $validate = $request->validate([
            'branch_name'=>'required|string|unique:branches,branch_name',
            'address'=>'required|string',
            'mobile'=>'required|string',
        ]);
        $validate['user_id'] = auth()->user()->id;
        $validate['company_id'] = auth()->user()->company_id;
        Branch::create($validate);
      
        return redirect()->route('admin.settings.index',['tab'=>'branch'])->with('success','Branch added successfully!');
    }

    public function userStore(Request $request){
        
        $validate = $request->validate([
            'branch_id'=>'required|string',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string',
            'first_name'=>'required|string',
            'last_name'=>'required|string',
        ]);
        $validate['password'] = Hash::make($validate['password']);
        $validate['role'] = 'user';
        $validate['company_id'] = auth()->user()->company_id;
        User::create($validate);
       
        return redirect()->route('admin.settings.index',['tab'=>'user'])->with('success','User added successfully!');
    }
}
