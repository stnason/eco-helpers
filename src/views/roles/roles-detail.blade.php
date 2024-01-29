@extends('ecoHelpers::core.eh-app-template')
@inject('control', 'ScottNason\EcoHelpers\Classes\ehControl')
@inject('access', 'ScottNason\EcoHelpers\Classes\ehAccess')
@inject('valid','App\Classes\ValidList')

@section ('base_head')

    {{-- Get the Menu & Page List styling.  --}}
    <link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/page-list.css')}}">

    <style>
        ul#page-tree {
            /* We need to lock to this to a fixed height for the users display and force it to scrollable. */
            height: 22em;
        }
        /* In the rights grid edit area for the permissions copy to group alignment. */
        .copy-to-group {
            position: absolute;
            margin-top: 2px;
            margin-left: .5em;
        }

        /* These are for the F1 - F4 check boxes on the right grid. */
        table.feature-grid {
            border-collapse: collapse;
            width: 100%;
        }
        table.feature-grid, td.feature-grid-text, td.feature-grid-checkbox, td.feature-grid-label {
            border-style : hidden!important;

            font-size: 0.94em;
            line-height: 1em;
        }
        /* trying to get a line under F1 and F2 -- no luck though
        td.feature-grid-text, td.feature-grid-checkbox, td.feature-grid-label {
            border-style: solid;
            border-bottom: 1px solid darkblue;
        }
         */
        td.feature-grid-label {
            font-weight: bold;
            vertical-align: top;
            text-align: left;
            width: 1em;
        }
        td.feature-grid-checkbox {
            padding-right: .4em;
            vertical-align: top;
            text-align: center;
            width: 1em;
        }
        td.feature-grid-text {
            width: auto;
            text-align: left;
            font-style: italic;
            color: darkgray;
        }

        /* This is the "Remove Selected Users" button below the Users dialog box. */
        button#remove-selected-button {
            width: 89%;
            margin-left: 1.4em;
            margin-right: 1em;
            margin-top: -14px;
            margin-bottom: 4px;
        }

    </style>

@endsection ('base_head')



