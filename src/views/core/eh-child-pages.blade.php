{{-- Note: This is basically the same as the child-menus template
            but used exclusivley on the Page List and Page Detail pages.
            It leaves out the dropdown menu styling.

            Recursively called to build out the levels of the whole page tree. (all active, non-active scripts, etc.)
            $page_item must initially be passed from the calling template:
            In this case either the page_list or page_detail.

    Note: All of the display class checking has been moved into the PageController@addDisplayClass();
            !! So this template is expecting the ['display_class'] key to be added before the page data gets here !!

            --}}

@php
    // Note, that is this is called from page_list, the $page variable is not set so these checks are skipped.
    // These classes are defined in eh-page-index.css and used for highlighting the menu tree sidebar with page we're on and it's parent.
    // Is this $page_item the same as the one we're editing now?
    $selected_page = '';
    if (isset($page) && $page->id == $page_item->id) {
        $selected_page = "selected-page";
    }

    // Is this $page_item the parent of the page we're editing now?
    if (isset($page) && $page->parent_id == $page_item->id) {
        // Note: included the whole id= here since a blank id shows up without the quotes in the final html render.
        $selected_page = 'id="parent-page"';
    }

    // Format the list name for this menu item.
    // Order.ID-Name
    $display_name = $page_item->order.'.'.$page_item->id.'-'.$page_item->name;

@endphp


@if(count($page_item->children) > 0)

    {{-- Note: data-* is making this data available to the draggable object in js. --}}
    <li data-page-id = "{{$page_item->id}}"
        data-parent-id = "{{$page_item->parent_id}}"
        data-order = "{{$page_item->order}}"
        {{$selected_page}} class="{{$page_item->display_class}}"><a href="{{config('app.url')}}/pages/{{$page_item->id}}">{{$display_name}}</a></li>
    <ul class="tree-view">
        @foreach($page_item->children as $page_item)
            @include('ecoHelpers::core.eh-child-pages')
        @endforeach
    </ul>

@else

    <li data-page-id = "{{$page_item->id}}"
        data-parent-id = "{{$page_item->parent_id}}"
        data-order = "{{$page_item->order}}"
        {{$selected_page}} class="{{$page_item->display_class}}"><a href="{{config('app.url')}}/pages/{{$page_item->id}}">{{$display_name}}</a></li>

@endif
