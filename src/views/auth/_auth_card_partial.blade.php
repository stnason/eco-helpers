{{-- Template form used for all password interactions; login; reset; forgot; change --}}
@extends('ecoHelpers::core.eh-app-template')
@inject('layout', 'ScottNason\EcoHelpers\Classes\ehLayout')

@php
    // Probably not a great MVC practice, but this works better here than having to
    // override all of the various auth controllers showForm() methods.


    $form = [];
    $layout::initLayout();
    $layout::setAll(false);             // Turn off all of the page area displays.

    $layout::setName('Authentication'); // Need the name for the browser tab title,
                                        // But false so it doesn't create a page title on the main template.

    $form['layout'] = $layout::getLayout();
    $form['layout']['form_method'] = 'POST';

    $show_register = false;
    $show_forgot = false;
    $show_ip = false;
    $form_action = '/';

    // Create the title for the card for the various authentication screens.
    $route = Route::currentRouteName();
    switch ($route) {
      case 'login':
        $form['layout']['card_header'] = 'Sign In';
        $show_register = true;
        $show_forgot = true;
        $show_ip = true;
        $form_action = 'login';
        break;
      case 'password.request':
        $form['layout']['card_header'] = 'Password Reset';
        $show_register = false;
        $show_forgot = false;
        $show_ip = true;
         $form_action = 'password.email';
        break;
      case 'password.reset':
        $form['layout']['card_header'] = 'Password Reset';
        $show_register = false;
        $show_forgot = false;
        $show_ip = true;
        $form_action = 'password.store';
        break;
      case 'register':
        $form['layout']['card_header'] = 'Create Account';
        $show_register = false;
        $show_forgot = false;
        $show_ip = true;
        $form_action = 'register';
        break;
      case 'verification.notice':
        $form['layout']['card_header'] = 'Verify Your Email';
        $show_register = false;
        $show_forgot = false;
        $show_ip = true;
        //$form_action = 'verification.notice';
        $form_action = 'verification.send';
        break;
      default:
        $form['layout']['card_header'] = 'Sign Up';
    }

    // Use the card title for page title too.
    // $layout::setName($form['layout']['card_header']);

@endphp


@section('base_head')
    <link rel="stylesheet" type="text/css" href="{{ config('app.url').'/vendor/ecoHelpers/css/login-page.css' }}">
@endsection

@section('base_body')
    {{-- Bootstrap 5.3 card. --}}
    <div class="row d-flex justify-content-center vertical-center">
        <div class="card black-opacity-50">
            <div class="card-header"><h1 class="text-center">{{ $form['layout']['card_header'] }}</h1></div>
            <div class="card-body">

                {{--<form method="POST" action="{{ $form['layout']['form_action'] }}">--}}
                <form method="POST" action="{{route($form_action)}}">
                    @csrf
                    @method($form['layout']['form_method'] ?? 'POST')
                    @yield('auth_card_inside_form')
                </form>

                @yield('auth_card_under_form')

                <div class="card-footer mt-3">
                    @if($show_register)
                    <div class="d-flex justify-content-center">
                        Don't have an account?
                        &nbsp;&nbsp;<a class="link" href="{{ route('register') }}"
                                       title="Register New User">{{ __('Register') }}</a>
                    </div>
                    @endif

                    @if($show_forgot)
                    <div class="d-flex justify-content-center">
                        @if (Route::has('password.request'))
                            <a class="link" href="{{ route('password.request') }}"
                               title="Forgot Password">{{ __('Forgot Your Password?') }}
                            </a>
                        @endif
                    </div>
                    @endif

                    @if($show_ip)
                    <div class="d-flex justify-content-center mt-3">
                        <p class="text-secondary">
                            <small title="Client IP: {{ Request::ip() }}">Client IP: {{ Request::ip() }}</small>
                        </p>
                    </div>
                    @endif

                </div>

            </div>
        </div>
    </div>

@endsection

@section('base_js')

    <script type="text/javascript">
        $(document).ready(function () {
            // Force autofocus on the email input field.
            $('#email').focus()
        });
    </script>

@endsection
