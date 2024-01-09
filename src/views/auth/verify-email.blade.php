{{--
This is only used for the laravel Breeze scaffolding.
--}}
@extends('auth._auth_card_partial')

@section('auth_card_inside_form')

    <p class="text-light">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <p class="text-light">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </p>
    @endif

    {{-- Full card width submit button. --}}
    <div class="d-grid gap-2">
        <button class="btn btn-secondary" type="submit">{{ __('Resend Verification Email') }}</button>
    </div>

@endsection