{{--
######################################################################################################################
SECTION 1 - STATICALLY CALLED JAVASCRIPT FILES.
    This is where you include any gloabal site links for js files you want included on every page
    (Assuming you're not using something like npm to do this already)

######################################################################################################################
SECTION 2 - JS AUTOLOADER SECTION
    Controller allable autoloaders for js/css that only needs to be included on this page.
    --}}



{{-- ###################################################################################################################### --}}
{{-- SECTION 1 - STATICALLY CALLED JAVASCRIPT FILES. --}}
{{-- ###################################################################################################################### --}}

{{-- Latest version of jquery & bootstrap js --}}
{{-- Local copies:
<script type="text/javascript" src="{{ config('path.JQ') }}/jquery.js"></script>
<script type="text/javascript" src="{{ config('path.BS') }}/js/bootstrap.js"></script>
--}}
{{-- CDN copies: --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

{{--
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js" integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous"></script>
--}}





{{-- ###################################################################################################################### --}}
{{-- SECTION 2 - JS AUTOLOADER SECTION. --}}
{{-- ###################################################################################################################### --}}

{{-- Sample autoloaders provided (Remember to configure these in eco-helpers.php
    'autoloaders'=> [
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
    What does this autoloader do?

    @if (isset($form['layout']['auto_load'][nn]))
        <script type="text/javascript">
            // Anything js code or link you want to include here
            // Remember, that the number must be defined in the 'autoloaders' section of eco-helpers.php config file.
            // Remember, this is set/called in the page controller using:  Layout::setAutoload(nn);
        </script>
    @endif
--}}




{{-- TODO: I feel like this should be moved into a "base" template somewhere so it can't be accidently removed. --}}
{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #1
    ///////////////////////////////////////////////////////////////////////////////////////////
    Catch any form change and display an Unsaved changes message. --}}
@if (isset($form['layout']['auto_load'][1]))
    <script type="text/javascript">
        $("form.form-crud").change(function () {

            // Update the system flash message on any form change.

            {{--TODO: this is using the permissions system to check for ADD or EDIT rights.
                Use new getUserRights() ?
            --}}
            {{-- @if($form['right']['ADD'] || $form['right']['EDIT']) --}}
            @if (true)
            $('#layout-page-flash').html('You have <strong>unsaved</strong> changes.');
            @endif

        });
    </script>
@endif



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #2
    ///////////////////////////////////////////////////////////////////////////////////////////
    Date (only) picker. --}}
@if (isset($form['layout']['auto_load'][2]))
    <!-- JQuery Date Picker -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

    {{-- Install the date picker handlers
    Note: these are created in each action_form_vars file by a call to createDatePickerFields($this->t);
    --}}
    {{--
    <script type="text/javascript">
        $(function() {
            {{ form.datepickerjs | raw }}
        });
    </script>
    --}}
@endif



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #3
    ///////////////////////////////////////////////////////////////////////////////////////////
    Date/Time picker. --}}
@if (isset($form['layout']['auto_load'][3]))
    <!-- JQuery Date Time Picker -->
    <script type="text/javascript" src="{{ config('path.DTPICKER') }}/jquery.datetimepicker.full.js"></script>
    {{-- Install the date time picker handlers
    Note: these are created in each action_form_vars file by a call to createDatePickerFields($this->t);
    --}}
    {{--
    <script type="text/javascript">
        $(function() {
            {{ form.datepickerjs | raw }}
        });
    </script>
    --}}
@endif



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #4
    ///////////////////////////////////////////////////////////////////////////////////////////
    Popup Help System. --}}
@if (isset($form['layout']['auto_load'][4]))
    <!-- PopUp Help System -->
    {{-- {{ form.helpsystemjs | raw }} --}}
@endif



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #5
    ///////////////////////////////////////////////////////////////////////////////////////////
    Chosen multi-select. --}}
@if (isset($form['layout']['auto_load'][5]))
    <!-- Chosen functions-->
    <script type="text/javascript" src="{{ config('path.CHOSEN') }}/chosen.jquery.js"></script>
    <script type="text/javascript" class="init">
        // Cosen multi-select function setup
        var config = {
            '.chosen-select': {},
            '.chosen-select-deselect': {allow_single_deselect: true},
            '.chosen-select-no-single': {disable_search_threshold: 10},
            '.chosen-select-no-results': {no_results_text: 'Oops, nothing found!'},
            '.chosen-select-width': {width: "95%"}
        }
        for (var selector in config) {
            $(selector).chosen(config[selector]);
        }
    </script>

@endif



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #6
    ///////////////////////////////////////////////////////////////////////////////////////////
    Datatables. --}}
@if (isset($form['layout']['auto_load'][6]))
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

        // Initialize the Datatables object

        // Set the default sort column
        if (typeof dtsortcolumn === "undefined") {
            dtsortcolumn = 0;
        }

        // Set the default sort direction
        if (typeof dtsortdirection === "undefined") {
            dtsortdirection = "asc";
        }

        // Set the default sort direction
        if (typeof dtrowgroup === "undefined") {
            var dtrowgroup = "0";
        }

        /*zero config
            $(document).ready(function() {
                $('#datatable').dataTable()
            } );
         */

        /* server-side
            $(document).ready(function() {
                $('#datatable').dataTable( {
                    "processing": true,
                    "serverSide": true,
                    "ajax": "<?php echo config('app.url');?>/media_content/datatables-old/server_processing.php"
            } );
        } );
      */


        /* fixed heading-columns */
        $(document).ready(function () {

            {{-- var usefields = {{ form.usefields | json_encode | raw }}; --}}

                    {{-- Using the JQuery "starts with" selector to target multiple on each page as needed. --}}
                    {{-- var table = $("#datatable").DataTable({ --}}

                    @include('ecoHelpers.dt-standard-init')

            /*
            $("table[id^='datatable']").DataTable( {

                // dom: 'B<"clear">lfrtip',
                // dom: 'Bfrtip',
                dom: 'Bfrtip',

                @if ($form['dtrowgroup'] ?? false)
            rowGroup: {
                dataSrc: dtrowgroup
            },
@endif

            {{-- Does this user have the rights to show the Save button --}}
            @if ($form['layout']['right']['SEC_EXPORT_DISPLAYED'] ?? false)

            buttons: {
                dom: {
                    button: {
                        tag: 'button',
                        className: ''       // This removes the default datatables button class so the below can override it.
                    }
                },
                buttons: [                  // I have no idea why the buttons are justifying the images differently but the spaces are need to faux center.
                    {
                        extend: 'copy',
                        className: 'btn btn-sm btn-outline-secondary',
                        titleAttr: 'Copy to Clipboard',
                        text: '&nbsp;&nbsp;&nbsp;<i class="far fa-copy"></i>&nbsp;&nbsp;&nbsp;'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm btn-outline-secondary',
                        titleAttr: 'Print Preview',
                        text: '&nbsp;&nbsp;&nbsp;<i class="fas fa-print"></i>'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-sm btn-outline-secondary',
                        titleAttr: 'Export a CSV file',
                        text: '&nbsp;&nbsp;&nbsp;<i class="fas fa-file-csv"></i>'
                        // filename: '',         // set a static file name like "export"
                        // extension: '.csv'     // Already default to csv so only if you need something different.
                    }
                ]
            },


@else
            buttons: [],
@endif
            scrollY: 400,           // Size of scroll window (when not using pagination)
            scrollX: true,          // Needs for Fixed Cols but breaks service cost (too short) - fixed with sScrollXInner
            scrollCollapse: true,
            paging: false,
            sScrollXInner: '100%',
            fixedColumns: true,     // doesn't work here (?)
            order: [[dtsortcolumn, dtsortdirection]]
        });
        */

        });

    </script>
@endif



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #7
    ///////////////////////////////////////////////////////////////////////////////////////////
    TinyMCE rich text editor. --}}
@if (isset($form['layout']['auto_load'][7]))
    <script type="text/javascript" src="{{ config('path.TINYMCE')}}/tinymce.js"></script>
    <script type="text/javascript" class="init">

        // Calling page can set any of these vars ahead of time to specify the toolbar to use
        if (typeof toolbarsetup === "undefined") {
            toolbarsetup = "undo redo | bold italic | bullist link";
        }
        if (typeof menubarsetup === "undefined") {
            menubarsetup = false;
        }
        if (typeof contentcsssetup === "undefined") {
            contentcsssetup = false;
        }
        if (typeof pluginssetup === "undefined") {
            pluginssetup = 'link paste';
        }
        if (typeof content_height === "undefined") {
            content_height = '280';
        }

        tinymce.init({
            selector: '#texteditor',
            //content_style: ".mce-content-body {font-size:.8em;color:darkgrey;}",
            content_style: ".mce-content-body {font-size:.8em;}",
            theme: "silver",
            toolbar: toolbarsetup,
            menubar: menubarsetup,
            contentcsssetup: contentcsssetup,
            plugins: pluginssetup,
            height: content_height,
            default_link_target: '_blank',
            paste_data_images: true,
            importcss_append: true,
            branding: false,
            relative_urls: false
        });
    </script>
@endif



{{--///////////////////////////////////////////////////////////////////////////////////////////
    AUTOLOADER #8
    ///////////////////////////////////////////////////////////////////////////////////////////
    Video popup. --}}
@if (isset($form['layout']['auto_load'][8]))
    <!-- Video PopUp functions-->
    <script type="text/javascript" src="{{ config('path.JS') }}/media_popup.js"></script>
@endif


