{{-- Recursively called to build out subsequent levels of drop-down menus.
     The full $menu_item collection must be initially passed from the calling template: eh-navbar-header.

{{-- Made the decision to have NO LINKS for any items that can have children (but might not):
     So, that means "submenus" and "modules" will not link to anything.
     <a class="dropdown-item">{{$menu_item->name}}</a>

     If we want a link then use the same format as the other non-children-bearing menu items:
     <a class="dropdown-item" href="{{config('app.url')}}/{{$menu_item->route}}">{{$menu_item->name}}</a>
       --}}
@if($menu_item->type == "module" || $menu_item->type == "submenu")


    {{-- In the case that main modules or submenus do not have children. --}}
    @if(count($menu_item->children) > 0)
        <li class="dropdown-submenu">               {{-- css will provide the right arrow for the flyout menu. --}}
    @else
        {{-- <li class="dropdown-item">                   THIS INDENTS No right arrow for the flyout menu. --}}
        <li class="dropdown-submenu-no-flyout">     {{-- No right arrow for the flyout menu. --}}
    @endif

        <a class="dropdown-item">{{$menu_item->name}}</a>
        <ul class="dropdown-menu">

        @foreach($menu_item->children as $menu_item)
            @include('ecoHelpers::core.eh-child-menus')
        @endforeach

        </ul>
    </li>
@else
    <li><a class="dropdown-item" href="{{config('app.url')}}/{{$menu_item->route}}">{{$menu_item->name}}</a></li>
@endif
