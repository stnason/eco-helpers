{{--
######################################################################################################################
SECTION 1 - STATICALLY CALLED CSS FILES.
    This is where you include any gloabal site links for css files you want included on every page
    (Assuming you're not using something like npm to do this already)

######################################################################################################################
SECTION 2 - CSS AUTOLOADER SECTION
    Controller allable auto_loaders for js/css that only needs to be included on this page.
    --}}




{{-- ###################################################################################################################### --}}
{{-- SECTION 1 - STATICALLY CALLED CSS FILES. --}}
{{-- ###################################################################################################################### --}}


{{-- A required eco-helpers file that contains the base css for the main page layout and design.
     This can be (carefully) modified for your own use.
    <link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/eh-app-template.css')}}">
     --}}
<link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/eh-app-template.css')}}">





{{-- This controls and styles the multilevel menu dropdowns in the navbar. --}}
<link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/navbar-multilevel.css')}}">

{{-- Sticky footer control. Force the footer to the bottom of the viewport or page - whichever is farther. --}}
<link rel="stylesheet" href="{{asset('vendor/ecoHelpers/css/sticky-footer-navbar.css')}}">

{{-- Original Laravel Fonts
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
--}}

<!-- Latest compiled Bootstrap 5 minified CSS -->
{{-- Local:
<link href="{{ asset('path.BS').'/css/bootstrap.min.css' }}" rel="stylesheet" type="text/css">
--}}
{{-- or CDN --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<!-- Font Awesome - local copy -->
{{-- Local copy
<link rel="stylesheet" type="text/css" href="{{ asset('css/local')}}">
--}}
{{-- or CDN --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">







{{-- ###################################################################################################################### --}}
{{-- SECTION 2 - CSS AUTOLOADER SECTION. --}}
{{-- ###################################################################################################################### --}}

{{-- Sample auto_loaders provided (Remember to configure these in eco-helpers.php
    'auto_loaders'=> [
        1=>'unsaved',
        2=>'datepicker',
        3=>'datetimepicker',
        4=>'help-system',
        5=>'chosen',
        6=>'datatables'
        7=>'textedit',
        8=>'video',
    ]

    {{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #nn
    ///////////////////////////////////////////////////////////////////////////////////////////
    What does this this autoloader do.

@if (isset($form['layout']['auto_load'][nn]))

    // Anything css code or link you want to include here
    // Remember, that the number must be defined in the 'auto_loaders' section of eco-helpers.php config file.
    // Remember, this is set/called in the page controller using:  Layout::setAutoload(nn);

@endif
--}}




{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #2
    ///////////////////////////////////////////////////////////////////////////////////////////
    Date (only) picker. --}}
{{-- ###################################################################################################################### --}}
{{-- Date Picker auto-loader - Remember to set $this->autoload['datepicker'] = true; ; at top of the controller. --}}
{{-- ###################################################################################################################### --}}
@if (isset($form['layout']['auto_load'][2]))
    <!-- JQuery Date Picker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css">
@endif



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #3
    ///////////////////////////////////////////////////////////////////////////////////////////
    jQuery Date/Time picker. --}}
@if (isset($form['layout']['auto_load'][3]))
    <!-- JQuery Date-Time Picker -->
    <link rel = "stylesheet" href = "" >
@endif



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #5
    ///////////////////////////////////////////////////////////////////////////////////////////
    Chosen multi-select. --}}
@if (isset($form['layout']['auto_load'][5]))
    <!-- Chosen multi-select -->
    <link rel="stylesheet" href="">
@endif




{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #6
    ///////////////////////////////////////////////////////////////////////////////////////////
    Datatables. TODO: Remember to include the datatables_init file (name and usage?). --}}
@if (isset($form['layout']['auto_load'][6]))
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

@endif

