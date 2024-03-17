{{-- User Detail edit for admins only. --
     End users will use the user-profile template to edit their own.
    --}}
@extends('ecoHelpers::core.eh-app-template')
@inject('control', 'ScottNason\EcoHelpers\Classes\ehControl')
@inject('access', 'ScottNason\EcoHelpers\Classes\ehAccess')

@inject('role', 'ScottNason\EcoHelpers\Models\ehRole')
@inject('valid','App\Classes\ValidList')

@section('base_head')
    <style>

        /* Style for the "default" role as indicated by $user->default_role. */
        form.eh-form-crud input.default-role {
            font-weight: bold;
            border-color: green;
        }

        input[id^='role_'] {
            width: 180px;
        }

        /* Adjustment for weird position on just the the first form input field of this group. */
        input#role_1 {
            margin-inline-start: 2px;
        }

        /* Push the whole role group down to line up with the dropdown select to the left. */
        #default_role_group {
            margin-block-start: 2rem;
        }
        #default_role_group input, #default_role_group label, #default_role_group .form-control {
            margin-block-end: 3px;  /* Space between the roles assigned list. */
            height: 31px;           /* Same as the small [x] Remove button beside it. */
        }
        div.tighter {
            margin-block-end: 0;
            margin-block-start: 0;
        }

    </style>
@endsection

@section('base_body')


