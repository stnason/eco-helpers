{{--
 * The autoload file associated with the eco-helpers config file array
 * entry for 'auto_loaders' => [6 => 'name']
 *
 *  Usage (available in the controller as):
 *     ehLayout::setAutoload('name'); or ehLayout::setAutoload(6);
 *
 --}}



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #6
    ///////////////////////////////////////////////////////////////////////////////////////////
    Datatables. --}}

    <!-- Datatables functions-->

    {{-- HOSTED COPIES
    {{-- The plan "Datatable package seems to work best (better than either Bootstrap 4 or JQuery)
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.20/datatables.min.js"></script>
    {{-- Buttons extensions
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
    --}}

    {{-- Datatables Local copies.
    Remember: go to https://datatables.net/download/
    and select
    1. DataTables  ( for some reason get bad results with Bootstrap 4 - prefer the default )
    2. DataTables
    Extensions
        - Buttons
        - Column visibility
        - HTML5 export
        - Print view
        - FixedColumns
        - RowGroup
    --}}


    {{--
       https://cdn.datatables.net
       https://datatables.net/download/
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.3/js/dataTables.jqueryui.min.js"></script>
       --}}

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.13.3/b-2.3.5/b-html5-2.3.5/b-print-2.3.5/fc-4.2.1/datatables.min.js"></script>


    {{--
    <script type="text/javascript" src="{{ config('path.DT') }}extensions/FixedColumns/js/dataTables.fixedColumns.js"></script>
    <script type="text/javascript" src="{{ config('path.DT') }}extensions/TableTools/js/dataTables.tableTools.js"></script>


    <script type="text/javascript" src="{{ config('path.DT') }}extensions/Buttons/js/dataTables.buttons.js"></script>
    <script type="text/javascript" src="{{ config('path.DT') }}extensions/Buttons/js/buttons.flash.js"></script>
    <script type="text/javascript" src="{{ config('path.DT') }}extensions/Buttons/js/buttons.print.js"></script>
    <script type="text/javascript" src="{{ config('path.DT') }}extensions/Buttons/js/buttons.html5.js"></script>
    --}}

    <script type="text/javascript" class="init">

        /* fixed heading-columns */
        $(document).ready(function () {

            {{-- var usefields = {{ form.usefields | json_encode | raw }}; --}}

            {{-- This uses the JQuery "starts with" selector to target multiple on each page as needed. --}}
            {{-- var table = $("table[id^='datatable']").DataTable({
            @include('ecoHelpers.eh-dt-standard-init') --}}

        });

    </script>


{{-- If an autoload $parameter is specified then see if we can include that template name. --}}
@inject('ehConfig', 'ScottNason\EcoHelpers\Classes\ehConfig')
@if(View::exists('ecoHelpers.'.$auto_load))
    @include('ecoHelpers.'.$auto_load)
@else
    {{-- Otherwise attempt to include the default dt-inti specified in the config file. --}}
    @includeIf('ecoHelpers.'.$ehConfig::get('datatables_default_init'))
@endif