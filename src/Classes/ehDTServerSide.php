<?php

namespace ScottNason\EcoHelpers\Classes;

/**
 * Datatables Server-Side paging processing.
 * Notes:
 *
 *
 *
 *
 */
class ehDTServerSide
{

    protected $draw;                                // Expected from dt ajax.
    protected $row;                                 // Expected from dt ajax.
    protected $rowperpage;                          // Internally created from the dt ajax "length" field.
    protected $columnIndex;                         // Expected from dt ajax.
    protected $columnName;                          // Original sort column name from dt ajax.
    protected $columnSortOrder;                     // Pulled from dt ajax request.
    protected $totalRecords;                        // Total records unfiltered (initial $resultset passed in)
    protected $totalRecordwithFilter;               // Total record count AFTER filtering
    protected $use_fields;                          // $use_field $key=>$value pair array (from controller)
    protected $resultset;                           // The passed data result set for this operation.
    protected $searchValue;                         // The user provided dt search box entry


    /**
     * For standalone testing without an actual call from dt.
     * This creates a expected "dummy" Request() just so we can use the class for development purposes.
     *
     * @return mixed
     */
    protected function dummyRequest() {
        // This is an example of a request()->input() coming from dt:  (note: columns are defined in the dt-init file)

        $tmp = json_decode('
            {
                "draw":"1",
                "columns":
                    [
                    {"data":"id","name":"id","searchable":"true","orderable":"true","search":{"value":null,"regex":"false"}},
                    {"data":"kit_identifier","name":"kit_identifier","searchable":"true","orderable":"true","search":{"value":null,"regex":"false"}},
                    {"data":"contact_or_event","name":"contact_or_event","searchable":"true","orderable":"true","search":{"value":null,"regex":"false"}},
                    {"data":"distribution_date","name":"distribution_date","searchable":"true","orderable":"true","search":{"value":null,"regex":"false"}}
                    ],

                "order":[
                    {
                        "column":"0",
                        "dir":"asc"
                    }
                    ],
                "start":"0",
                "length":"50",
                "search":{"value":"","regex":"false"},
                "narcan_status":"2",
                "_":"1728389009351"
            }', true);

        $tmp2 = Request();
        foreach ($tmp as $key => $value) {

            if (gettype($value) == 'string') {
                $tmp2->merge([$key=>$value]);
            } else {
                $tmp2->merge([$key=>(array)$value]);
            }

        }

        return $tmp2;
    }


    /**
     * The calling controller will be passing these after checking for an ajax request.
     *
     * @param $request              - Request()
     * @param $use_fields           - same $form['layout']['use_fields'] used for the blade template (passed from
     *                              controller)
     * @param $resultset            - Illuminate\\Database\\Eloquent\\Builder
     *                              - pass in the initial $resultset (data collection)
     *
     */
    public function __construct($request, $use_fields, $resultset) {


        // FOR DEVELOPMENT TESTING ONLY. this class without an actual call from dt.
        // $request = $this->dummyRequest();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Pull in all of the needed information passed to us by the dt ajax request.

        // These are in the $request and came from dt (Note: columns were set up in the dt-init using the $use_fields)
        $this->draw = $request->input('draw');                                  // Just goes back out in the response (usually 1) ??
        $this->row = $request->input('start');                                  // Used as: limit ($row, $rowperpage); $row is the offset part of the limit query.
        $this->rowperpage = $request->input('length');                          // Rows to display per paging request.

        if (empty($request->input('order')[0]['column'])) {                     // Column index
            $this->columnIndex = 0;                                             // In some cases it's missing and will cause a crash; so fix that here.
        } else {
            $this->columnIndex = $request->input('order')[0]['column'];
        }

        $this->columnName = $request->input('columns')[$this->columnIndex]['data'];  // Column name (defined in the dt init)
        $this->finalSortColumnName = $this->columnName;                              // Temporary place holder for manipulating the final sortBy name (for relationships and extended searchFilter())

        if (empty($request->input('order')[0]['dir'])) {                        // The 'orderBy' sort order - asc or desc
            $this->columnSortOrder = 'asc';                                     // In some cases it's missing and will cause a crash; so fix that here.
        } else {
            $this->columnSortOrder = $request->input('order')[0]['dir'];
        }

        $this->searchValue = $request->input('search')['value'];                // User input from the dt search box.

        // Passed parameters from the calling controller.
        $this->use_fields = $use_fields;                                        // Same $use_fields list as the index template expects.
        $this->resultset = $resultset;                                          // A query builder instance that defines our starting (baseline) record-set.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Total number of records before any filtering
        $this->totalRecords = $this->resultset->count();

    }

    /**
     * This is where we ensure that any virtual or relationship field is included in the input $resultset.
     *
     * @param $resultset
     * @return mixed
     */
    protected function processInputData($resultset) {

        /*
        foreach ($resultset as $row) {
            // Extend and add virtual or relationship fields here.
            //  $row['date_ordered'] = Order::find($row->order_id)->date_ordered;
        }
        */

        return $resultset;
    }

    /**
     * Boolean check applied to each $record in the getColumnData() method.
     * If no user input is passed (dt search box), then it just returns true.
     *
     * @return bool
     */
    protected function searchFilter($resultset)
    {

        if (!empty($this->searchValue)) {
            return $resultset->filter(
                function ($record, $key) {

                    foreach ($this->use_fields as $field => $label) {
                        if (str_contains(strtolower($record->$field), strtolower($this->searchValue))) {
                            return true;
                        }
                        // Extend and add virtual or relationship fields here.

                    }
                    return false;
                }
            );
        } else {
            return $resultset;
        }

    }

    /**
     * Apply the column sort selected by user interaction on the dt column heading.
     *
     * @param $resultset
     * @return mixed
     */
    protected function sortOrder($resultset) {

        // This will sort by the given field name, but it will also convert this collection to an indexed array
        // that we'll have to undo in the getColumnData() method before passing this back to DT.
        if ($this->columnSortOrder == 'asc') {
            return $resultset->sortBy($this->columnName);
        } else {
            return $resultset->sortByDesc($this->columnName);
        }

    }


    /**
     * Make a result set that MUST! match the $use_field array.
     *
     *  Extend this class and override this method if you need to:
     *      - Deal w/any virtual fields (like contact_or_vendor)
     *      - Format any data; like dates, numbers, dollars...
     *
     * @param $resultset
     * @return array
     */
    protected function getColumnData($resultset) {

        // Undo any "adding of index keys" that would've happened in the sortOrder() method
        // and return a straight array for final response back to the dt ajax call.

        $data = [];
        foreach ($resultset as $key => $record) {
            $one_record = [];

            foreach ($this->use_fields as $field=>$label) {

                // If we don't touch it, every field will just pass through as is.
                $one_record[$field] = $record->$field;

                // Extend and add any formatting or links here.
                /*
                 // Add a link to.
                if ($field == 'kit_identifier') {
                    $one_record[$field] = '<a href="'.route('narcan.show',['narcan'=>$record->id]).'">'. $record->$field . "</a>";
                } else {
                    $one_record[$field] = $record->$field;
                }

                // Pull the ValidList name.
                if ($field == 'location') {
                    if (!empty($record->$field)) {
                        $one_record[$field] = ValidList::getList('narcan_locations')[$record->$field];
                    }
                }

                // Format a date field.
                if ($field == 'date_ordered') {
                    if (!empty($record->$field)) {
                        $one_record[$field] = '<a href="'.route('orders.show',['order'=>$record->order_id]).'">'. $record->$field->format('Y-m-d') . "</a>";
                    }
                }
                 */
            }

            $data[] = $one_record;

        }

        return $data;

    }


    /**
     * Build out and return the ajax response back to dt.
     * - This is where we apply the sort order and user entered filter
     *      (the search filter is actually applied in getColumnData()).
     * @return array
     */
    public function response() {


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 1) Add any virtual or relationship fields to the input $resultset
        $this->resultset = $this->processInputData($this->resultset);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 2) Add the user clicked (or default) column to sort.
        $this->resultset = $this->sortOrder($this->resultset);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 3) Apply the searchFilter from the end-user.
        $this->resultset = $this->searchFilter($this->resultset);

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 4) Get the record count AFTER filtering.
        $this->totalRecordwithFilter = $this->resultset->count();

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 5) Get the final data to return to dt
        // While applying the paging values for offset (row) and limit (rowperpage)
        $data = $this->getColumnData(
            $this->resultset
                ->skip($this->row)                      // DT paging value for the starting row.
                ->take($this->rowperpage)               // DT paging value for how many per page.
        );

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 6) Return the dt fields along with the finalized $resultset ($data)
        // Looks like data tables is getting: {"draw":1,"iTotalRecords":8543,"iTotalDisplayRecords":8543,"aaData":[$results_of_the_query]}
        // For some reason using json_encode() here escapes all double quotes and renders the data in a form not viewable.
        return  [
            "draw" => intval($this->draw),
            "iTotalRecords" => $this->totalRecords,
            "iTotalDisplayRecords" => $this->totalRecordwithFilter,
            "aaData" => $data
        ] ;

    }


}