<form class="eh-form-crud" method="post" action="{{ $form['layout']['form_action'] }}">
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
    <div class="col-sm">

        <div class="row">
            {{-- Left column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex">

                    <label>Profile ID: {{$user->id}}</label>

                </div>
            </div>
            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex">
                    <label>&nbsp;</label>
                    <p class="form-em">(* indicates user is archived)</p>
                </div>
            </div>
        </div>


        <div class="row">
            {{-- Left column of form data. --}}
            <div class="col-sm">
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
            <div class="col-sm">

                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'goto', 'display_name'=>'Go To', 'errors'=>$errors]) !!}
                    {!! $control::select([
                    'field_name'=>'goto',
                    'model'=>$user,
                    'selections'=>$valid::getList('user_list'),
                    'preselect'=>$user->id,
                    'auto_submit'=>true,
                    'errors'=>$errors]) !!}
                </div>

            </div>
        </div>

        {{-- ######################################################################## --}}
        {{-- Standard form information header; for endu-user form content headings. --}}
        {{-- ######################################################################## --}}
        <div><p class="form-header-information">role memberships:</p></div>


        <div class="row">
            {{-- Left column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'role_id', 'display_name'=>'+ Add Role', 'errors'=>$errors]) !!}
                    {!! $control::select([
                    'field_name'=>'role_id',
                    'model'=>$user,
                    'selections'=>$valid::getList('role_list'),
                    //'auto_submit' => true,                        // 2/24/2023 - default behavior of Control is gotoSubmit()
                    'auto_submit' => 'this.form.submit()',
                    'add_blank' => true,
                    'errors'=>$errors]) !!}
                </div>
            </div>

            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group ">

                    {{-- Display the "no group assigned" message. --}}
                    @if (empty($form['my_roles']))
                        <div class="d-inline-flex flex-wrap">
                            <label>&nbsp;</label>
                            <p class="form-em">User has no roles assigned!</p>
                        </div>
                    @endif

                    {{-- Display the "no default role" message. --}}
                    @if (empty($user->default_role))
                        <div class="d-inline-flex flex-wrap">
                            <label>&nbsp;</label>
                            <p class="form-em">User has no default role assigned!</p>
                        </div>
                    @endif


                    {{-- THIS WOULD BE THE PLACE TO RESTRICT THE ABILITY TO SET ROLES TO something like FEATURE_1 or Admin ONLY.
                         Currently, if you have Edit rights, you have everything.

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

                            <div class="d-inline-flex tighter">

                                <div class="row">
                                <div class="col-sm me-2">
                                <label class="{{$default_role}}" for="role_{{$key+1}}">
                                    <a target="_blank" href="{{$link}}">Role {{$key+1}}</a></label>
                                </div>

                                <div class="col-sm">
                                <input id="role_{{$key+1}}" name="role_{{$key+1}}"
                                       class="{{$default_role}} form-control"
                                       value="{{$role::find($role_lookup->role_id)->name}}" readonly>
                                </div>

                                <div class="col-sm p-0">
                                <div class="d-inline-flex">
                                <input
                                       type="radio" name="default_role_group"
                                       value="{{$role_lookup->role_id}}" {{$checked}}>
                                </div>
                                </div>

                                <div class="col-sm">
                                <button
                                        type="button"
                                        class="form-control btn btn-sm btn btn-outline-danger text-nowrap"
                                        data-group-name = "{{$role::find($role_lookup->role_id)->name}}"
                                        data-role-lookup-id="{{$role_lookup->id}}"
                                >[x] Remove
                                </button>
                                </div>
                                </div>

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
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'first_name', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'first_name', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'last_name', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'last_name', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
        </div>


        <div class="row">
            {{-- Left column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'middle_name', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'middle_name', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'nickname', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'nickname', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
        </div>


        <div class="row">
            {{-- Left column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'Full Name', 'display_name'=>null, 'errors'=>$errors]) !!}
                    <input class="form-control" value="{{$user->fullName()}}" readonly>
                </div>
            </div>
            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    <div class="form-group d-inline-flex flex-wrap">
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

{{-- OPTIONAL: Extended business fields
        <div class="row">
            {{-- Left column of form data.
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'title', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'title', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
            {{-- Right column of form data.
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'description', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'description', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Left column of form data.
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'company', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'company', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
            {{-- Right column of form data.
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'reports_to', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'reports_to', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Left column of form data.
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'phone_work_desk', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'phone_work_desk', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
            {{-- Right column of form data.
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'phone_work_cell', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'phone_work_cell', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
        </div>


        <div class="row">
            {{-- Left column of form data.
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'email_work', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'email_work', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
            {{-- Right column of form data.
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">

                </div>
            </div>
        </div>
--}}



        <div class="spacer-line"></div>

        <div class="row">
            {{-- Left column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'phone_personal_home', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'phone_personal_home', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'phone_personal_cell', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'phone_personal_cell', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
        </div>


        <div class="row">
            {{-- Left column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'email_personal', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'email_personal', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'email_alternate', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'email_alternate', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
        </div>

        <div class="spacer-line"></div>

        <div class="row">
            <div class="col-sm">
                {{-- Leaving off the -flex at the end of d-inline causes the label to be above
                the <textarea> but it seems to be the only way I can get the width to be 100%
                and responsive.
                --}}
                <div class="form-group d-inline-flex flex-wrap">
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
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'name', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'name', 'model'=>$user, 'required'=>true,'errors'=>$errors]) !!}
                </div>
            </div>
            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">

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
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'email_verified_at', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'email_verified_at', 'model'=>$user, 'date_long'=>true, 'errors'=>$errors]) !!}
                </div>
            </div>
            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'remember_token', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'remember_token', 'model'=>$user, 'errors'=>$errors]) !!}
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Left column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'password', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'password', 'model'=>$user, 'type'=>'password', 'errors'=>$errors]) !!}
                </div>
            </div>
            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'login_created', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'login_created', 'model'=>$user, 'disabled'=>true,'errors'=>$errors]) !!}
                </div>
            </div>
        </div>


        <div class="row">
            {{-- Left column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {{-- Not sure this is going to be implemented. Haven't found a strong use case under Breeze auth.
                    {!! $control::label(['field_name'=>'force_password_reset', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::radio(['field_name'=>'force_password_reset', 'model'=>$user, 'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No'] ]) !!}
                    --}}
                </div>
            </div>
            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {!! $control::label(['field_name'=>'last_login', 'display_name'=>$user, 'errors'=>$errors]) !!}
                    {!! $control::input(['field_name'=>'last_login', 'model'=>$user, 'disabled'=>true,'errors'=>$errors]) !!}
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Left column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    {{--
                    <label>Clear attempts</label>
                    {!! $control::checkbox(['field_name'=>'clearattempts', 'model'=>$user, 'errors'=>$errors]) !!}
                    --}}
                </div>
            </div>
            {{-- Right column of form data. --}}
            <div class="col-sm">
                <div class="form-group d-inline-flex flex-wrap">
                    <label>User Logins</label>
                    <div class="form-control"><strong>{{ number_format($user->login_count) }}</strong> times since {{ $user->ucreated }}.</div>
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

@endsection


@section('base_js')

    {{-- For the goto functionality you have to define the goto url path and include the js file below --}}
    <script type="text/javascript">
        // Set the url based on the action of this form.
        // But without the id at the end; the js will pull the value of the #goto drop-down select.
        var goto_url = "{{ config('app.url') }}" + '/users';

        // Set the Standard "Delete Me" message
        delete_me_message = "Are you sure you want to permanently delete this User record?\n\n" +
            "This will also delete their Role memberships and any pending notifications. (Consider Archiving them if you're not sure.)";
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
