@extends('user.layouts.user')

@section('title', __('Add Expense'))
@section('content-header', __('Add Expense'))

@section('content-actions')
<a href="{{route('user.expense.index')}}" class="btn btn-primary"><i class="fa fa-chevron-left"></i> Expenses</a>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('user.expense.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-sm-9">
                            <div class="form-group">
                                <label for="expense_head">{{ 'Expense Head'}}</label>
                                <select class="form-control @error('expense_head') is-invalid @enderror" name="expense_head" id="expense_head" required>
                                    <option value="">:: Select expense head ::</option>
                                    @foreach ($expense_heads as $exp )
                                        <option value='{{$exp->expense_head}}' {{ old('expense_head') == $exp->expense_head ? 'selected' : '' }}>{{$exp->expense_head}}</option>
                                    @endforeach
                                </select>

                                @error('expense_head')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label for="damage_notes">Expense Head</label><br>

                            <a href="{{route('user.expense.head.create')}}" role="button" class="btn btn-success">+ Expense Item</a>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="expense_description">Expense Description</label>
                        <textarea name="expense_description" class="form-control @error('expense_description') is-invalid @enderror"
                            id="expense_description" placeholder="Enter expense description..." maxlength="1000" required>{{ old('expense_description') }}</textarea>
                        <small class="form-text text-muted">Maximum 1000 characters allowed.</small>
                        @error('expense_description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>






                    <div class="form-group">
                        <label for="expense_amount">Expense Amount</label>
                        <input type="number" name="expense_amount" class="form-control @error('expense_amount') is-invalid @enderror"
                            id="expense_amount" placeholder="Enter expense amount..." value="{{ old('expense_amount', '') }}"
                            step="0.01" min="0.01" max="999999.99" required>
                        <small class="form-text text-muted">Enter amount between 0.01 and 999,999.99</small>
                        @error('expense_amount')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="text-right">
                        <button class="btn btn-primary" type="submit">{{ 'Save Expense' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const expenseHeadSelect = document.getElementById('expense_head');
        const expenseDescription = document.getElementById('expense_description');
        const expenseAmount = document.getElementById('expense_amount');

        // Real-time validation for expense head
        expenseHeadSelect.addEventListener('change', function() {
            validateExpenseHead();
        });

        // Real-time validation for expense description
        expenseDescription.addEventListener('input', function() {
            validateExpenseDescription();
        });

        // Real-time validation for expense amount
        expenseAmount.addEventListener('input', function() {
            validateExpenseAmount();
        });

        // Form submission validation
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
        });

        function validateExpenseHead() {
            const value = expenseHeadSelect.value.trim();
            if (!value) {
                showError(expenseHeadSelect, 'Please select an expense head.');
                return false;
            }
            clearError(expenseHeadSelect);
            return true;
        }

        function validateExpenseDescription() {
            const value = expenseDescription.value.trim();
            if (!value) {
                showError(expenseDescription, 'Please provide an expense description.');
                return false;
            }
            if (value.length > 1000) {
                showError(expenseDescription, 'Expense description cannot exceed 1000 characters.');
                return false;
            }
            clearError(expenseDescription);
            return true;
        }

        function validateExpenseAmount() {
            const value = parseFloat(expenseAmount.value);
            if (!expenseAmount.value || isNaN(value)) {
                showError(expenseAmount, 'Please enter a valid expense amount.');
                return false;
            }
            if (value < 0.01) {
                showError(expenseAmount, 'Expense amount must be at least 0.01.');
                return false;
            }
            if (value > 999999.99) {
                showError(expenseAmount, 'Expense amount cannot exceed 999,999.99.');
                return false;
            }
            clearError(expenseAmount);
            return true;
        }

        function validateForm() {
            const isHeadValid = validateExpenseHead();
            const isDescriptionValid = validateExpenseDescription();
            const isAmountValid = validateExpenseAmount();

            return isHeadValid && isDescriptionValid && isAmountValid;
        }

        function showError(element, message) {
            element.classList.add('is-invalid');
            let errorElement = element.parentNode.querySelector('.invalid-feedback');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'invalid-feedback';
                element.parentNode.appendChild(errorElement);
            }
            errorElement.innerHTML = '<strong>' + message + '</strong>';
        }

        function clearError(element) {
            element.classList.remove('is-invalid');
            const errorElement = element.parentNode.querySelector('.invalid-feedback');
            if (errorElement) {
                errorElement.remove();
            }
        }
    });
</script>
@endsection
