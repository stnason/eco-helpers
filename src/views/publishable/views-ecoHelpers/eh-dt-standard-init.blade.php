{{-- Standard Datatabels init.

https://datatables.net/download/

REMEMBER; that the js-autoloader with add this to the bottom of the page when 'datatables'
is called for by ehLayou::setAutoload('datatables').

--}}
<script>

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


var datatable_table = $("table[id^='datatable']").DataTable( {

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
    },

@else
    buttons: [],
@endif
scrollY: 400,               // Size of scroll window (when not using pagination)
scrollX: true,              // Needs for Fixed Cols but breaks service cost (too short) - fixed with sScrollXInner
scrollCollapse: true,
paging: false,              // Returns a set number per page and shows page selection buttons on the lower left
sScrollXInner: '100%',
fixedColumns: true,         // doesn't work here (?)
order: [[dtsortcolumn, dtsortdirection]]        // Set these variables in the calling view to override the defaults.
});
</script>