{{--
This is used for the laravel Breeze scaffolding.
--}}
@extends('auth._auth_card_partial')

@section('auth_card_inside_form')


{{-- This is the original Breeze version
<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
--}}

<p class="text-warning">
    @if ($errors->any())
        @error('email') {{ $errors->first('email') }} @enderror
        @error('password') {{ $errors->first('password') }} @enderror
    @elseif(!empty(session('status')))
        {{session('status')}}
    @else
        &nbsp;  {{-- Placeholder for any error message. --}}
    @endif
</p>

<!-- Password Reset Token (from the email) -->
<input type="hidden" name="token" value="{{ $request->route('token') }}">

<div class="input-group form-group">

    {{-- user name input area --}}
    <div class="input-group mb-3">
        {{-- user name prepend image --}}
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-user"></i></span>
        <input class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}"
               type="text"
               name="email"
               id="email"
               value="{{ old('email') }}"
               placeholder="username or email"
               required autocomplete="off" autofocus>
    </div>

    <span class="invalid-feedback" role="alert">
    <strong>{{ $errors->first('email') }}</strong>
    </span>

</div>

<div class="input-group form-group">

    {{-- user password input area --}}
    <div class="input-group mb-3">
        {{-- user password prepend image --}}
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-key"></i></span>
        <input class="form-control @error('password') is-invalid @enderror"
               type="password"
               name="password"
               id="password"
               value="{{ old('password') }}"
               placeholder="password"
               autocomplete="off">
    </div>

</div>


<div class="input-group form-group">

    {{-- user password cnofirmation input area --}}
    <div class="input-group mb-3">
        {{-- user password prepend image --}}
        <span class="input-group-text" id="basic-addon1"><i class="fas fa-key"></i></span>
        <input class="form-control @error('password') is-invalid @enderror"
               type="password"
               name="password_confirmation"
               id="password_confirmation"
               value="{{ old('password_confirmation') }}"
               placeholder="re-enter password"
               autocomplete="off">
    </div>

</div>


{{-- Full card width submit button. --}}
<div class="d-grid gap-2">
    <button class="btn btn-secondary" type="submit">{{ __('Reset Password') }}</button>
</div>
@endsection


@section('auth_card_under_form')

    {{-- In case the token has expired or is no good. Give the user a way to easily resend right from here. --}}
    @error('email')
    <form action="{{ route('verification.notice') }}" method="GET">
        @csrf
        {{-- Full card width submit button. --}}
        <div class="d-grid gap-2 mt-2">
            <button class="btn btn-secondary" type="submit">{{ __('Resend Verification Email') }}</button>
        </div>
    </form>
    @enderror

@endsection