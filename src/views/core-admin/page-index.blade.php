@extends('ecoHelpers::core.eh-app-master-template')
@section ('additional-head')
    {{-- specific css to get the Menu & Page List to Display  --}}
    <link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/eh-page-index.css')}}">

    {{-- Disable all of the auto-generated links in the legend display.
         (because the id's are not real and will throw an exception when they don't line up with real page id's)
    --}}
    <style>
    ul#legend {
        pointer-events: none;
        cursor: default;
    }

    ul#page-tree li {
        cursor: move;
    }

    </style>

@endsection ('additional-head')

@section ('main-content')

    <div class="container pt-4 pl-5 pr-5">

        <div class="row">

            {{-- Left hand column (main content) --}}
            <div class="col-md gx-4">
                <h4 class="ms-3">Pages Tree {!! $form['tree_layout_explanation'] !!}</h4>
                {{-- Build the recursive menu structure with the appropriate css formatting --}}
                <ul id="page-tree" class="tree-view">

                    @if (!config('eco-helpers.menus.enabled'))
                        <li>THE MENUS SYSTEM IS NOT ENABLED</li>
                        <li>(you can control this in the eco-helpers config file)</li>
                    @endif

                    @foreach($form['layout']['pages_all'] as $page_item)
                        @include('ecoHelpers::core.eh-child-pages')
                    @endforeach

                </ul>
            </div>

            <div class="col-md gx-4">
                <h4 class="ms-3">Legend {!! $form['tree_layout_explanation'] !!}</h4>
                <ul id="legend" class="tree-view">

                    @foreach($form['layout']['pages_legend'] as $page_item)
                        @include('ecoHelpers::core.eh-child-pages')
                    @endforeach

                </ul>
            </div>

        </div>

    </div>

@endsection ('main-content')

@section ('per-page-js')

    <script type="text/javascript">


        {{-- The update link for saving the page entry data after a successful onscreen drag-n-drop. --}}
        savedataurl = "{{ route('pages.save-drag') }}";

        {{-- Laravel CSRF mechanism. This is needed for the ajax call to match the right token. --}}
        $.ajaxSetup({
            headers: {
                /* REMEMBER: This must be set in the calling page additional head area
                         <meta name="csrf-token" content="{{ csrf_token() }}">	*/
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /**
         * Save the page entry data (through Ajax) after the drag-n-drop operation.
         *
         * @param id        - page id number
         * @param order     - the menu item's order
         * @param parent_id - the item's parent id number
         */
        function saveDrag(id, order, parent_id) {
            $.ajax({
                type: "POST",
                url: savedataurl,
                data: {id: id, order: order, parent_id: parent_id},
                cache: false,
                dataType: 'json',
                success: function(data){
                }
            })
                .done(function(data) {
                    // alert( "SUCCESS  - "+JSON.stringify(data) );
                    // I think we should redraw the page here.
                    // Refresh the page
                    location.reload();

                })
                .fail(function(data) {
                    //alert( "error" + JSON.stringify(data));
                    alert("FAILED:  - "+data.responseText);
                });
        }


        $(document).ready(function () {

            {{--
                https://jqueryui.com/draggable/
                outer ul is "#page-tree"
            --}}

/*
            $( "#draggable" ).draggable({ snap: true });
            $( "#draggable2" ).draggable({ snap: ".ui-widget-header" });
            $( "#draggable3" ).draggable({ snap: ".ui-widget-header", snapMode: "outer" });
            $( "#draggable5" ).draggable({ grid: [ 80, 80 ] });
             $( "#draggable3" ).draggable({ containment: "#containment-wrapper", scroll: false })
 */

            {{-- Set up the outer ul and any decendant ul's as a sortable object.
                 Using this by iteself only allows sorting within each ul
                 Using it with the .draggable below allow (mostly) free-form dragging of even the ul elements.
            --}}
            $( "ul#page-tree, ul#page-tree ul" ).sortable({
                revert: true,        // Goes back to its original position if not dropped in a new position.
                stop: function( event, ui ) {
                    console.log(
                        {{-- Pull the data off the item that just got dragged (this is built in eh-child-pages). --}}
                        "data-page-id: "+ui.item.attr("data-page-id")+"\n"+
                        "data-order: "+ui.item.attr("data-order")+"\n"+
                        "data-parent-id: "+ui.item.attr("data-parent-id")+"\n"
                    );

                    //saveDrag();   // call the saveDrag function after drag-n-drop has stopped.
                }
            });



            {{-- Set up the attributes of the selectable, draggable elements. --}}
            $( "#page-tree li" ).draggable(
                {
                    //grid: [ 20, 20 ],                     // Grid size we want to snap to.
                    containment: "ul#page-tree",            // Don't allow item to move outside of this container.
                    scroll: true,                           // Allow dragging to trigger scrolling when needed (?)
                    connectToSortable: "ul#page-tree, ul#page-tree ul",      // This allows automatic sorting/ inserting when moving. (must be set up above as .sortable)
                    //helper: "original",                   // Used in conjunction with sortable. If set to "clone" it creates a copy of the original item. (original is default)
                    //revert: "invalid",
                    start: function(event, ui){             // This is needed to resize the item after letting it go. (it shrinks to text size on initial grab.)
                        //$(ui.helper).css('width', `${ $(event.target).width() }px`);
                        $(ui.helper).css('width', 'auto');
                    }

                }
            );


            /*
            // Getter
            var connectToSortable = $( ".selector" ).draggable( "option", "connectToSortable" );
            // Setter
            $( ".selector" ).draggable( "option", "connectToSortable", "#my-sortable" );
             */


        });
    </script>

@endsection ('per-page-js')