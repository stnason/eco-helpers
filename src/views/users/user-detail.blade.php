{{-- User Profile detail form (for admins) -- Not the same as My Account (for end-users)--}}
@extends('ecoHelpers::core.eh-app-template')
@inject('control', 'ScottNason\EcoHelpers\Classes\ehControl')
@inject('access', 'ScottNason\EcoHelpers\Classes\ehAccess')

@inject('role', 'ScottNason\EcoHelpers\Models\ehRole')
@inject('valid','App\Classes\ecoHelpers\ValidList')


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


{{-- ######################################################################## --}}
{{-- TODO: Right now this is intended for admin use only with appropriate privledges.
        Will have to add a user only version and a possible admin-restricted version. --}}
{{-- ######################################################################## --}}



@section('base_body')

    <div class="container">
        <form class="form-crud" method="post" action="{{ $form['layout']['form_action'] }}">
            @csrf
            @method($form['layout']['form_method'] ?? 'PATCH')

            {{-- ######################################################################## --}}
            {{-- Build out the BUTTON area and enumerate over any possible buttons ###### --}}
            {!! $control::buttonArea($form['layout']['buttons']) !!}
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
                            <p class="form-em">(* indicates user is archived)</p>
                        </div>
                    </div>
                </div>


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'login_active', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::radio(['field_name'=>'login_active', 'model'=>$user, 'alert_if'=>'0', 'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No'] ]) !!}
                        </div>
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'archived', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::radio(['field_name'=>'archived', 'model'=>$user, 'alert_if'=>'1', 'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No'] ]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">

                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'goto', 'display_name'=>'Go To', 'errors'=>$errors]) !!}
                            {!! $control::select([
                            'field_name'=>'goto',
                            'model'=>$user,
                            'selections'=>$valid::getList('user_list'),
                            'preselect'=>$user->id,
                            'auto_submit'=>true,
                            'errors'=>$errors]) !!}
                        </div>

                        <div class="form-group d-inline-flex">

                        </div>

                    </div>
                </div>

                {{-- ######################################################################## --}}
                {{-- Standard form information header; for endu-user form content headings. --}}
                {{-- ######################################################################## --}}
                <div><p class="form-header-information">role memberships:</p></div>


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'role_id', 'display_name'=>'+ Add Role', 'errors'=>$errors]) !!}
                            {!! $control::select([
                            'field_name'=>'role_id',
                            'model'=>$user,
                            'selections'=>$valid::getList('role_list'),
                            //'auto_submit' => true,                        // 2/24/2023 - default behavior of Control is gotoSubmit()
                            'auto_submit' => 'this.form.submit()',
                            'errors'=>$errors]) !!}
                        </div>
                    </div>

                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group ">

                            {{-- Display the "no group assigned" message. --}}
                            @if (empty($form['my_roles']))
                                <div class="d-inline-flex">
                                    <label>&nbsp;</label>
                                    <p class="form-em">User has no roles assigned!</p>
                                </div>
                            @endif

                            {{-- Display the "no default role" message. --}}
                            @if (empty($user->default_role))
                                <div class="d-inline-flex">
                                    <label>&nbsp;</label>
                                    <p class="form-em">User has no default role assigned!</p>
                                </div>
                            @endif



                            {{-- TODO: Decision: SHOULD THIS RESTRICT THE ABILITY TO SET ROLES TO something like FEATURE_1 or Admin ONLY?
                            Or if you have Edit you have everything ??
                            @if ($access::getUserRights()->admin) --}}

                            <fieldset id="default_role_group">

                                {{-- Skip the my_roles dropdown when adding a new record. --}}
                                @if (!$form['layout']['when_adding'])

                                @foreach($form['my_roles'] as $key=>$role_lookup)

                                    @php
                                        // Set the class to highlight the default group.
                                        $default_role = '';
                                        $checked = '';
                                        if ($user->default_role == $role_lookup->role_id) {
                                            $default_role = 'default-role';
                                            $checked = 'checked';
                                        }
                                        $link = config('app.url').'/roles/'.$role_lookup->role_id;
                                    @endphp

                                    <div class="d-inline-flex">

                                        <label class="{{$default_role}}" for="role_{{$key+1}}">
                                            <a target="_blank" href="{{$link}}">Role {{$key+1}}</a></label>

                                        <input id="role_{{$key+1}}" name="role_{{$key+1}}"
                                               class="{{$default_role}} form-control"
                                               value="{{$role::find($role_lookup->role_id)->name}}" readonly>

                                        <div class="default-role-radio">
                                        <input  type="radio" name="default_role_group"
                                               value="{{$role_lookup->role_id}}" {{$checked}}>
                                        </div>

                                        <button
                                                type="button"
                                                class="delete-role ms-1 mb-1 btn btn-outline-secondary btn-sm"
                                                data-group-name = "{{$role::find($role_lookup->role_id)->name}}"
                                                data-role-lookup-id="{{$role_lookup->id}}"
                                        >[x] Remove
                                        </button>
                                    </div>
                                @endforeach
                                @endif

                            </fieldset>
                            {{--  @endif --}}

                        </div>
                    </div>
                </div>



                {{-- ######################################################################## --}}
                {{-- Standard form information header; for endu-user form content headings. --}}
                {{-- ######################################################################## --}}
                <div><p class="form-header-information">user information:</p></div>


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


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'middle_name', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'middle_name', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'nickname', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'nickname', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'Full Name', 'display_name'=>null, 'errors'=>$errors]) !!}
                            <input class="form-control" value="{{$user->fullName()}}" readonly>
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'timezone', 'display_name'=>$user, 'errors'=>$errors]) !!}
                                {!! $control::select([
                                'field_name'=>'timezone',
                                'model'=>$user,
                                'selections'=>$valid::getList('timezone'),
                                'errors'=>$errors]) !!}
                            </div>
                        </div>
                    </div>
                </div>


                <div class="spacer-line"></div>


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'title', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'title', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'description', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'description', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'company', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'company', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'reports_to', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'reports_to', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>


                <div class="spacer-line"></div>


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'phone_work_desk', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'phone_work_desk', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'phone_work_cell', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'phone_work_cell', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>

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


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'email_work', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'email_work', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'email_personal', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'email_personal', 'model'=>$user, 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>

                <div class="spacer-line"></div>

                <div class="row">
                    <div class="col-md-9">
                        {{-- Leaving off the -flex at the end of d-inline causes the label to be above
                        the <textarea> but it seems to be the only way I can get the width to be 100%
                        and responsive.
                        --}}
                        <div class="form-group d-inline">
                            {!! $control::label(['field_name'=>'comments', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::textarea(['field_name'=>'comments', 'model'=>$user, 'rows'=>'3', 'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>



                {{-- ######################################################################## --}}
                {{-- Standard form information header; for end-user form content headings. --}}
                {{-- ######################################################################## --}}
                <div><p class="form-header-information">login information:</p></div>

                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'name', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'name', 'model'=>$user, 'required'=>true,'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">

                            {{-- When adding a new record we will require either the personal or work email.
                                 the dataConsistency check will pick one and then it can be changed later here.
                                --}}
                            @if(!$form['layout']['when_adding'])

                            @php
                                // Build the Registered email link for the label
                                $link = '';
                                if ($user->email) {
                                    $link = "mailto:".$user->email;
                                }
                            @endphp

                            {!! $control::label(['field_name'=>'email', 'link'=>$link, 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::select([
                            'field_name'=>'email',
                            'model'=>$user,
                            'selections'=>[
                                $user->email=>$user->email,
                                $user->email_work=>$user->email_alternate,
                                $user->email_personal=>$user->email_personal,
                                ],
                            'errors'=>$errors]) !!}

                             @endif
                        </div>
                    </div>
                </div>

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

                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'password', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'password', 'model'=>$user, 'type'=>'password', 'errors'=>$errors]) !!}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'login_created', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'login_created', 'model'=>$user, 'disabled'=>true,'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>


                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {{-- Not sure this is going to be implemented. Haven't found a strong use case under Breeze auth.
                            {!! $control::label(['field_name'=>'force_password_reset', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::radio(['field_name'=>'force_password_reset', 'model'=>$user, 'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No'] ]) !!}
                            --}}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {!! $control::label(['field_name'=>'last_login', 'display_name'=>$user, 'errors'=>$errors]) !!}
                            {!! $control::input(['field_name'=>'last_login', 'model'=>$user, 'disabled'=>true,'errors'=>$errors]) !!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Left column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            {{--
                            <label>Clear attempts</label>
                            {!! $control::checkbox(['field_name'=>'clearattempts', 'model'=>$user, 'errors'=>$errors]) !!}
                            --}}
                        </div>
                    </div>
                    {{-- Right column of form data. --}}
                    <div class="col-md">
                        <div class="form-group d-inline-flex">
                            <label>User Logins</label>
                            <span
                                    class="form-em pt-2"><strong>{{ number_format($user->login_count) }}</strong> times since {{ $user->ucreated }}.</span>
                        </div>
                    </div>
                </div>


                {{-- ######################################################################## --}}
                {{-- Standard form information header; for endu-user form content headings. --}}
                {{-- ######################################################################## --}}
                @php($model=$user)
                @include('ecoHelpers::core.eh-system-info')


            </div>
        </form>
    </div>

@endsection


@section('base_js')
    '

    {{-- For the goto functionality you have to define the goto url path and include the js file below --}}
    <script type="text/javascript">
        // Set the url based on the action of this form.
        // But without the id at the end; the js will pull the value of the #goto drop-down select.
        var goto_url = "{{ config('app.url') }}" + '/users';

        // Set the Standard "Delete Me" message
        delete_me_message = "Are you sure you want to permanently delete this User record?";
    </script>
    <script type="text/javascript" src="{{ asset('vendor/ecoHelpers/js/eh-goto-submit.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {

            {{-- ######################################################################## --}}
            {{-- Manage the "remove" role button delete message and action.  --}}
            {{-- ######################################################################## --}}
            $(".delete-role").click(function () {

                if (confirm("Are you sure you want to remove this role: " + $(this).attr("data-group-name") + "?")) {

                    // Delete the actual role by id number (back-end permissions will apply).
                    {{-- Laravel CSRF mechanism. This is needed for the ajax call to match the right token. --}}
                    $.ajaxSetup({
                        headers: {
                            /* REMEMBER: This must be set in the calling page additional head area
                                     <meta name="csrf-token" content="{{ csrf_token() }}">	*/
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    // Setup the ajax call URL using the role id data attribute from the delete button.
                    var url = "{{config('app.url')}}/delete-role-from-user/" + $(this).attr("data-role-lookup-id");
                    $.ajax({
                        type: "delete",
                        url: url,
                        //data: {"group_name": $(this).attr("data-group-name")} // moved this logic into the Controller@destroy
                    }).done(function () {
                        location.reload();      // refresh the page to remove the deleted item and refresh the flash message.
                    });

                }

            });


        });
    </script>
@endsection
