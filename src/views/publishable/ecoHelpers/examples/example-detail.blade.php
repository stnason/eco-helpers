{{-- Site Settings detail form  --}}
@extends('ecoHelpers::core.eh-app-master-template')
@inject('control', 'ScottNason\EcoHelpers\Classes\ehControl')
@inject('valid', 'App\Classes\ValidList')
@inject('access', 'ScottNason\EcoHelpers\Classes\ehAccess')

@section ('additional-head')
    <style>

    </style>
@endsection

@section('main-content')

    {{--<div class="spacer-line"></div>--}}

    <div class="container">
        <form class="eh-form-crud" method="post" action="{{ $form['layout']['form_action'] }}">
            @csrf
            @method($form['layout']['form_method'] ?? 'PATCH')

            {{-- ######################################################################## --}}
            {{-- Build out the BUTTON area and enumerate over any possible buttons --}}
            {!! $control::buttonAreaHTML($form['layout']['buttons']) !!}
            {{-- ######################################################################## --}}


            {{-- ######################################################################## --}}
            {{-- Standard form information header; for end-user form content headings. --}}
            {{-- ######################################################################## --}}
            <div><p class="form-header-information">system banner:</p></div>

            <div class="row">
                {{-- Left column of form data. --}}
                <div class="col-md text-nowrap mb-3">
                    <div class="form-group d-inline-flex">
                        <label>Role ID: <strong>{{ $example->id }}</strong></label>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Left column of form data. --}}
                <div class="col-md">
                    <div class="form-group d-inline-flex flex-wrap">
                        {!! $control::label(['field_name'=>'active', 'display_name'=>$example, 'errors'=>$errors]) !!}
                        {!! $control::radio(['field_name'=>'active', 'model'=>$example, 'alert_if'=>'0', 'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No']]) !!}
                    </div>
                </div>
                {{-- Right column of form data. --}}
                <div class="col-md">
                    <div class="form-group d-inline-flex flex-wrap">
                        {{-- Leave out the GOTO dropdowns when adding a new record. --}}
                        @if(!$form['layout']['when_adding'])
                            <div class="form-group d-inline-flex flex-wrap">

                                @php
                                    // If needed, you can supply different lists based on permissions.
                                    if ($access::getUserRights()->feature_1) {
                                    $list = 'example_list';
                                    } else {
                                    $list = 'example_list';
                                    }
                                @endphp

                                {!! $control::label(['field_name'=>'goto', 'display_name'=>'Go To', 'errors'=>$errors]) !!}
                                {!! $control::select([
                                'field_name'=>'goto',
                                'model'=>$example,
                                'selections'=>$valid::getList($list),
                                'preselect'=>$example->id,
                                'auto_submit'=>true,
                                'errors'=>$errors]) !!}

                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Left column of form data. --}}
                <div class="col-md">
                    <div class="form-group d-inline-flex flex-wrap">
                        {!! $control::label(['field_name'=>'archived', 'display_name'=>$example, 'errors'=>$errors]) !!}
                        {!! $control::radio(['field_name'=>'archived', 'model'=>$example, 'alert_if'=>1, 'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No']]) !!}
                    </div>
                </div>
                {{-- Right column of form data. --}}
                <div class="col-md">
                   <label></label>
                </div>
            </div>


            {{-- ######################################################################## --}}
            {{-- Standard form information header; for endu-user form content headings. --}}
            {{-- ######################################################################## --}}
            <div><p class="form-header-information">a standard information header:</p></div>


            <div class="row">
                {{-- Left column of form data. --}}
                <div class="col-md">
                    <div class="form-group d-inline-flex flex-wrap">
                        {!! $control::label(['field_name'=>'name', 'display_name'=>$example, 'errors'=>$errors]) !!}
                        {!! $control::input(['field_name'=>'name', 'model'=>$example, 'errors'=>$errors]) !!}
                    </div>
                </div>
                {{-- Right column of form data. --}}
                <div class="col-md">
                    <div class="form-group d-inline-flex flex-wrap">
                        <label>&nbsp;</label>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Left column of form data. --}}
                <div class="col-md">
                    <div class="form-group d-inline-flex flex-wrap">

                        {{-- Create the link some other page. --}}
                        @php $link = config('app.url').'/examples/'.$example->id; @endphp

                        {{-- Or you can create a "mailto" link. --}}
                        @php
                            $link = '';
                            if ($example->email) {$link = "mailto:".$example->email;}
                        @endphp

                        {!! $control::label(['field_name'=>'email', 'display_name'=>$example, 'link'=>$link, 'errors'=>$errors]) !!}
                        {!! $control::input(['field_name'=>'email', 'model'=>$example, 'errors'=>$errors]) !!}

                    </div>
                </div>
                {{-- Right column of form data. --}}
                <div class="col-md">
                    <div class="form-group d-inline-flex flex-wrap">
                        {!! $control::label(['field_name'=>'birthdate', 'display_name'=>$example, 'errors'=>$errors]) !!}
                        {!! $control::input(['field_name'=>'birthdate', 'model'=>$example, 'additional_class'=>'datepicker', 'errors'=>$errors]) !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm">
                    <div class="form-group d-inline flex-wrap">
                        {!! $control::label(['field_name'=>'bio', 'display_name'=>$example, 'errors'=>$errors]) !!}
                        {!! $control::textarea(['field_name'=>'bio', 'model'=>$example, 'rows'=>'3', 'errors'=>$errors]) !!}
                    </div>
                </div>
            </div>


            {{--
                Display/Hide the protected/ confidential information if you do or don't have Feature_1 access.
            @if ($form['right']['SEC_FEATURE_1'])
                --}}

                {{-- Standard form information header; for endu-user form content headings. --}}
                <p class="form-header-security">FEATURE_1 Security controlled by each template.</p>

                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex flex-wrap">
                            {!! $control::label(['field_name'=>'address', 'display_name'=>$example, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'address', 'model'=>$example, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex flex-wrap">
                            {!! $control::label(['field_name'=>'city', 'display_name'=>$example, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'city', 'model'=>$example, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex flex-wrap">
                            {!! $control::label(['field_name'=>'state', 'display_name'=>$example, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'state', 'model'=>$example, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex flex-wrap">
                            {!! $control::label(['field_name'=>'zip', 'display_name'=>$example, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'zip', 'model'=>$example, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>


            {{--
            @endif
            --}}




            {{-- ######################################################################## --}}
            {{-- Standard form information header; for endu-user form content headings. --}}
            {{-- ######################################################################## --}}
            @php($model=$example)
            @include('ecoHelpers.eh-system-info')

        </form>
    </div>

    <script type="text/javascript">
        // When using TinyMCE, the calling page can set these vars ahead of time to specify
        // any of the toolbars and plugins for TinyMCE to use:
        toolbarsetup = "undo redo | bold italic | styleselect | bullist link image | code removeformat fullscreen anchor";
        menubarsetup = "tools table view insert edit";
        pluginssetup = 'lists advlist link paste image code fullscreen anchor';
    </script>

@endsection

@section('per-page-js')

    {{-- For the goto functionality you have to define the goto url path and include the js file below --}}
    <script type="text/javascript">

        // Set the url based on the action of this form.
        // But without the id at the end; the js will pull the value of the #goto drop-down select.

        {{--  This one is proving more difficult to convert to a route() since the {parameter} is added later by js. --}}
        var goto_url = "{{ config('app.url') }}" + '/examples';

        // Set the Standard "Delete Me" message
        delete_me_message = "Are you sure you want to permanently delete this Example record?\n\nNOTE: This custom message is set at the bottom of the example-detail blade template.";
    </script>
    <script type="text/javascript" src="{{ asset('vendor/ecoHelpers/js/eh-goto-submit.js') }}"></script>


    <!-- Load per page js -->
    <script type="text/javascript">

        $(document).ready(function () {

            // Note: the datepicker is instantiated in the main app template so no need on individual pages.
            // Just remember to include the 'additional_class'=>'datepicker' in the Control above.
            // $(".datepicker").datepicker();

        });
    </script>
@endsection



