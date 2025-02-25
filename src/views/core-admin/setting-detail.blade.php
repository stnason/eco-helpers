{{-- Site Settings detail form  --}}
@extends('ecoHelpers::core.eh-app-template')
@inject('config', 'ScottNason\EcoHelpers\Classes\ehConfig')
@inject('control', 'ScottNason\EcoHelpers\Classes\ehControl')
@inject('access', 'ScottNason\EcoHelpers\Classes\ehAccess')
@inject('valid','App\Classes\ValidList')

@section ('base_head')
@endsection

@section('base_body')

<form class="eh-form-crud" method="post" action="{{ $form['layout']['form_action'] }}">
    @csrf
    @method($form['layout']['form_method'] ?? 'PATCH')

    {{-- ######################################################################## --}}
    {{-- Build out the BUTTON area and enumerate over any possible buttons --}}
    {!! $control::buttonAreaHTML($form['layout']['buttons']) !!}
    {{-- ######################################################################## --}}

    {{-- Only Site Admins have access to lock and unlock the site. --}}
    @if ($access::getUserRights()->admin)
        <div class="row">
            {{-- Add a warning alert around this control if the site is locked --}}
            <div class="pt-2 col-sm {{ $config::get('site_lockout') ? ' bg-warning ' : '' }}">

                <div class="form-group d-inline-flex">

                    {{-- Set the SITE LOCKOUT warning class here for the radio button --}}
                    @php
                        // Set the alert class to surround the radio button with (should match what we're setting on the column div above)
                        $lockout_class = '';
/*
                        if ($config::get('site_lockout')) {
                            $lockout_class = 'alert-warning';
                        }
*/
                    @endphp

                    @if ($access::getUserRights()->admin)
                        {!! $control::label(['field_name'=>'site_lockout', 'display_name'=>$setting, 'errors'=>$errors]) !!}
                        {!! $control::radio(['field_name'=>'site_lockout', 'model'=>$setting, 'additional_class'=>$lockout_class,'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No']]) !!}
                    @endif
                    {{-- This is just the display on this form and has nothing to do with the actual site lockout message,
                        That's controlled in the index_site_locked.blade.php.
                        --}}
                    <p class="m-0 p-0 text-nowrap">{!! ($setting->site_lockout ? ' <strong>Site is currently locked out!</strong>' : '') !!}</p>
                </div>
            </div>
        </div>
    @endif


    {{-- ######################################################################## --}}
    {{-- Standard form information header; for endu-user form content headings. --}}
    {{-- ######################################################################## --}}
    <p class="form-header-information">system banner:</p>

    <div class="row">
        <div class="form-group d-inline flex-wrap">
            <label>&nbsp;</label>
            {{--
            <span class="form-em">Note: leave <strong>System Banner</strong> blank to display the current
            date: <strong>{{ date("l jS \\o\\f F, o") }}</strong></span>
            --}}
            <span class="form-em">Note: leave <strong>System Banner</strong> blank to display the default from eco-helpers.php
                    : <strong>{{ config('eco-helpers.layout.default.banner.content') }}</strong></span>
        </div>
    </div>

    <div class="row">
        <div class="form-group d-inline-flex flex-wrap">
            {!! $control::label(['field_name'=>'system_banner', 'display_name'=>$setting, 'errors'=>$errors]) !!}
            {!! $control::input(['field_name'=>'system_banner', 'model'=>$setting, 'errors'=>$errors, 'additional_class'=>'input-wide']) !!}
        </div>
    </div>

    <div class="row">
        <div class="form-group d-inline-flex">
            {!! $control::label(['field_name'=>'system_banner_blink', 'display_name'=>$setting, 'errors'=>$errors]) !!}
            {!! $control::radio(['field_name'=>'system_banner_blink', 'model'=>$setting, 'errors'=>$errors, 'radio'=>[0=>'No',1=>'Yes'] ]) !!}
        </div>
    </div>


    {{-- ######################################################################## --}}
    {{-- Standard form information header; for endu-user form content headings. --}}
    {{-- ######################################################################## --}}
    <p class="form-header-information">system welcome messages:</p>

    <div class="row">
        <div class="form-group d-inline-flex flex-wrap">
            {!! $control::label(['field_name'=>'message_welcome', 'display_name'=>$setting, 'errors'=>$errors]) !!}
            {!! $control::input(['field_name'=>'message_welcome', 'model'=>$setting, 'errors'=>$errors, 'additional_class'=>'input-wide']) !!}
        </div>
    </div>

    <div class="row">
        <div class="form-group d-inline-flex flex-wrap">
            {!! $control::label(['field_name'=>'message_jumbotron', 'display_name'=>$setting, 'errors'=>$errors]) !!}
            {!! $control::input(['field_name'=>'message_jumbotron', 'model'=>$setting, 'errors'=>$errors, 'additional_class'=>'input-wide']) !!}
        </div>
    </div>

    <div class="row">
        <div class="form-group d-inline-flex flex-wrap">
            {!! $control::label(['field_name'=>'message_copyright', 'display_name'=>$setting, 'errors'=>$errors]) !!}
            {!! $control::input(['field_name'=>'message_copyright', 'model'=>$setting, 'errors'=>$errors, 'additional_class'=>'input-wide']) !!}
        </div>
    </div>


    {{-- ######################################################################## --}}
    {{-- Standard form information header; for endu-user form content headings. --}}
    {{-- ######################################################################## --}}
    <p class="form-header-information">system defaults:</p>

    <div class="row">
        <div class="form-group d-inline-flex flex-wrap">
            {!! $control::label(['field_name'=>'default_time_zone', 'model'=>$setting, 'errors'=>$errors]) !!}
            {!! $control::select([
                    'field_name'=>'default_time_zone',
                    'model'=>$setting,
                    'selections'=>$valid::getList('timezone'),
                    'errors'=>$errors]) !!}
        </div>
    </div>


    {{-- ######################################################################## --}}
    {{-- Standard form information header; for endu-user form content headings. --}}
    {{-- ######################################################################## --}}
    <p class="form-header-information">email defaults:</p>

    <div class="row">

        {{-- Left column of form data. --}}
        <div class="col-sm">
            <div class="form-group d-inline-flex flex-wrap">
                {!! $control::label(['field_name'=>'site_contact_email', 'display_name'=>$setting, 'errors'=>$errors]) !!}
                {!! $control::input(['field_name'=>'site_contact_email', 'model'=>$setting, 'errors'=>$errors]) !!}
            </div>

            <div class="form-group d-inline-flex flex-wrap">
                {!! $control::label(['field_name'=>'site_contact_name', 'display_name'=>$setting, 'errors'=>$errors]) !!}
                {!! $control::input(['field_name'=>'site_contact_name', 'model'=>$setting, 'errors'=>$errors]) !!}
            </div>

            <div class="form-group d-inline-flex flex-wrap">
                <label></label>
                <p class="form-em">Note: <strong>Default From Email</strong> must contain this site's <strong>@domain</strong> name.</p>
            </div>

            <div class="form-group d-inline-flex flex-wrap">
                {!! $control::label(['field_name'=>'default_from_email', 'display_name'=>$setting, 'errors'=>$errors]) !!}
                {!! $control::input(['field_name'=>'default_from_email', 'model'=>$setting, 'errors'=>$errors]) !!}
            </div>

        </div>

        {{-- Right column of form data. --}}
        <div class="col-sm">
            <div class="form-group d-inline-flex flex-wrap">
                {!! $control::label(['field_name'=>'default_from_name', 'display_name'=>$setting, 'errors'=>$errors]) !!}
                {!! $control::input(['field_name'=>'default_from_name', 'model'=>$setting, 'errors'=>$errors]) !!}
            </div>

            <div class="form-group d-inline-flex flex-wrap">
                {!! $control::label(['field_name'=>'default_subject_line', 'display_name'=>$setting, 'errors'=>$errors]) !!}
                {!! $control::input(['field_name'=>'default_subject_line', 'model'=>$setting, 'errors'=>$errors]) !!}
            </div>
        </div>
    </div>



    {{-- ######################################################################## --}}
    {{-- Standard form information header; for end-user form content headings. --}}
    {{-- ######################################################################## --}}
    <p class="form-header-information">authentication settings:</p>

    <div class="row">
        <div class="col-sm">
            <div class="form-group d-inline-flex flex-wrap">
                {!! $control::label(['field_name'=>'days_to_lockout', 'display_name'=>$setting, 'errors'=>$errors]) !!}
                {!! $control::input(['field_name'=>'days_to_lockout', 'model'=>$setting, 'errors'=>$errors]) !!}
            </div>
            <div class="form-group d-inline-flex flex-wrap">
                {!! $control::label(['field_name'=>'failed_attempts_timer', 'display_name'=>$setting, 'errors'=>$errors]) !!}
                {!! $control::input(['field_name'=>'failed_attempts_timer', 'model'=>$setting, 'errors'=>$errors]) !!}
            </div>
        </div>
        <div class="col-sm">
            <div class="form-group d-inline-flex flex-wrap">
                {!! $control::label(['field_name'=>'failed_attempts', 'display_name'=>$setting, 'errors'=>$errors]) !!}
                {!! $control::input(['field_name'=>'failed_attempts', 'model'=>$setting, 'errors'=>$errors]) !!}
            </div>
            <div class="form-group d-inline-flex flex-wrap">
                {!! $control::label(['field_name'=>'logout_timer', 'display_name'=>$setting, 'errors'=>$errors]) !!}
                {!! $control::input(['field_name'=>'logout_timer', 'model'=>$setting, 'errors'=>$errors]) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm">
            <div class="form-group d-inline-flex flex-wrap">
                {!! $control::label(['field_name'=>'minimum_password_length', 'display_name'=>$setting, 'errors'=>$errors]) !!}
                {!! $control::input(['field_name'=>'minimum_password_length', 'model'=>$setting, 'errors'=>$errors]) !!}
            </div>
        </div>
    </div>


    {{-- ######################################################################## --}}
    {{-- Display of software and system version information --}}
    {{-- ######################################################################## --}}
    <div><p class="form-header-security" data-bs-toggle="collapse" data-bs-target=".multi-software-versions">
            software and system versions:</p></div>

    <div class="row collapse multi-software-versions">

        <p><a href="{{route('dev-log')}}">Application and eco-helpers development log history</a></p>

        <div class="form-group d-inline-flex flex-wrap">
            <label>Eco Helpers version</label>
            <input class="form-control input-wide" disabled value="{{ ScottNason\EcoHelpers\Classes\ehConfig::get('eh-app-version') }}">
        </div>

        <div class="form-group d-inline-flex flex-wrap">
            <label>php version</label>
            <input class="form-control input-wide" disabled value="{{ phpversion() }}">
        </div>

        <div class="form-group d-inline-flex flex-wrap">
            <label>webserver</label>
            <input class="form-control input-wide" disabled value="{{ php_uname() }}">
        </div>

        <div class="form-group d-inline-flex flex-wrap">
            <label>server os</label>
            <input class="form-control input-wide" disabled value="{{ $_SERVER['SERVER_SOFTWARE'] }}">
        </div>

        <div class="form-group d-inline-flex flex-wrap">
            <label>webserver ver</label>

            @php
                if(!function_exists('apache_get_version')){
                function apache_get_version(){
                    if(!isset($_SERVER['SERVER_SOFTWARE']) || strlen($_SERVER['SERVER_SOFTWARE']) == 0){
                        return false;
                    }
                    return $_SERVER["SERVER_SOFTWARE"];
                }
            }
            @endphp

            <input class="form-control input-wide" disabled value="{{ apache_get_version() }}">


            @php
                $results = DB::select("select version()");
                $mysql_version =  $results[0]->{'version()'};
            @endphp
            <div class="form-group d-inline-flex flex-wrap">
                <label>db ver</label>
                <input class="form-control input-wide" disabled value="{{ $mysql_version }}">
            </div>


            {{-- Laravel Framework version number. --}}
            <div class="form-group d-inline-flex flex-wrap">
                <label>Laravel</label>
                <input class="form-control input-wide" disabled value="{{ app()->version() }}">
            </div>

            @php
                // Composer version number.
                // $command = '/opt/cpanel/composer/bin/composer -V 2>&1';
                // $command = 'composer -V 2>&1';
                // $command = 'cd '.base_path().' && php composer.phar -V 2>&1';

                // This assumes that Composer is setup to run globally.
               $command = 'composer -V';
               $composer_version = shell_exec($command);
            @endphp
            <div class="form-group d-inline-flex flex-wrap">
                <label>Composer</label>
                <input class="form-control input-wide" disabled value="{{ $composer_version }}">
            </div>

        </div>
    </div>

    {{-- ######################################################################## --}}
    {{-- Standard form information header; for endu-user form content headings. --}}
    {{-- ######################################################################## --}}
    @php($model=$setting)
    @include('ecoHelpers.eh-system-info')
    
</form>

@endsection

@section('base_js')
    <script type="text/javascript">
        $(document).ready(function () {

        });
    </script>
@endsection



