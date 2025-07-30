@extends('user.layouts.user')

@section('title', __('Edit Supplier'))
@section('content-header', __('Edit Supplier'))

@section('content')

    <div class="card">
        <div class="card-body">

            <form action="{{ route('user.suppliers.update', $supplier) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="first_name">{{ __('supplier.First_Name') }}</label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                           id="first_name"
                           placeholder="{{ __('supplier.First_Name') }}" value="{{ old('first_name', $supplier->first_name) }}">
                    @error('first_name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="last_name">{{ __('supplier.Last_Name') }}</label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                           id="last_name"
                           placeholder="{{ __('supplier.Last_Name') }}" value="{{ old('last_name', $supplier->last_name) }}">
                    @error('last_name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">{{ __('supplier.Email') }}</label>
                    <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" id="email"
                           placeholder="{{ __('supplier.Email') }}" value="{{ old('email', $supplier->email) }}">
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">{{ __('supplier.Phone') }}</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" id="phone"
                           placeholder="{{ __('supplier.Phone') }}" value="{{ old('phone', $supplier->phone) }}">
                    @error('phone')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">{{ __('supplier.Address') }}</label>
                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                           id="address"
                           placeholder="{{ __('supplier.Address') }}" value="{{ old('address', $supplier->address) }}">
                    @error('address')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="avatar">{{ __('supplier.Avatar') }}</label>
                    @if($supplier->avatar)
                        <div class="mb-2">
                            <img src="{{ $supplier->avatar_url }}" alt="Current Avatar" style="max-width: 100px; max-height: 100px;">
                        </div>
                    @endif
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="avatar" id="avatar">
                        <label class="custom-file-label" for="avatar">{{ __('supplier.Choose_file') }}</label>
                    </div>
                    @error('avatar')
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                    @enderror
                </div>

                <button class="btn btn-primary" type="submit">{{ __('common.Update') }}</button>
                <a href="{{ route('user.suppliers.index') }}" class="btn btn-secondary">{{ __('common.Cancel') }}</a>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Bootstrap custom file input
            if (typeof bsCustomFileInput !== 'undefined') {
                bsCustomFileInput.init();
            } else {
                // Fallback: manual file input handling
                const fileInputs = document.querySelectorAll('.custom-file-input');
                fileInputs.forEach(function(input) {
                    input.addEventListener('change', function() {
                        const fileName = this.files[0] ? this.files[0].name : 'Choose file';
                        const label = this.nextElementSibling;
                        if (label && label.classList.contains('custom-file-label')) {
                            label.textContent = fileName;
                        }
                    });
                });
            }
        });
    </script>
@endsection
