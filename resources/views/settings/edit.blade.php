@extends('layouts.admin')

@section('title', __('settings.Update_Settings'))
@section('content-header', __('settings.Update_Settings'))

@section('content')
<div class="row">
    <div class="col-sm-8">
        <div class="card">
            <div class="card-body">

                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Home</a>
                        <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">User list</a>
                        <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false">Branch List</a>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane border p-5 fade show active " id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        
                        <form action="{{ route('settings.store') }}" method="post">
                            @csrf

                            <div class="form-group">
                                <label for="app_name">{{ __('settings.app_name') }}</label>
                                <input type="text" name="app_name" class="form-control @error('app_name') is-invalid @enderror" id="app_name" placeholder="{{ __('settings.App_name') }}" value="{{ old('app_name', config('settings.app_name')) }}">
                                @error('app_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="app_description">{{ __('settings.app_description') }}</label>
                                <textarea name="app_description" class="form-control @error('app_description') is-invalid @enderror" id="app_description" placeholder="{{ __('settings.app_description') }}">{{ old('app_description', config('settings.app_description')) }}</textarea>
                                @error('app_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="currency_symbol">{{ __('settings.Currency_symbol') }}</label>
                                <input type="text" name="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" id="currency_symbol" placeholder="{{ __('settings.Currency_symbol') }}" value="{{ old('currency_symbol', config('settings.currency_symbol')) }}">
                                @error('currency_symbol')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="warning_quantity">{{ __('settings.warning_quantity') }}</label>
                                <input type="text" name="warning_quantity" class="form-control @error('warning_quantity') is-invalid @enderror" id="warning_quantity" placeholder="{{ __('settings.warning_quantity') }}" value="{{ old('warning_quantity', config('settings.warning_quantity')) }}">
                                @error('warning_quantity')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('settings.Change_Setting') }}</button>
                        </form>
                    </div><!---------- end tab-one ------------->
                    <div class="tab-pane fade border p-5" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                           <form action="{{ route('settings.store') }}" method="post">
                            @csrf

                            <div class="form-group">
                                <label for="app_name">{{ __('settings.app_name') }}</label>
                                <input type="text" name="app_name" class="form-control @error('app_name') is-invalid @enderror" id="app_name" placeholder="{{ __('settings.App_name') }}" value="{{ old('app_name', config('settings.app_name')) }}">
                                @error('app_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="app_description">{{ __('settings.app_description') }}</label>
                                <textarea name="app_description" class="form-control @error('app_description') is-invalid @enderror" id="app_description" placeholder="{{ __('settings.app_description') }}">{{ old('app_description', config('settings.app_description')) }}</textarea>
                                @error('app_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="currency_symbol">{{ __('settings.Currency_symbol') }}</label>
                                <input type="text" name="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" id="currency_symbol" placeholder="{{ __('settings.Currency_symbol') }}" value="{{ old('currency_symbol', config('settings.currency_symbol')) }}">
                                @error('currency_symbol')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="warning_quantity">{{ __('settings.warning_quantity') }}</label>
                                <input type="text" name="warning_quantity" class="form-control @error('warning_quantity') is-invalid @enderror" id="warning_quantity" placeholder="{{ __('settings.warning_quantity') }}" value="{{ old('warning_quantity', config('settings.warning_quantity')) }}">
                                @error('warning_quantity')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('settings.Change_Setting') }}</button>
                        </form>

                         <table class="border table table-border mt-5">
                            <tr>
                                <th>Branch Code</th>
                                <th>Address</th>
                                <th>Mobile</th>
                                <th>Action</th>
                            </tr>

                            <tr>
                                <th>Branch Code</th>
                                <th>Address</th>
                                <th><a href="#" class="btn btn-success">Edit</a></th>
                            </tr>

                        </table>

                    </div><!---------- end tab-two ------------->
                    <div class="tab-pane fade border p-5" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                           <form action="{{ route('settings.store') }}" method="post">
                            @csrf

                            <div class="form-group">
                                <label for="app_name">{{ __('settings.app_name') }}</label>
                                <input type="text" name="app_name" class="form-control @error('app_name') is-invalid @enderror" id="app_name" placeholder="{{ __('settings.App_name') }}" value="{{ old('app_name', config('settings.app_name')) }}">
                                @error('app_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="app_description">{{ __('settings.app_description') }}</label>
                                <textarea name="app_description" class="form-control @error('app_description') is-invalid @enderror" id="app_description" placeholder="{{ __('settings.app_description') }}">{{ old('app_description', config('settings.app_description')) }}</textarea>
                                @error('app_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="currency_symbol">{{ __('settings.Currency_symbol') }}</label>
                                <input type="text" name="currency_symbol" class="form-control @error('currency_symbol') is-invalid @enderror" id="currency_symbol" placeholder="{{ __('settings.Currency_symbol') }}" value="{{ old('currency_symbol', config('settings.currency_symbol')) }}">
                                @error('currency_symbol')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="warning_quantity">{{ __('settings.warning_quantity') }}</label>
                                <input type="text" name="warning_quantity" class="form-control @error('warning_quantity') is-invalid @enderror" id="warning_quantity" placeholder="{{ __('settings.warning_quantity') }}" value="{{ old('warning_quantity', config('settings.warning_quantity')) }}">
                                @error('warning_quantity')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('settings.Change_Setting') }}</button>
                        </form>

                         <table class="border table table-border mt-5">
                            <tr>
                                <th>Branch Code</th>
                                <th>Address</th>
                                <th>Mobile</th>
                                <th>Action</th>
                            </tr>

                            <tr>
                                <th>Branch Code</th>
                                <th>Address</th>
                                <th><a href="#" class="btn btn-success">Edit</a></th>
                            </tr>

                        </table>
                    </div><!---------- end tab-three ---------->
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
