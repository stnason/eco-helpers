{{-- The main system Login screen. --}}
@extends('publishable.auth._auth_card_partial')

@section('auth_card_inside_form')

    <p class="text-warning">
        @if ($errors->any())
            {{$errors->first()}}
            {{--
            @error('email') {{ $errors->first('email') }} @enderror
            @error('name') {{ $errors->first('name') }} @enderror
            @error('password') {{ $errors->first('password') }} @enderror
            --}}
        @else
            &nbsp;  {{-- Placeholder for any error message. --}}
        @endif
    </p>

    <div class="input-group form-group">

        {{-- user name input area --}}
        <div class="input-group">
            {{-- user name prepend image --}}
            <span class="input-group-text" id="basic-addon1"><i class="fas fa-user"></i></span>
            <input class="form-control {{ $errors->has('name') || $errors->has('email') ? ' is-invalid' : '' }}"
                   type="text"
                   name="email"
                   id="email"
                   value="{{ old('email') }}"
                   placeholder="username or email"
                   required autocomplete="off" autofocus>
        </div>

        <span class="invalid-feedback" role="alert">
    <strong>{{ $errors->first('name') ?: $errors->first('email') }}</strong>
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
                   value="{{ old('password') }}" {{-- Note: That neither password, nor password_confirmation return an old value.
                                                           This is specific to Laravel's Exception/Handler.php and done to keep the browser from caching that value. --}}
                   placeholder="password"
                   autocomplete="off">
        </div>

    </div>

    {{-- Full card width login submit button. --}}
    <div class="d-grid gap-2 mt-3">
        <button class="btn btn-secondary" type="submit">{{ __('Login') }}</button>
    </div>

@endsection

