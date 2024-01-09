{{-- Forgot Password (request) form template --}}
{{-- Confusing, but this route is a get request on /password/reset --}}
@extends('auth._auth_card_partial')

@section('auth_card_inside_form')

@if (session('status'))
    <div class="alert alert-success" role="alert">
        {{ session('status') }}
    </div>
@endif
@if (session('email'))
    <div class="alert alert-warning" role="alert">
        {{ session('email') }}
    </div>
@endif

<div class="input-group form-group">

    {{-- user name (email) input area --}}
    <div class="input-group mb-3">
        {{-- user password prepend image --}}
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-user"></i></span>
        <input class="form-control @error('email') is-invalid @enderror"
               type="email"
               name="email"
               id="email"
               value="{{ old('email') }}"
               placeholder="email"
               required autocomplete="off" autofocus>
        @error('email')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>


</div>



{{-- Full card width login submit button. --}}
<div class="d-grid gap-2">
    <button class="btn btn-secondary" type="submit">{{ __('Send Password Reset Link') }}</button>
</div>

@endsection
