@extends('admin.layouts.admin')

@section('title', __('Add Damage'))
@section('content-header', __('Add Damage'))

@section('content')
<div class="row">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.damages.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="select-product">{{ 'Product'}}</label>
                        <select onchange="selectProduct(this)" class="form-control" name="select-product" id="select-product" required>
                            <option value="">:: Select product for damage ::</option>
                            @foreach ($products as $product )
                                <option value='{{$product->id."=".$product->name."=".$product->purchase_price."=".$product->sell_price}}'>{{$product->name}}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="product_id" readonly class="form-control @error('product_id') is-invalid @enderror" id="product_id"
                            placeholder="Product ID" required value="{{ old('product_id') }}">

                        @error('product_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="purchase_price">{{ 'Purchase Price' }}</label>
                        <input type="text" name="purchase_price" readonly class="form-control @error('purchase_price') is-invalid @enderror" id="purchase_price"
                            placeholder="Purchase Price" value="{{ old('purchase_price') }}">
                        @error('purchase_price')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sell_price">Sell Price</label>
                        <input type="text" name="sell_price" readonly class="form-control @error('sell_price') is-invalid @enderror" id="sell_price"
                        placeholder="Sell Price" value="{{ old('sell_price') }}">
                        @error('sell_price')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <!-- Branch Stock and Damage Quantities -->
                    <div id="branch-damage-section" style="display: none;">
                        <h5 class="mt-4 mb-3">Branch Stock & Damage Quantities</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Branch</th>
                                        <th>Available Stock</th>
                                        <th>Damage Quantity</th>
                                        <th>Remaining Stock</th>
                                    </tr>
                                </thead>
                                <tbody id="branch-stock-tbody">
                                    <!-- Branch rows will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="damage_notes">Damage Notes</label>
                        <textarea name="damage_notes" class="form-control @error('damage_notes') is-invalid @enderror"
                            id="damage_notes" placeholder="Damage Notes">{{ old('damage_notes') }}</textarea>
                        @error('damage_notes')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="text-right">
                        <button class="btn btn-primary" type="submit" id="submit-btn" disabled>{{ 'Add Damage' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>

     function selectProduct($this){
           const $product = $this.value.split('=');
           const productId = $product[0];

           if (!productId) {
               // Clear everything if no product selected
               document.getElementById('product_id').value = '';
               document.getElementById('purchase_price').value = '';
               document.getElementById('sell_price').value = '';
               document.getElementById('branch-damage-section').style.display = 'none';
               document.getElementById('submit-btn').disabled = true;
               return;
           }

           document.getElementById('product_id').value = productId;
           document.getElementById('purchase_price').value = $product[2];
           document.getElementById('sell_price').value = $product[3];

           // Load branch stocks for this product
           loadBranchStocks(productId);
         }

     function loadBranchStocks(productId) {
         fetch(`/admin/branch-stocks`)
             .then(response => response.json())
             .then(data => {
                 const productStocks = data[productId] || [];
                 displayBranchStocks(productStocks);
             })
             .catch(error => {
                 console.error('Error loading branch stocks:', error);
                 document.getElementById('branch-damage-section').style.display = 'none';
             });
     }

     function displayBranchStocks(branchStocks) {
         const tbody = document.getElementById('branch-stock-tbody');
         tbody.innerHTML = '';

         if (branchStocks.length === 0) {
             document.getElementById('branch-damage-section').style.display = 'none';
             return;
         }

         branchStocks.forEach(stock => {
             const row = document.createElement('tr');
             row.innerHTML = `
                 <td>${stock.branch_name}</td>
                 <td>${stock.quantity}</td>
                 <td>
                     <input type="number"
                            class="form-control damage-qty"
                            name="damage_qty[${stock.branch_id}]"
                            min="0"
                            max="${stock.quantity}"
                            value="0"
                            onchange="updateRemainingStock(this, ${stock.quantity})"
                            style="width: 80px;">
                 </td>
                 <td>
                     <span class="remaining-stock">${stock.quantity}</span>
                 </td>
             `;
             tbody.appendChild(row);
         });

         document.getElementById('branch-damage-section').style.display = 'block';
         updateSubmitButton();
     }

     function updateRemainingStock(input, availableStock) {
         const damageQty = parseInt(input.value) || 0;
         const row = input.closest('tr');
         const remainingSpan = row.querySelector('.remaining-stock');
         const remaining = availableStock - damageQty;

         remainingSpan.textContent = remaining;

         // Visual feedback
         if (remaining < 0) {
             remainingSpan.style.color = 'red';
             input.style.borderColor = 'red';
         } else if (remaining < 10) {
             remainingSpan.style.color = 'orange';
             input.style.borderColor = 'orange';
         } else {
             remainingSpan.style.color = 'green';
             input.style.borderColor = '#28a745';
         }

         updateSubmitButton();
     }

     function updateSubmitButton() {
         const damageInputs = document.querySelectorAll('.damage-qty');
         let hasValidDamage = false;
         let hasInvalidDamage = false;

         damageInputs.forEach(input => {
             const damageQty = parseInt(input.value) || 0;
             const maxQty = parseInt(input.getAttribute('max'));

             if (damageQty > 0) {
                 hasValidDamage = true;
             }

             if (damageQty > maxQty) {
                 hasInvalidDamage = true;
             }
         });

         const submitBtn = document.getElementById('submit-btn');
         submitBtn.disabled = !hasValidDamage || hasInvalidDamage;
     }

    $(document).ready(function () {
        // Form submission validation
        $('form').on('submit', function(e) {
            const damageInputs = document.querySelectorAll('.damage-qty');
            let hasInvalidDamage = false;

            damageInputs.forEach(input => {
                const damageQty = parseInt(input.value) || 0;
                const maxQty = parseInt(input.getAttribute('max'));

                if (damageQty > maxQty) {
                    hasInvalidDamage = true;
                    input.style.borderColor = 'red';
                }
            });

            if (hasInvalidDamage) {
                e.preventDefault();
                alert('Please correct the damage quantities. Some values exceed available stock.');
                return false;
            }
        });
    });
</script>
@endsection
