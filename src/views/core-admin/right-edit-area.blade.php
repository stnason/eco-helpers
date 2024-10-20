{{-- The whole rights edit area. --}}

{{-- If adding a new record then leave out the rights grid. --}}
@if(!$form['layout']['when_adding'])


    {{-- FEATURE 1 (or full site_admin) controls access to the rights grid. --}}
    @if ($access->getUserRights()->feature_1 || $role->id == 3)

    <div class="row edit-right">

        <div class="row">

            <div class="col-md">
                <div class="form-group d-inline-flex">

                    @if(isset($form['layout']['buttons']['save']))
                    <div class="me-2">{!! $form['layout']['buttons']['save'] !!}</div>
                    @endif
                    <h3 class="ps-2">Role Permissions by Page</h3>

                    {{-- Set the SITE ADMIN warning class here for the whole row
                         1/8/2024 -- decided to leave this out altogether.
                                     it's now hard set in the controller -- we're only allowing one Admin role.

                    @if ($access->getUserRights()->admin)
                    {!! $control::label(['field_name'=>'site_admin', 'display_name'=>$role, 'errors'=>$errors]) !!}
                    {!! $control::radio(['field_name'=>'site_admin', 'model'=>$role, 'additional_class'=>$form['site_admin_class'], 'errors'=>$errors, 'radio'=>[1=>'Yes', 0=>'No']]) !!}
                    @endif  No set Site Admin Y/N button. --}}

                </div>
            </div>

        </div>




        {{-- FEATURE 1 controls access to the rights grid.
        @if ($access->getUserRights()->feature_1 || !$role->id==3)
        --}}

        {{-- Build the Rights Grid box --}}

        {{--
        /////////////////////////////////////////////////////////////////////////////////////////////
        // Create the Modules editing table area
        /////////////////////////////////////////////////////////////////////////////////////////////
        --}}


        <div class="row mt-2">

            <div class="col-auto">
                <p><strong>Module</strong></p>
                {{-- Top Level Modules dropdown list. --}}
                {!! $control::select([
                       'field_name'=>'frm_module_list',
                       'model'=>$role,
                       'selections'=>$valid::getList('module_list_all'),
                       'preselect'=>$form['module_id'],        // module_id is passed as a parameter from the custom_submit() js
                       'auto_submit'=>'custom_submit()',
                       'add_blank'=>false,
                       'errors'=>$errors]) !!}

            </div>

            <div class="col-auto">
                <p><strong>Active</strong></p>
                {{-- Is this Module Active or not? --}}
                <input  class="css-checkbox" type="checkbox" id="m_bit_view" name="m_bit_view"
                        value="1" {{ $form['m_bit_view']  }}>
                <label  for="m_bit_view" class="css-label"></label>
            </div>

            <div class="col-auto">
                {{-- Submit button for the role copy process. --}}
                <p><strong>Press here to:</strong></p>
                <input class="btn btn-secondary" type="submit" id="copy_from" name="copy_from"
                       value="Copy From">

            </div>

            <div class="col-auto">
                {{-- The role to "copy from" dropdown select. --}}

                <p><strong>this Role's Permissions:</strong></p>
                {!! $control::select([
                        'field_name'=>'copy_role_id',
                        //'model'=>$role,
                        'selections'=>$valid::getList('role_list'),
                        'preselect'=>$form['default_copy_from_role_id'],
                        'auto_submit'=>false,
                        'add_blank'=>false,

                        'errors'=>$errors]) !!}

            </div>

            <div class="col-auto">
                {{-- The right arrown icon and the final "copy-to" role name. --}}
                <p><strong>to this Role: <span class="text-danger">(WARNING! <em>This will <strong>REPLACE</strong> all permissions!)</em></span></strong></p>
                    <i class="copy-from-arrow far fa-arrow-alt-circle-right"></i>
                    <span class="ms-3 text-info">{{ $role->name }}</span>
            </div>

        </div>



        <div class="row">

            {{--
            /////////////////////////////////////////////////////////////////////////////////////////////
            // Create the Page Rights (right grid) editing table area
            /////////////////////////////////////////////////////////////////////////////////////////////
            --}}
            @include ('ecoHelpers.admin.right-grid')

        </div>
    </div>

    @endif {{-- FEATURE_1 Security Check for rights grid. --}}

@endif  {{-- If adding a new record then leave out the rights grid. --}}