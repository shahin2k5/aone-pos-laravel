@extends('admin.layouts.admin')

@section('title', __('customer.Update_Customer'))
@section('content-header', __('customer.Update_Customer'))

@section('content')

    <div class="card">
        <div class="card-body">

            <form action="{{ route('admin.customers.update', $customer) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="first_name">{{ __('customer.First_Name') }}</label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                           id="first_name"
                           placeholder="{{ __('customer.First_Name') }}" value="{{ old('first_name', $customer->first_name) }}">
                    @error('first_name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="last_name">{{ __('customer.Last_Name') }}</label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                           id="last_name"
                           placeholder="{{ __('customer.Last_Name') }}" value="{{ old('last_name', $customer->last_name) }}">
                    @error('last_name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">{{ __('customer.Email') }}</label>
                    <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" id="email"
                           placeholder="{{ __('customer.Email') }}" value="{{ old('email', $customer->email) }}">
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">{{ __('customer.Phone') }}</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" id="phone"
                           placeholder="{{ __('customer.Phone') }}" value="{{ old('phone', $customer->phone) }}">
                    @error('phone')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">{{ __('customer.Address') }}</label>
                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                           id="address"
                           placeholder="{{ __('customer.Address') }}" value="{{ old('address', $customer->address) }}">
                    @error('address')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="avatar">{{ __('customer.Avatar') }}</label>
                    @if($customer->avatar)
                        <div class="mb-2">
                            <img src="{{ $customer->avatar_url }}" alt="Current Avatar" style="max-width: 100px; max-height: 100px;">
                        </div>
                    @endif
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="avatar" id="avatar">
                        <label class="custom-file-label" for="avatar">{{ __('customer.Choose_file') }}</label>
                    </div>
                    @error('avatar')
                    <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                    @enderror
                </div>


                <button class="btn btn-primary" type="submit">Update</button>
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
