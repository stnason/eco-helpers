{{-- The Change Password screen used by currently logged in users. --}}
@extends('auth._auth_card_partial')

@section('additional_head')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/login-page.css') }}">
    <style>
        {{-- Password strength container div --}}
        .pwstrength_viewport_progress {
            margin-top: 10px;
            width: 240px;
        }

        {{-- Actual progress bar inside of the viewport div --}}
        div.progress {
            background: red;
        }

        #pwd-container {
            margin-top: 6px;
            margin-bottom: 0;
            height: 28px;
        }
    </style>
@endsection

@section('auth_card_inside_form')

    <div class="input-group form-group">
        {{-- user name (email) prepend image --}}
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
        </div>
        {{-- user name (email) input area --}}
        <input class="form-control @error('email') is-invalid @enderror"
               type="email"
               name="email"
               id="email"
               title="Your email address"
               {{--value="{{ old('email') }}"--}}
               value="{{ Auth::user()->email }}"
               placeholder="email"
               autocomplete="email" autofocus>

        @error('email')
        <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="input-group form-group">
        {{-- user old (current) password prepend image --}}
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-key"></i></span>
        </div>
        {{-- user old (current) password input area --}}
        <input class="form-control @error('old_password') is-invalid @enderror"
               type="password"
               name="old_password"
               id="old_password"
               title="Your current password"
               value="{{ old('old_password') }}"
               placeholder="current password">
        @error('old_password')

        <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>


    <div class="input-group form-group">
        {{-- user new password prepend image --}}
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-key"></i></span>
        </div>
        {{-- user new password input area --}}
        <input class="form-control @error('new_password') is-invalid @enderror"
               type="password"
               name="new_password"
               id="new_password"
               title="Your new password"
               value="{{ old('new_password') }}"
               placeholder="new password">

        @error('new_password')
        <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
        </span>
        @enderror

        {{-- Password strength meter --}}
        <div id="pwd-container">
            <div class="form-group d-inline-flex">
                <p class="pt-1 pr-2 text-white">strength:</p>
                <div class="pwstrength_viewport_progress"></div>
            </div>
        </div>

    </div>

    <div class="input-group form-group">
        {{-- user new password confirmation prepend image --}}
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-key"></i></span>
        </div>
        {{-- user new password confirmation input area --}}
        <input class="form-control @error('new_password_confirmation') is-invalid @enderror"
               type="password"
               name="new_password_confirmation"
               id="new_password_confirmation"
               title="Confirm your new password"
               value="{{ old('new_password_confirmation') }}"
               placeholder="confirm password">

        @error('new_password_confirmation')
        <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    {{-- Full card width submit button. --}}
    <div class="btn-block btn-group">
        <button type="submit" class="btn float-right login-btn">
            {{ __('Submit') }}
        </button>
    </div>

@endsection

@section('perpagejs')
    <script type="text/javascript" src="{{ asset('js/pwstrength.js') }}"></script>
    <script type="text/javascript">


        // Set focus to the current (old) password field to help user NOT change the email address.
        // Note that email address (not username) is required on this page.

        $('#old_password').focus();

        $(document).ready(function () {
            "use strict";
            var options = {};
            options.ui = {
                container: "#pwd-container",
                showVerdictsInsideProgressBar: true,
                viewports: {
                    progress: ".pwstrength_viewport_progress"
                }
            };
            options.common = {
                debug: true,
                onLoad: function () {
                    $('#messages').text('Start typing password');
                }
            };
            $('#new_password').pwstrength(options);
            $('#current_password').focus();
        });
    </script>
@endsection
