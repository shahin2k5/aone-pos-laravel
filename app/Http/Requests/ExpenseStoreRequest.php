<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'expense_head' => [
                'required',
                'string',
                'max:255',
                Rule::exists('expense_heads', 'expense_head')->where(function ($query) {
                    $user = \Illuminate\Support\Facades\Auth::user();
                    if ($user->role === 'admin') {
                        $query->where('company_id', $user->company_id);
                    } else {
                        $query->where('company_id', $user->company_id)
                            ->where('branch_id', $user->branch_id);
                    }
                })
            ],
            'expense_description' => [
                'required',
                'string',
                'max:1000'
            ],
            'expense_amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'expense_head.required' => 'Please select an expense head.',
            'expense_head.exists' => 'The selected expense head is invalid.',
            'expense_description.required' => 'Please provide an expense description.',
            'expense_description.max' => 'Expense description cannot exceed 1000 characters.',
            'expense_amount.required' => 'Please enter the expense amount.',
            'expense_amount.numeric' => 'Expense amount must be a valid number.',
            'expense_amount.min' => 'Expense amount must be at least 0.01.',
            'expense_amount.max' => 'Expense amount cannot exceed 999,999.99.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'expense_head' => 'expense head',
            'expense_description' => 'expense description',
            'expense_amount' => 'expense amount',
        ];
    }
}
