@inject('role', 'ScottNason\EcoHelpers\Models\ehRole')
@inject('notifications', 'ScottNason\EcoHelpers\Controllers\ehNotificationsController')
<nav class="navbar navbar-expand-md navbar-dark sticky-top bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/eco"><img src="{{asset('vendor/ecoHelpers/images/eco-helpers_logo-v1.png')}}"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">


                {{-- Some placeholder/ left justified links if needed.
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Link</a>
                </li>
                --}}


                {{-- Menus dropdown section. --}}
                {{-- Don't include the "Menus" dropdown section if there are no menus in the array. --}}
                @if (count($form['layout']['menus']) > 0)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Menus
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            @foreach($form['layout']['menus'] as $menu_item)
                                @include('ecoHelpers::core.eh-child-menus')
                            @endforeach
                        </ul>
                    </li>
                @endif


                {{-- A disabled navbar link.
                <li class="nav-item">
                    <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
                </li>
                --}}


                {{-- Disabled link used to display the .env APP_NAME. --}}
                <li class="nav-item">
                    <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">{{env('APP_NAME')}}</a>
                </li>

            </ul>


            {{-- Login and Register section. --}}
            <ul class="navbar-nav ms-4 me-0 mb-lg-0">
                @auth
                    {{-- User is logged in. --}}

                    {{-- The user notification area. --}}
                    @php
                        $total_notification = $notifications::getTotal()
                    @endphp

                    @if(!empty($total_notification))
                        <li class="nav-item">
                            <button type="button" id="user-notification" class="btn btn-info position-relative me-4">
                                Notifications
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $total_notification ?? 0 }}
                                <span class="visually-hidden">unread messages</span>
                            </span>
                            </button>
                            {{-- live eesfm.com
                            <a id="user-notification" class="nav-link" href="#"><span id="title-bar-badge" class="badge badge-pill badge-warning">{{ $total ?? 0 }}</span>&nbsp;Notifications&nbsp;</a>
                            --}}
                        </li>
                    @endif

                    {{-- The User Role display and dropdown menu. --}}
                    @include('ecoHelpers::core.eh-user-roles')


                    {{-- The logout button.
                    <li class="nav-item"><a class="nav-link" href="{{route('logout')}}">Logout</a></li>
                    --}}

                    {{-- Normal logged in user display and Sign out/ Profile drop-down menu. --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                           aria-haspopup="true" aria-expanded="false">
                            <i class="far fa-user"></i>&nbsp;
                            @if (empty(Auth::user()->first_name))
                                {{ Auth::user()->email }}
                            @else
                                {{ Auth::user()->first_name }}
                            @endif
                            <!--<span class="caret"></span>-->
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ config('app.url') }}/users/{{ Auth::user()->id }}" title="Your user profile.">Profile</a>

                            {{-- /logout does not have (nor should it have) a GET route.
                            <a class="dropdown-item" href="{{ route('logout') }}" title="Log out of the system.">Sign out</a>
                            --}}

                            {{-- POST submit the logout form using jQuery.
                            <a class="dropdown-item" href="javascript:void(0)" onclick='$("#logout-form").submit()'>
                                Logout_1
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            --}}

                            {{-- POST submit the logout form using straight html. --}}
                            <form action="{{ url('logout') }}" method="POST">
                                @csrf
                                <button class="btn-link dropdown-item" type="submit">Sign out</button>
                            </form>

                        </div>
                    </li>


                @else
                    {{-- @auth - User IS NOT logged in. --}}

                    {{--<li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>--}}
                    @if (Route::has('login'))   {{-- Don't include the login link if the login route has not yet been defined. --}}
                        <li><a class="nav-link"
                               style="cursor: pointer"
                               href="{{route('login')}}"
                            >{{ __('Login') }}</a></li>
                    @endif
                    @if (Route::has('register'))
                        <li><a  class="nav-link"
                                style="cursor: pointer"
                                href="{{route('register')}}"
                            >{{ __('Register') }}</a></li>
                    @endif

                @endauth
            </ul>

            <form class="d-flex ms-3">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>

        </div>
    </div>
</nav>

