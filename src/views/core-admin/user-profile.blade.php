{{-- User Profile detail form (for admins) -- Not the same as My Account (for end-users)--}}
@extends('ecoHelpers::core.eh-app-template')
@inject('control', 'ScottNason\EcoHelpers\Classes\ehControl')
@inject('access', 'ScottNason\EcoHelpers\Classes\ehAccess')

@inject('role', 'ScottNason\EcoHelpers\Models\ehRole')
@inject('valid','App\Classes\ValidList')


{{-- ######################################################################## --}}
{{-- TODO: this needs to be modifed to be the actual "user" profile
           that users will use to edit their own profile. --}}
{{-- ######################################################################## --}}

@section('base_head')
    <style>
        /* Per page styling only. Specific to just this page. */

        /* Style for the "default" role as indicated by $user->default_role. */
        form.form-crud input.default-role {
            font-weight: bold;
            border-color: green;
        }

        /* Adjust the default role radio button's position in between the input control and the remove button. */
        .default-role-radio {

            margin-top: 6px;        /* Distance from the top of the default_role control.
                                    /* 13px for Safari but Chrome needs it to be 0. */
                                    /* Had to move the <input> into its own <div> to get this to be closer. Still not 100% the same. */
            margin-left: 6px;       /* Distance from the right side of the default_role control. */
            margin-right: 2px;      /* Set the visual distance to the Remove button on the right. */

        }

        form.form-crud .form-control.form-textarea {
            /* This is set in the base-template.css file, but changed here to match the specific page layout. */
            /*width: 72%;

            max-width: available; */
        }

    </style>
@endsection


@section('base_body')

    <div class="container">
        <form class="form-crud" method="post" action="{{ $form['layout']['form_action'] }}">
            @csrf
            @method($form['layout']['form_method'] ?? 'PATCH')

            {{-- ######################################################################## --}}
            {{-- Build out the BUTTON area and enumerate over any possible buttons ###### --}}
            {!! $control::buttonAreaHTML($form['layout']['buttons']) !!}
            {{-- ######################################################################## --}}


            {{-- Keep these fields alive in the POSTed $request --}}
            <input name="default_role" id="default_role" value="{{$user->default_role}}" hidden>
            <input name="acting_role" id="acting_role" value="{{$user->acting_role}}" hidden>


            {{-- ######################################################################## --}}
            {{-- Main profile form header and Go-To. --}}
            {{-- ######################################################################## --}}
            <div class="col-md">

                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">

                            <label>Profile ID: {{$user->id}}</label>

                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            <label>&nbsp;</label>

                        </div>
                    </div>
                </div>



                {{-- ######################################################################## --}}
                {{-- Standard form information header; for endu-user form content headings. --}}
                {{-- ######################################################################## --}}
                <div><p class="form-header-information">some heading:</p></div>


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'first_name', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'first_name', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'last_name', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'last_name', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>



                {{-- ######################################################################## --}}
                {{-- Standard form information header; for endu-user form content headings. --}}
                {{-- ######################################################################## --}}
                <div><p class="form-header-information">som other heading :</p></div>


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'first_name', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'first_name', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'last_name', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'last_name', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>



                <div class="spacer-line"></div>


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'phone_personal_home', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'phone_personal_home', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'phone_personal_cell', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'phone_personal_cell', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>


                <div class="spacer-line"></div>



                {{-- ######################################################################## --}}
                {{-- Standard form information header; for end-user form content headings. --}}
                {{-- ######################################################################## --}}
                <div><p class="form-header-information">yet another header:</p></div>


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'email_verified_at', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'email_verified_at', 'model'=>$user, 'date_long'=>true, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'remember_token', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'remember_token', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>


                {{-- ######################################################################## --}}
                {{-- Standard form information header; for endu-user form content headings. --}}
                {{-- ######################################################################## --}}
                @php($model=$user)
                @include('ecoHelpers.eh-system-info')


            </div>
        </form>
    </div>

@endsection


@section('base_js')

    <script type="text/javascript">

        $(document).ready(function () {

        });

    </script>
@endsection
