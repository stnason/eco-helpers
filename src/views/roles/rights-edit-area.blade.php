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
                    <div class="mr-2">{!! $form['layout']['buttons']['save'] !!}</div>
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


        <div class="row">
            <div class="col-md">

                {{-- FEATURE 1 controls access to the rights grid.
                @if ($access->getUserRights()->feature_1 || !$role->id==3)
                --}}

                    {{-- Build the Rights Grid box --}}

                    {{--
                    /////////////////////////////////////////////////////////////////////////////////////////////
                    // Create the Modules editing table area
                    /////////////////////////////////////////////////////////////////////////////////////////////
                    --}}

                    <table>
                        <tr class="">
                            <td><p class="mb-0 mt-2"><strong>Module No</strong></p></td>
                            <td class="ps-2"></td>

                            <td><p class="mb-0 mt-2"><strong>Active</strong></p></td>
                            <td><p class="mb-0 mt-2"><strong>Press here to:</strong></p></td>
                            <td><p class="mb-0 mt-2 ps-3"><strong>this Role's Permissions:</strong></p></td>
                            <td><p class="mb-0 mt-2 ps-4"><strong>to this Role: <span class="text-danger">(WARNING! <em>This will <strong>REPLACE</strong> all permissions!)</em></span></strong></p></td>
                        </tr>

                        <tr>
                            {{-- The Module (TOP LEVEL) drop-down select. --}}
                            <td>
                                {!! $control::select([
                                   'field_name'=>'frm_module_list',
                                   'model'=>$role,
                                   'selections'=>$valid::getList('module_list_all'),
                                   'preselect'=>$form['module_id'],        // module_id is passed as a parameter from the custom_submit() js
                                   'auto_submit'=>'custom_submit()',
                                   'add_blank'=>false,
                                   'errors'=>$errors]) !!}
                            </td>
                            <td>
                                &nbsp;
                            </td>
                            {{-- Is this Module (TOP LEVEL) Menu active for this group? --}}
                            <td>
                                <input  class="css-checkbox" type="checkbox" id="m_bit_view" name="m_bit_view"
                                        value="1" {{ $form['m_bit_view']  }}>
                                <label  for="m_bit_view"
                                        class="css-label"></label>
                            </td>

                            <td class="pr-2">
                                <input class="btn btn-secondary" type="submit" id="copy_from" name="copy_from"
                                       value="Copy From">
                            </td>

                            {{-- The Role select for the "Copy ALL From" selection --}}
                            <td>
                                {!! $control::select([
                                    'field_name'=>'copy_role_id',
                                    //'model'=>$role,
                                    'selections'=>$valid::getList('role_list'),
                                    'preselect'=>$form['default_copy_from_role_id'],
                                    'auto_submit'=>false,
                                    'add_blank'=>false,
                                    'additional_class'=>'ms-1',
                                    'errors'=>$errors]) !!}
                            </td>

                            {{-- The right arrow between "Copy From" and the "Copy To" Group. --}}
                            <td>
                                <span class="ms-1 fs-3 copy-from-arrow far fa-arrow-alt-circle-right"></span>
                                <span class="text-info copy-to-group">{{ $role->name }}</span>
                            </td>
                        </tr>

                    </table>
            </div>

            <p class="spacer-line"></p>

        </div>

        <div class="row">

            {{--
            /////////////////////////////////////////////////////////////////////////////////////////////
            // Create the Page Rights (right grid) editing table area
            /////////////////////////////////////////////////////////////////////////////////////////////
            --}}
            @include ('ecoHelpers::roles.rights-grid')

            {{-- @endif {{-- FEATURE_1 Security Check. --}}

        </div>
    </div>

    @endif {{-- FEATURE_1 Security Check for rights grid. --}}

@endif  {{-- If adding a new record then leave out the rights grid. --}}