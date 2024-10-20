{{-- The main system Login screen. --}}
@extends('auth._auth_card_partial')

@section('auth_card_inside_form')

    {{-- Include the code for the Google ReCaptcha widget below.
    https://significanttechno.com/how-to-add-google-recaptcha-to-laravel-forms          (bloated validation)
    https://www.cloudways.com/blog/use-recaptcha-laravel-forms-validation/#controller   (simple validation)
        --}}
    <script async src="https://www.google.com/recaptcha/api.js"></script>

    <p class="text-warning">
        @if ($errors->any())
            @error('email') {{ $errors->first('email') }} @enderror
            @error('name') {{ $errors->first('name') }} @enderror
            @error('password') {{ $errors->first('password') }} @enderror
            @error('password') {{ $errors->first('password_confirm') }} @enderror
            @error('g-recaptcha-response') {{ $errors->first('g-recaptcha-response') }} @enderror
        @elseif(!empty(session('status')))
            {{session('status')}}
        @else
            &nbsp;  {{-- Placeholder for any error message. --}}
        @endif
    </p>


    <div class="input-group form-group">

        {{-- user email input area. Note: putting this first since it already gets focus in all auth views. --}}
        <div class="input-group">
            {{-- user email prepend image --}}
            <span class="input-group-text" id="basic-addon1"><i class="fa-regular fa-envelope"></i></span>
            <input class="form-control {{ $errors->has('first_name') || $errors->has('last_name') || $errors->has('email') ? ' is-invalid' : '' }}"
                   type="text"
                   name="email"
                   id="email"
                   value="{{ old('email') }}"
                   placeholder="your email address"
                   required autocomplete="off" autofocus>
        </div>

        <span class="invalid-feedback" role="alert">
            <strong>{{ ($errors->first('first_name') ?: $errors->first('last_name')) ?: $errors->first('email') }}</strong>
        </span>

    </div>


    <div class="input-group form-group">

        {{-- user first name input area --}}
        <div class="input-group mt-3">
            {{-- user first name prepend image --}}
            <span class="input-group-text" id="basic-addon1"><i class="fas fa-user"></i></span>
            <input class="form-control {{ $errors->has('first_name') || $errors->has('last_name') || $errors->has('email') ? ' is-invalid' : '' }}"
                   type="text"
                   name="first_name"
                   id="first_name"
                   value="{{ old('first_name') }}"
                   placeholder="enter your first name"
                   required autocomplete="off" autofocus>
        </div>

        {{-- user last name input area --}}
        <div class="input-group mt-3">
            {{-- user last name prepend image --}}
            <span class="input-group-text" id="basic-addon1"><i class="fas fa-user"></i></span>
            <input class="form-control {{ $errors->has('first_name') || $errors->has('last_name') || $errors->has('email') ? ' is-invalid' : '' }}"
                   type="text"
                   name="last_name"
                   id="last_name"
                   value="{{ old('last_name') }}"
                   placeholder="enter your last name"
                   required autocomplete="off" autofocus>
        </div>


        <span class="invalid-feedback" role="alert">
            <strong>{{ ($errors->first('first_name') ?: $errors->first('last_name')) ?: $errors->first('email') }}</strong>
        </span>

    </div>



    <div class="input-group form-group">

        {{-- user password input area --}}
        <div class="input-group mt-3">
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

        {{-- user password confirmation input area --}}
        <div class="input-group mt-3">
            {{-- user password prepend image --}}
            <span class="input-group-text" id="basic-addon1"><i class="fas fa-key"></i></span>
            <input class="form-control @error('password') is-invalid @enderror"
                   type="password"
                   name="password_confirmation"
                   id="password_confirmation"
                   value="{{ old('password-confirm') }}"
                   placeholder="re-enter password"
                   autocomplete="off">
        </div>

    </div>

    {{-- Google Recaptcha Widget --}}
    <div class="g-recaptcha mt-3 d-flex justify-content-center form-control" data-sitekey={{config('services.recaptcha.key')}}></div>

    {{-- Full card width login submit button. --}}
    <div class="d-grid gap-2 mt-3">
        <button class="btn btn-secondary" type="submit">{{ __('Submit') }}</button>
    </div>

@endsection
