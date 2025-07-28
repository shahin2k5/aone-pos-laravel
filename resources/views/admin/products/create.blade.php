@extends('admin.layouts.admin')

@section('title', __('product.Create_Product'))
@section('content-header', __('product.Create_Product'))

@section('content')

<div class="row">
<div class="col-sm-6">
<div class="card">
    <div class="card-body">

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label for="name">{{ __('product.Name') }}</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name"
                    placeholder="{{ __('product.Name') }}" value="{{ old('name') }}">
                @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>


            <div class="form-group">
                <label for="description">{{ __('product.Description') }}</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                    id="description" placeholder="{{ __('product.Description') }}">{{ old('description') }}</textarea>
                @error('description')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="image">{{ __('product.Image') }}</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" name="image" id="image" accept="image/*" style="display: none;">
                    <label class="custom-file-label" for="image" id="file-label" style="cursor: pointer; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; background-color: #fff; display: inline-block; min-width: 200px;">{{ __('product.Choose_file') }}</label>
                </div>
                <div id="image-preview" class="mt-2" style="display: none;">
                    <img id="preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                @error('image')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="barcode">{{ __('product.Barcode') }}</label>
                <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror"
                    id="barcode" placeholder="{{ __('product.Barcode') }}" value="{{ old('barcode') }}">
                @error('barcode')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

             <div class="form-group">
                <label for="price">{{ __('Purchase Price') }}</label>
                <input type="text" name="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" id="purchase_price"
                    placeholder="{{ __('product.Price') }}" value="{{ old('price') }}">
                @error('purchase_price')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="sell_price">{{ __('product.Price') }}</label>
                <input type="text" name="sell_price" class="form-control @error('sell_price') is-invalid @enderror" id="sell_price"
                    placeholder="{{ __('product.Price') }}" value="{{ old('sell_price') }}">
                @error('sell_price')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            {{-- Branch-wise Stock Table for Admins --}}
            @if(isset($branches))
            <div class="form-group">
                <label>{{ __('product.Quantity') }} ({{ __('product.Branch_wise') }})</label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('product.Branch') }}</th>
                            <th>{{ __('product.Quantity') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $branch)
                        <tr>
                            <td>{{ $branch->branch_name }}</td>
                            <td>
                                <input type="number" name="branch_stock[{{ $branch->id }}]" class="form-control" min="0"
                                    value="{{ old('branch_stock.' . $branch->id, 0) }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            {{-- Fallback if branch data not available --}}
            <div class="form-group">
                <label for="quantity">{{ __('product.Quantity') }}</label>
                <input type="text" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                    id="quantity" placeholder="{{ __('product.Quantity') }}" value="{{ old('quantity', 1) }}">
                @error('quantity')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            @endif

            <div class="form-group">
                <label for="status">{{ __('product.Status') }}</label>
                <select name="status" class="form-control @error('status') is-invalid @enderror" id="status">
                    <option value="1" {{ old('status') === 1 ? 'selected' : ''}}>{{ __('common.Active') }}</option>
                    <option value="0" {{ old('status') === 0 ? 'selected' : ''}}>{{ __('common.Inactive') }}</option>
                </select>
                @error('status')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>

            <button class="btn btn-primary" type="submit">{{ __('common.Create') }}</button>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@section('js')
<script>
    // Use vanilla JavaScript to avoid jQuery dependency issues
    document.addEventListener('DOMContentLoaded', function() {
        var fileInput = document.getElementById('image');
        var fileLabel = document.getElementById('file-label');
        var imagePreview = document.getElementById('image-preview');
        var previewImg = document.getElementById('preview-img');

        if (fileInput && fileLabel) {
            fileInput.addEventListener('change', function() {
                var file = this.files[0];

                if (file) {
                    // Update the label with the filename
                    fileLabel.textContent = file.name;

                    // Show image preview
                    if (imagePreview && previewImg) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            imagePreview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    }
                } else {
                    // Reset if no file selected
                    fileLabel.textContent = '{{ __("product.Choose_file") }}';
                    if (imagePreview) {
                        imagePreview.style.display = 'none';
                    }
                }
            });
        } else {
            console.error('File input or label not found');
        }

                // Handle leading zeros in quantity inputs
        var numberInputs = document.querySelectorAll('input[type="number"]');
        numberInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                var value = this.value;
                // Remove leading zeros but keep single zero
                if (value.length > 1 && value.startsWith('0')) {
                    this.value = value.replace(/^0+/, '');
                }
            });

            input.addEventListener('blur', function() {
                var value = this.value;
                // If empty or just zeros, set to 0
                if (value === '' || value === '0') {
                    this.value = '0';
                }
            });
        });

        // Handle form submission to clean up values before sending
        var form = document.getElementById('product-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Clean up all number inputs before submission
                var numberInputs = document.querySelectorAll('input[type="number"]');
                numberInputs.forEach(function(input) {
                    var value = input.value;
                    // Remove leading zeros but keep single zero
                    if (value.length > 1 && value.startsWith('0')) {
                        input.value = value.replace(/^0+/, '');
                    }
                    // If empty, set to 0
                    if (value === '') {
                        input.value = '0';
                    }
                });
            });
        }
    });
</script>
@endsection
