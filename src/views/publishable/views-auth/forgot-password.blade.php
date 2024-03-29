{{--
This is used for the laravel Breeze scaffolding.
--}}
@extends('auth._auth_card_partial')

@section('auth_card_inside_form')
    
<p class="text-warning">
    @if ($errors->any())
        @error('email') {{ $errors->first('email') }} @enderror
    @elseif(!empty(session('status')))
        {{session('status')}}
    @else
        &nbsp;  {{-- Placeholder for any error message. --}}
    @endif
</p>

<p class="text-light">
    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
</p>

<div class="input-group form-group">

    {{-- user email input area --}}
    <div class="input-group">
        {{-- user email prepend image --}}
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-key"></i></span>
        <input class="form-control @error('email') is-invalid @enderror"
               type="text"
               name="email"
               id="email"
               value="{{ old('email') }}"
               placeholder="enter your email address"
               autocomplete="off">
    </div>

</div>

{{-- Full card width submit button. --}}
<div class="d-grid gap-2 mt-3">
    <button class="btn btn-secondary" type="submit"> {{ __('Email Password Reset Link') }}</button>
</div>


@endsection