@section ('base_body')

    <div class="container">
        <form class="form-crud" method="post" action="{{ $form['layout']['form_action'] }}">
            @csrf
            @method($form['layout']['form_method'] ?? 'PATCH')

            {{-- ######################################################################## --}}
            {{-- Build out the BUTTON area and enumerate over any possible buttons ###### --}}
            {!! $control::buttonArea($form['layout']['buttons']) !!}
            {{-- ######################################################################## --}}


            <div class="row">

                <div class="col-md-8">

                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md text-nowrap">
                            <div class="form-group d-inline-flex">
                                <label>Role ID: <strong>{{ $role->id }}</strong></label>
                            </div>
                        </div>
                        {{-- Right column of form data. --}}
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                <label>&nbsp;</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {{-- Don't allow site admin to be disabled. --}}
                                @if(!$role->site_admin)
                                {!! $control::label(['field_name'=>'active', 'display_name'=>$role, 'errors'=>$errors]) !!}
                                {!! $control::radio(['field_name'=>'active', 'model'=>$role, 'alert_if'=>'0', 'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No']]) !!}
                                @endif
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'goto', 'display_name'=>'Go To', 'errors'=>$errors]) !!}
                                {!! $control::select([
                                'field_name'=>'goto',
                                'model'=>$role,
                                'selections'=>$valid::getList('role_list'),
                                'preselect'=>$role->id,
                                'auto_submit'=>'custom_submit()',       // Using a custom js rather than the standard so I can keep both module_id and id in sync.
                                'add_blank'=>false,
                                'errors'=>$errors]) !!}
                            </div>
                        </div>
                    </div>

                    {{-- Standard form information header; for end-user form content headings. --}}
                    <div><p class="form-header-information">role information:</p></div>

                    <div class="row">
                        {{-- Left column of form data. --}}
                        <div class="col-md-6">
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'name', 'display_name'=>$role, 'errors'=>$errors]) !!}
                                {!! $control::input(['field_name'=>'name', 'model'=>$role, 'errors'=>$errors]) !!}
                            </div>
                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'default_home_page', 'display_name'=>$role, 'errors'=>$errors]) !!}
                                {!! $control::select([
                                'field_name'=>'default_home_page',
                                'model'=>$role,

                                //'selections'=>$form['home_page_list'],  // This is a constrained list created in the controller
                                'selections'=>$valid::getList('page_list_active'),
                                //'preselect'=>$role->default_home_page,
                                'auto_submit'=>false,
                                'add_blank'=>true,          // Blank would be the normal "Home" page.
                                'errors'=>$errors]) !!}
                            </div>

                            <div class="form-group d-inline-flex">
                                {!! $control::label(['field_name'=>'restrict_flag', 'display_name'=>$role, 'errors'=>$errors]) !!}
                                {!! $control::radio(['field_name'=>'restrict_flag', 'model'=>$role, 'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No']]) !!}
                            </div>

                        </div>

                        {{-- Right column of form data. --}}
                        <div class="col-md-2">

                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-10">
                            {{-- Leaving off the -flex at the end of d-inline causes the label to be above
                            the <textarea> but it seems to be the only way I can get the width to be 100%
                            and responsive.
                            --}}
                            <div class="form-group d-inline">
                                {!! $control::label(['field_name'=>'description', 'display_name'=>$role, 'errors'=>$errors]) !!}
                                {!! $control::textarea(['field_name'=>'description', 'model'=>$role, 'rows'=>'3', 'errors'=>$errors]) !!}
                            </div>
                        </div>
                    </div>

                </div>


                {{-- Build the Role [ users list --}}
                @if(isset($form['user_list']))
                <div class="col-md-4">
                    <p class="ms-3">
                        <strong>Users</strong> <span class="text-muted">(*not active)</span>
                        <span id="select-all-none" class="ms-4 text-primary">selected (<span id="users_selected">0</span>)</span>
                    </p>
                    <div class="pre-scrollable">
                        <ul id="page-tree" class="tree-view overflow-auto">
                            @foreach ($form['user_list'] as $user)

                                {{-- display an asterisk if they are not Active --}}


                            {{-- TODO: we're going to have to somehow pass both a user id AND a group id. --}}
                                @if ($user->login_active)
                                    <li class="menu-item"><input type="checkbox" value="{{$user->id}}">
                                        <strong><a target="_blank"
                                                   href="{{config('app.url')}}/users/{{$user->id}}">{{$user->name}}</a></strong>
                                    </li>
                                @else
                                    <li class="deactive"><input type="checkbox" value="{{$user->id}}">
                                        <a target="_blank"
                                           href="{{config('app.url')}}/users/{{$user->id}}">*{{$user->name}}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>

                    </div>
                    <button type="button"
                            id="remove-selected-button"
                            class="btn btn-outline-danger btn-sm"
                            data-role-id="{{$role->id}}">Remove Selected Users</button>
                </div>
                @endif


            </div>


            {{-- Rights grid heading area. --}}
            @include ('ecoHelpers::roles.rights-edit-area')


            {{-- Standard form information header; for endu-user form content headings.  --}}
            @php $model = $role @endphp
            @include('ecoHelpers::core.eh-system-info')


        </form>
    </div>

@endsection ('base_body')



@section ('base_js')

    <script type="text/javascript">
        // Goto submit for the Module list dropdown
        goto_url = "{{config('app.url')}}/roles";

        // Set the Standard "Delete Me" message
        delete_me_message = "Are you sure you want to permanently delete this Role?\n\nWARNING: This will also clear any stored access permissions for this Role!";
    </script>


    <script type="text/javascript">

        $(document).ready(function () {

            ///////////////////////////////////////////////////////////////////////////////////////////
            // "Copy From" warning box
            var copy_from_role_name = $("#copy_gsid option:selected").text();
            var copy_to_role_name = '{{ $role->role_name }}';
            $('#copy_from').click(function () {
                // Refresh these variables to current whenever called.
                copy_from_role_name = $("#copy_gsid option:selected").text();
                copy_to_role_name = '{{ $role->role_name }}';
                return confirm("Are you sure you want to Copy from "
                    + copy_from_role_name + " to " + copy_to_role_name
                    + "?\n\nWill completely REPLACE any current permissions for "
                    + copy_to_role_name + "!");
            });

        });


        // This is the <span? to display how many user are selected out of how many total.
        var users_selected = $("#users_selected");
        // This is the initial display of selected / total.
        users_selected.text($(".tree-view input:checkbox:checked").length + "/" + $(".tree-view input:checkbox").length);
        $(".tree-view input:checkbox").click(function() {
            // Then this is the updated users selected / users total after each click on one of the check boxes in the Users box.
            users_selected.text($(".tree-view input:checkbox:checked").length + "/" + $(".tree-view input:checkbox").length);
        });

        // Select all users / select none./
        $("#select-all-none").click(function() {
            // Toggle the check box state from the previous.
            $('.tree-view input:checkbox').prop('checked', !$('.tree-view input:checkbox').prop('checked'));
            // Then update the users selected / users total in the Users box.
            users_selected.text($(".tree-view input:checkbox:checked").length + "/" + $(".tree-view input:checkbox").length);
        });


        // Remove Selected Users button was pressed.
        $("#remove-selected-button").click(function() {

            // No checkboxes are checked.
            if ($(".tree-view input:checkbox:checked").length == 0) {
                alert("No users selected.");
            } else {

                // All checkboxes for the users are checked
                var confirm_message = "";
                if ($(".tree-view input:checkbox:checked").length == $(".tree-view input:checkbox").length) {
                    confirm_message = "You are about to remove ALL of the users from this role. Are you sure?";
                } else {
                    confirm_message = "This is going to remove the selected users ("+$(".tree-view input:checkbox:checked").length+") from this role. Are you sure?";
                }

                if ((confirm(confirm_message))) {

                    // Initialize the array of user_id and role_id.
                    var deletion_array = [];

                    {{-- Loop through all of the selected (checked) users and build an array for use when deleting them. --}}
                    //$(".tree-view input:checkbox:checked").each(function () { // For some reason this doesn't work to loop the checked boxes. (?)
                    $(".tree-view input:checkbox").each(function () {           // This does work to loop all checkboxes, though.
                       if (this.checked) {

                           // "this.value" is the user id from this checkbox value.
                           {{-- Build out the array to pass to the back-end; --}}
                           deletion_array.push({'user_id': this.value, 'role_id': {{$role->id}}});

                       }
                    });

                    {{-- If any were checked then call the api on the array. --}}
                    {{-- Call the remove users api --}}
                    // Delete the actual role by id number (back-end permissions will apply).
                    // alert('deleting here: '+ JSON.stringify(deletion_array));
                    removeUsersFromRole(deletion_array);

                }   // If confirm the deletions.
            }       // If any checkboxes are checked or not.
        });         // #remove-selected-button clicked.

        /**
         * Function to remove an individual user from this Role
         * This is expecting a deletion_array {user_id: user_id, role_id: role_id}
         */
        function removeUsersFromRole(deletion_array) {
                $.ajaxSetup({
                    headers: {
                        {{-- Laravel CSRF mechanism. This is needed for the ajax call to match the right token. --}}
                        /* REMEMBER: This must be set in the calling page additional head area
                                 <meta name="csrf-token" content="{{ csrf_token() }}">	*/
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // Ajax call URL call to the backend.
                var url = "{{config('app.url')}}/delete-user-from-role";
                $.ajax({
                    type: "delete",
                    url: url,
                    data: {'deletion_array':deletion_array}
                }).done(function () {
                    // Refresh the page to remove the deleted checkboxes.
                    // No luck setting the flash on the back-end. And no luck getting a count of records deleted here.
                    // And this one here display all the time -- not just on .done.
                    {{-- {{session()->flash('message','Selected users successfully removed from this role.')}} --}}
                    location.reload();      // Refresh the page to remove the deleted item and refresh the flash message.
                });
        }



        /**
         * Using a custom goto (rather than the default which just redirects to this record's id)
         * We need to add a parameter that is the module number currently selected for view.
         *
         * Note: this does have to be outside of the $(document).ready(function () in order to work.
         */
        function custom_submit() {
            // This works when redirecting (GET) to a show for a new id on this controller
            var id = $('#goto').val();
            var module_id = $('#frm_module_list').val();

            if (typeof module_id == 'undefined') {
                $(location).attr('href', goto_url + '/' + id);
            } else {
                $(location).attr('href', goto_url + '/' + id + '?module_id=' + module_id);
            }
        }

    </script>


@endsection ('base_js')