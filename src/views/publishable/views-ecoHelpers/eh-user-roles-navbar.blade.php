{{-- User Current Role display area -- but only if they have more than one group/role assigned.
                         Does this user have more than one role? --}}

{{-- In case the user has not been assigned a role yet, then skip this whole section. --}}
@if (!empty(Auth()->user()->getActingRole()))

    @if (Auth()->user()->howManyRoles() > 1)
        <li class="nav-item dropdown me-3">
        @php $more_roles = true; @endphp
    @else
        <li class="nav-item me-3">
            @php $more_roles = false; @endphp
            @endif

            {{-- Assign a different class color to any displayed Role Name that is Not your default Role. --}}
            @php
                $class = '';

                if (Auth()->user()->getActingRole()->id != Auth()->user()->default_role) {
                    $class = 'text-warning';
                }

            @endphp


            {{-- Display the "acting role" as the label for the role dropdown list.
                 But if you only have 1 role then skip the dropdown list part of the show.
                 Note: creating an href link to the actual role/show/id route.
                 Note: if we choose to use target="_blank" we'll need an additional check to see if we're already on that route.
                --}}
            @if ($more_roles)
                <a class="nav-link dropdown-toggle {{$class}}"
                   href="{{route('roles.show', [Auth()->user()->getActingRole()->id])}}" role="button"
                   data-bs-toggle="dropdown"
                   aria-haspopup="true" aria-expanded="false">
                    @else
                        {{-- User only has one role assigned so don't put a link on it. --}}
                        <a class="nav-link {{$class}}" href="#" role="button" aria-expanded="false">
                            @endif

                            {{-- The font awesome, generic role icon. --}}
                            <i class="fas fa-users"></i>

                            {{-- Display the currently set "acting_role". --}}
                            {{ Auth()->user()->getActingRole()->name }}
                        </a>


                        {{-- User's Role dropdown menu --}}
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                            {{-- Pull a list of user roles and then remove this user's current "acting role"
                                 from the dropdown list before displaying it.
                                  --}}
                            @php
                                $myRoles = Auth()->user()->getUserRoles();
                                foreach ($myRoles as $key => $value){
                                    if ($value->role_id == Auth()->user()->getActingRole()->id) {
                                        unset($myRoles[$key]);
                                    }
                                }
                            @endphp

                            @foreach ($myRoles as $key=>$myRoleLookup)
                                <li>

                                    {{-- Note: the $myRoles array has no "name" field. It's from the eh_role_lookup table;
                                                so it just has user id and role id.
                                                So we need to pull the real $role to get that name. --}}
                                    <a class="dropdown-item" href="#"
                                       title="{{ $role->find($myRoleLookup->role_id)->name }}"

                                       {{-- Build out a hidden field to POST the requested role id to the back-side.
                                            Note: back-end security checks apply. --}}
                                       onclick="
                                event.preventDefault();
                                myvar = document.createElement('input');
                                myvar.setAttribute('name', 'role');
                                myvar.setAttribute('type', 'hidden');
                                myvar.setAttribute('value', {{$myRoleLookup->role_id}});        // This is the role id
                                document.getElementById('change-role').appendChild(myvar);
                                document.getElementById('change-role').submit();
                    ">
                                      {{$role->find($myRoleLookup->role_id)->name}}</a>
                                </li>
                            @endforeach


                            {{-- Role change is handled as a form submit rather than an ajax call so that
                                 the whole page, header, menus are all refreshed for this role. --}}
                            <form id="change-role" action="{{ route('users.role')}}" method="POST"
                                  style="display: none;">
                                @csrf
                            </form>

                        </ul>


    @endif
