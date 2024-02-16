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
    <script   src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"
              integrity="sha256-xLD7nhI62fcsEZK2/v8LsBcb4lG7dgULkuXoXB/j91c="
              crossorigin="anonymous"></script>

    {{-- Install the date picker handlers
         Remember that any element that needs a data picker will need the
          'additional_class'=>'datepicker' added.
    --}}
    <script type="text/javascript">
    $(".datepicker").datepicker();
    </script>


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

        /* fixed heading-columns */
        $(document).ready(function () {

            {{-- var usefields = {{ form.usefields | json_encode | raw }}; --}}

            {{-- This uses the JQuery "starts with" selector to target multiple on each page as needed. --}}
            {{-- var table = $("table[id^='datatable']").DataTable({ --}}
            @include('ecoHelpers.dt-standard-init')

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


