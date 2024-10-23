{{-- Standard Datatabels init for server-side processing.

https://datatables.net/download/


REMEMBER: That the js-autoloader uses the default init defined in eco-helpers.layout.datatables_default_init
or a the one you specify in the call to ehLayout::setAutoload('datatables','my-init-file').

NOTE: This requires the $form['layout']['use_fields'] in order to build out the dt columns list./

You can use this init file directly as long as you don't need to modify any of the initializaiton parameters
or add any custom fields (the case when posting something from the page like a $filter variable) - in that
case you'll need to copy this file and create the custom init to include on your resrouce-index page template.

USE THIS eh-dt-ajax-init AS A STARTING POINT.

 /* simple server-side usage.
    $(document).ready(function() {
       $('#datatable').dataTable( {
       "processing": true,
       "serverSide": true,
       "ajax": script_name
       });
    });
 */
--}}

<script>

    // Check for option variables from the calling template.
    // Set the default sort column -- can be set in the calling template.
    if (typeof dtsortcolumn === "undefined") {
        dtsortcolumn = 0;
    }

    // Set the default sort direction -- can be set in the calling template.
    if (typeof dtsortdirection === "undefined") {
        dtsortdirection = "asc";
    }

    // Set the default sort group -- can be set in the calling template.
    if (typeof dtrowgroup === "undefined") {
        var dtrowgroup = "0";
    }

    var dt_processing = false;
    var dt_serverSide = false;
    var dt_ajax = "";

    {{-- dt_server_side_process must be defined in the calling template in order for ajax to initialize properly. --}}
    if (typeof dt_server_side_process !== "undefined") {
        dt_processing = true;
        dt_serverSide = true;
        dt_ajax = dt_server_side_process;

        // Initialize Laravel ajax
        $.ajaxSetup({
            headers: {
                /* REMEMBER: This must be set in the calling page additional head area
                <meta name="csrf-token" content="{{ csrf_token() }}">	*/
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    } else {
        {{-- We can't initialize ajax w/o the dt_server_side_process url, so just turn it off here. --}}
        dt_server_side_process = '/ajax-url-is-missing';
        dt_processing = false;
        dt_serverSide = false;
        dt_ajax = null;
        alert('WARNING 243: Ajax URL is missing. Please define dt_server_side_process in the calling template.');
    }

    // Initialize the Datatables object
    var datatable_table = $("table[id^='datatable']").DataTable({

        /*dom: 'B<"clear">lfrtip',*/
        /*dom: 'Bfrtip',*/
        dom: 'Bfrtip',


        @if ($form['dtrowgroup'] ?? false)
        rowGroup: {
            dataSrc: dtrowgroup
        },
        @endif

        {{-- Does this user have the rights to show the Save button --}}
        @inject('access', 'ScottNason\EcoHelpers\Classes\ehAccess')
        @if ($access::getUserRights()->export_displayed ?? false)

        layout: {
            top1Start: {

                buttons: {
                    dom: {
                        button: {
                            tag: 'button',
                            className: ''                   // This removes the default datatables button class so the below can override it.
                        }
                    },

                    buttons: [                      // I have no idea why the buttons are justifying the images differently but the spaces are needed to faux center.
                        {
                            extend: 'copy',
                            className: 'btn btn-sm btn-outline-secondary',
                            titleAttr: 'Copy to Clipboard',
                            text: '&nbsp; <i class="far fa-copy"></i> &nbsp;'
                        },
                        {
                            extend: 'print',
                            className: 'btn btn-sm btn-outline-secondary',
                            titleAttr: 'Print Preview',
                            text: '&nbsp; <i class="fas fa-print"></i> &nbsp;'
                        },
                        {
                            extend: 'csv',
                            className: 'btn btn-sm btn-outline-secondary',
                            titleAttr: 'Export a CSV file',
                            text: '&nbsp <i class="fas fa-file-csv"></i> &nbsp;'
                            // filename: '',         // set a static file name like "export"
                            // extension: '.csv'     // Already default to csv so only if you need something different.
                        }
                    ]
                }

                @else
                buttons: [],
                @endif
            }},

        processing: dt_processing,
        serverSide: dt_serverSide,
        ajax: {
            url: dt_server_side_process,

            // Include any custom data for the back-end $request-ajax() test section (if needed);
            //  Eaxample wouild be the narcan query select radio button status so we know which class to initialize.
            data: function (d) {
                // d.myKey = 'myValue';
                // d.narcan_status = $('input[name="narcan_status"]:checked').val();
            }

        },

        // Build out the dt column definitions from the $use_fields array that must be supplied by the controller.
        // (not sure if dt needs both the data and name but their example showed it so including both just in case)
        columns: [
            @foreach($form['layout']['use_fields'] as $field=>$label)
            {
                'data': '{{$field}}', 'name': '{{$field}}'
            },
            @endforeach
            /*
            {'data':'kit_identifier', 'name':'kit_identifier'},
            {'data':'date_ordered', 'name':'date_ordered'},
            {'data':'lot_number', 'name':'lot_number'},
            {'data':'expiration_date', 'name':'expiration_date'},
            {'data':'date_received', 'name':'date_received'},
            {'data':'location', 'name':'location'}
            */
        ],

        scrollY: 400,                               // Size of scroll window (when not using pagination)
        scrollX: true,                              // Needs for Fixed Cols but breaks service cost (too short) - fixed with sScrollXInner
        scrollCollapse: true,
        paging: true,                               // Returns a set number per page and shows page selection buttons on the lower left
        pageLength: 25,                             // How many records to page out at a time from the ajax request.
        sScrollXInner: '100%',
        fixedColumns: true,                         // doesn't work here (?)
        order: [[dtsortcolumn, dtsortdirection]]    // Can set these variables in the calling view to override the defaults.
    });

</script>
