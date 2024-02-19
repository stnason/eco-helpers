{{--
 * The auto-load file associated with the eco-helpers config file array
 * entry for 'auto_loaders' => [6 => 'name']
 *
 *  Usage (available in the controller as):
 *     ehLayout::setAutoload('name'); or ehLayout::setAutoload(6);
 *
 --}}



{{--///////////////////////////////////////////////////////////////////////////////////////////
    Datatables. TODO: Remember to include the datatables_init file (name and usage?). --}}
    {{--

// 1.10.10 combined files
// TODO: they've changed their delivery system and folder structure - I have some pages with the DT initialization hard coded.
//echo '<link rel="stylesheet" type="text/css" href="{{ constant('DT_DIR_URL') }}media/css/datatables.min.css">'
// 1.10.7
--}}
<!-- Datatables Local copies -->
{{--  SEE THE standard_footer FILE FOR INFORMAIOTN WHAT TO CHOOSE WHEN USING THE DATATABLE DOWNLOAD BUILDER. --}}

{{--
    https://cdn.datatables.net
    https://datatables.net/download/
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/dataTables.jqueryui.min.css">
    --}}
<link href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.13.3/b-2.3.5/b-html5-2.3.5/b-print-2.3.5/fc-4.2.1/datatables.min.css" rel="stylesheet"/>


{{--
<link rel="stylesheet" type="text/css" href="{{ config('path.DT') }}/extensions/Buttons/css/buttons.dataTables.css">
<link rel="stylesheet" type="text/css" href="{{ config('path.DT') }}/extensions/FixedColumns/css/fixedColumns.dataTables.css">
--}}

{{-- HOSTED COPIES
{{-- The plain "Datatable package seems to work best (better than either Bootstrap 4 or JQuery)
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.0/css/buttons.dataTables.min.css"/>
{{-- Buttons extensions
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css"/>
--}}


