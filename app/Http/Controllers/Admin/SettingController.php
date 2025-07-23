<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function index()
    {
        $company_id = Auth::user()->company_id;
        $branch_list = Branch::where('company_id', $company_id)->get();
        $user_list = User::where('company_id', $company_id)->get();
        return view('admin.settings.edit', compact('user_list', 'branch_list'));
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

    public function loadBranches()
    {
        if (request()->wantsJson()) {
            $company_id = Auth::user()->company_id;
            return response(
                Branch::where('company_id', $company_id)->get()
            );
        }
    }

    public function branchStore(Request $request)
    {
        $validate = $request->validate([
            'branch_name' => 'required|string|unique:branches,branch_name',
            'address' => 'required|string',
            'mobile' => 'required|string',
        ]);
        $validate['user_id'] = Auth::user()->id;
        $validate['company_id'] = Auth::user()->company_id;
        Branch::create($validate);

        return redirect()->route('admin.settings.index', ['tab' => 'branch'])->with('success', 'Branch added successfully!');
    }

    public function userStore(Request $request)
    {

        $validate = $request->validate([
            'branch_id' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
        ]);
        $validate['password'] = Hash::make($validate['password']);
        $validate['role'] = 'user';
        $validate['company_id'] = Auth::user()->company_id;
        User::create($validate);

        return redirect()->route('admin.settings.index', ['tab' => 'user'])->with('success', 'User added successfully!');
    }

    public function updateUser(Request $request, $userId)
    {
        $user = User::where('id', $userId)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        $validate = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'role' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'branch_id' => 'required|exists:branches,id',
        ]);

        // Check branch belongs to company
        $branch = Branch::where('id', $validate['branch_id'])
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        $user->first_name = $validate['first_name'];
        $user->last_name = $validate['last_name'];
        $user->role = $validate['role'];
        $user->email = $validate['email'];
        $user->branch_id = $validate['branch_id'];
        $user->save();

        // For AJAX, return JSON
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User updated successfully.']);
        }
        // Fallback for non-AJAX
        return redirect()->route('admin.settings.index', ['tab' => 'user'])->with('success', 'User updated successfully!');
    }

    public function deleteUser($userId)
    {
        $user = User::where('id', $userId)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();
        // Check for related records (sales, purchases, etc.)
        $hasSales = \App\Models\Sale::where('user_id', $user->id)->exists();
        $hasPurchases = \App\Models\Purchase::where('user_id', $user->id)->exists();
        // Add more checks as needed for other relations
        if ($hasSales || $hasPurchases) {
            $msg = 'Cannot delete user: user has related sales or purchases.';
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return redirect()->route('admin.settings.index', ['tab' => 'user'])->withErrors($msg);
        }
        $user->delete();
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User deleted successfully!']);
        }
        return redirect()->route('admin.settings.index', ['tab' => 'user'])->with('success', 'User deleted successfully!');
    }

    public function deleteBranch($branchId)
    {
        $branch = Branch::where('id', $branchId)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();
        $userCount = \App\Models\User::where('branch_id', $branch->id)->count();
        if ($userCount > 0) {
            $msg = 'Cannot delete branch: users are assigned to this branch.';
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return redirect()->route('admin.settings.index', ['tab' => 'branch'])->withErrors($msg);
        }
        $branch->delete();
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Branch deleted successfully!']);
        }
        return redirect()->route('admin.settings.index', ['tab' => 'branch'])->with('success', 'Branch deleted successfully!');
    }

    public function updateBranch(Request $request, $branchId)
    {
        $branch = Branch::where('id', $branchId)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        $validate = $request->validate([
            'branch_name' => 'required|string|unique:branches,branch_name,' . $branch->id,
            'address' => 'required|string',
            'mobile' => 'required|string',
        ]);

        $branch->branch_name = $validate['branch_name'];
        $branch->address = $validate['address'];
        $branch->mobile = $validate['mobile'];
        $branch->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Branch updated successfully.']);
        }
        return redirect()->route('admin.settings.index', ['tab' => 'branch'])->with('success', 'Branch updated successfully!');
    }
}
