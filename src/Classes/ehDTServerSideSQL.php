<?php

namespace ScottNason\EcoHelpers\Classes;

use App\Classes\ValidList;
use Illuminate\Support\Facades\DB;
use function App\Classes\Request;

/**
 * Datatables Server-Side paging processing.
 * Notes:
 *
 *
 *
 *
 */
class ehDTServerSideSQL
{

    protected $draw;                                // Expected from dt ajax.
    protected $row;                                 // Expected from dt ajax.
    protected $rowperpage;                          // Internally created from the dt ajax "length" field.
    protected $columnIndex;                         // Expected from dt ajax.
    protected $columnName;                          // Original sort column name from dt ajax.
    protected $columnSortOrder;                     // Pulled from dt ajax request.
    protected $totalRecords;                        // Total records unfiltered (initial $dataset passed in)
    protected $totalRecordwithFilter;               // Total record count AFTER filtering
    protected $use_fields;                          // $use_field $key=>$value pair array (from controller)
    protected $field_list;                          // The key=>value pair array for the alias to field_name or subquery
    protected $joins = '';                          // When defining custom fields; include any joins that may be needed.
    protected $dataset;                             // A complete SQL query for the starting dataset.
    protected $dataset_query;                       // Fully formed sql query for the final dataset.
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
     * @param $dataset_query        - a string containing the initial sql query for the starting dataset.
     *                              - pass in the initial $dataset (data collection)
     *
     */
    public function __construct($request, $use_fields, $dataset_query) {


        ///////////////////////////////////////////////////////////////////////////////////////////
        // FOR DEVELOPMENT TESTING ONLY. this class without an actual call from dt.
        // $request = $this->dummyRequest();
        ///////////////////////////////////////////////////////////////////////////////////////////


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Pull in all of the needed information passed to us by the dt ajax request.
        // These are in the $request and came from dt (Note: columns were set up in the dt-init using the $use_fields array from the controller.)
        $this->draw = $request->input('draw');                                  // Just goes back out in the response (maybe a page number ??)
        $this->row = $request->input('start');                                  // Used as: offset/skip; ($row, $rowperpage); $row is the offset part of the limit query.
        $this->rowperpage = $request->input('length');                          // Used as: limit/take; (rows to display per paging request.)

        if (empty($request->input('order')[0]['column'])) {                     // Sort on field; the DT column index (zero-based).
            $this->columnIndex = 0;                                             // Default to 0 when missing.
        } else {
            $this->columnIndex = $request->input('order')[0]['column'];         // Use the DT passed column to sort on.
        }

        $this->columnName = $request
            ->input('columns')[$this->columnIndex]['data'];                     // Column name (defined in the dt init)

        if (empty($request->input('order')[0]['dir'])) {                        // The sort order direction - asc or desc
            $this->columnSortOrder = 'asc';                                     // Default to 'asc' when missing.
        } else {
            $this->columnSortOrder = $request->input('order')[0]['dir'];        // Use the DT passed sort order.
        }

        $this->searchValue = $request->input('search')['value'];                // User input from the dt search box.

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Passed parameters (required) from the calling controller.
        $this->use_fields = $use_fields;                                        // Same $use_fields list as the index template expects.
        $this->dataset_query = $dataset_query;                                  // A query builder instance that defines our starting (baseline) record-set.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Create the aliased field_name list
        $this->field_list = $this->fieldList('generate');

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Total number of records BEFORE any user filtering
        $this->totalRecords =
            DB::select("
            SELECT count(*) as totalRecords FROM (
                ".$this->dataset_query."
            ) as t1
            ")[0]->totalRecords;


    }

    /**
     * Create - or return; a $key=>$value pair array with the field's alias => field_name/subquery
     * For native fields this will be 'field_name'=>'field_name.
     * For non-native - 'field_name'=>'(SELECT field FROM table WHERE clause)'
     *
     * @return mixed
     */
    protected function fieldList($action = 'generate') {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Generate the initial key=>value pair list.
        if ($action == 'generate') {
            $tmp = [];

            ///////////////////////////////////////////////////////////////////////////////////////////
            // Build out either default (native only) or custom defined fields.
            if ($this->getCustomFields()) {
                // If custom field definitions have been defined then use those.
                $tmp = $this->getCustomFields();
            } else {
                // Otherwise, just setup the defaults for native fields only.
                foreach ($this->use_fields as $field=>$label) {
                    $tmp[$field] = $field;
                }
            }

        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Return a "SELECT" statement ready list of fields.
        if ($action == 'select') {
            $tmp = '';
            foreach ($this->field_list as $alias=>$subquery) {
                $tmp .= $subquery. ' as '.$alias .',';
            }
            // Remove the trailing comma
            $tmp = rtrim($tmp, ',');
        }

        return $tmp;
    }

    /**
     * Boolean check applied to each $record in the getColumnData() method.
     * If no user input is passed (dt search box), then it just returns true.
     *
     * @return string
     */
    protected function searchFilter()
    {

        // Create the searchFilter HAVING clause [non-native for aliased fields]
        // Loop the $use_fields and create the LIKE / OR part of the search.
        $tmp = 'WHERE (';
        foreach ($this->field_list as $alias=>$subquery) {
           $tmp .= $subquery . " LIKE '%" . $this->searchValue . "%' OR ";
        }
        $tmp = rtrim($tmp, ' OR ');
        $tmp .= ')';

        return $tmp;

    }

    /**
     * Return any user defined joins.
     *
     * @return string
     */
    protected function joins() {
        $tmp = '';
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $tmp .= $join . ' ';
            }
        }
        return $tmp;
    }


    /**
     * Build out and return the ajax response back to dt.
     * - This is where we apply the sort order and user entered filter
     *
     * @return array
     */
    public function response() {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 1) Get the record count AFTER filtering. -- But BEFORE LIMITing.
        $this->totalRecordwithFilter =  DB::select("
            SELECT ".$this->fieldList('select').", count(*) as totalRecords FROM (
                ".$this->dataset_query."
            ) as t1
            ".$this->joins()."
            ".$this->searchFilter()."
            ")[0]->totalRecords;


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 2) Get the final data to return to dt
        // While applying the paging values for offset (row) and limit (rowperpage)
        $data =  DB::select("
            SELECT ".$this->fieldList('select')." FROM (
                ".$this->dataset_query."
            ) as t1
            ".$this->joins()."
            ".$this->searchFilter()."
            ORDER BY ".$this->columnName." ".$this->columnSortOrder."
            LIMIT ".$this->rowperpage."
            OFFSET ".$this->row."
            ");


        ///////////////////////////////////////////////////////////////////////////////////////////
        // 3) Apply links or any additional formatting to the selected fields in this final dataset.
        $data = $this->fieldPrep($data);



        ///////////////////////////////////////////////////////////////////////////////////////////
        // 4) Return all the dt specific fields along with the finalized $dataset ($data)
        //    Note: For some reason using json_encode() here escapes all double quotes and renders
        //          the data in a form not viewable.
        return  [
            "draw" => intval($this->draw),
            "iTotalRecords" => $this->totalRecords,
            "iTotalDisplayRecords" => $this->totalRecordwithFilter,
            "aaData" => $data
        ] ;

    }


    /**
     * Provide any links or supplemental formating for the data before handing it off to DT.
     *
     * @param $data
     * @return void
     */
    protected function fieldPrep($data) {

        // All that's needed for native fields.
        return $data;

        // WHEN EXTENDING/ OVERRIDING:
        /*
        foreach($data as $row) {
            foreach($this->use_fields as $field=>$label) {

                // A simple link on a native field.
                if ($field == 'kit_identifier') {
                    $row->$field = '<a href="'.route('narcan.show',['narcan'=>$row->id]).'">'.$row->$field.'</a>';
                }

                // A link on a related field (need to basically recreate the relationship here in 2 parts).
                if ($field == 'date_ordered') {
                    $n = Narcan::find($row->id);                    // First need the base record since order_id is not in the final dataset
                    $o = Order::where('id',$n->order_id)->first();  // Then use that to get the order information.
                    $row->$field = '<a href="'.route('orders.show',['order'=>$o->id]).'">'.$row->$field.'</a>';
                }

            }
        }
        */
    }

    /**
     * For any field that is using a ValidList entry, pull that list and create the corresponding sql CASE statement.
     *
     * @param $field        - alias name of field in question.
     * @param $list_name    - the ValidList() list to use.
     * @return string       - the fully formed sql CASE statement.
     */
    protected function createValidListCASE($field, $list_name) {
        $tmp = 'CASE ';

        // Get the ValidList we need to lookup on.
        $list = ValidList::getList($list_name);
        // Error check for 'no list' returned.
        if ($list == 'no list') {
            return $field;
        }

        /*
         CASE
            WHEN Quantity > 30 THEN "The quantity is greater than 30"
            WHEN Quantity = 30 THEN "The quantity is 30"
            ELSE "The quantity is under 30"
        END
         */

        // Create the internal case criteria
        foreach ($list as $key => $value) {
            $tmp .= 'WHEN '.$field.'='.$key. ' THEN "'.$value.'" ';
        }
        $tmp .= 'END';


        return $tmp;
    }


    /**
     * Override this method() to define our own (non-native) custom fields.
     *
     * @return mixed
     */
    protected function getCustomFields()
    {
        return false;   // This is all that's needed to use with all native fields.

        ///////////////////////////////////////////////////////////////////////////////////////////
        // WHEN EXTENDING THIS CLASS TO INCLUDE CUSTOM FIELDS:
        ///////////////////////////////////////////////////////////////////////////////////////////
        // to provide relationship, ValidList lookups or virtual fields
        // Start by copying in the use_fields list from the controller and manually entering them here.
        // Note: these are used to create both the WHERE search criteria and in the SELECT statement
        // so LEAVE OFF the 'as field_name' alias -- it will be added in the 'select' section below
        // when calling the fields for use.

        // Define any joins needed for your custom fields
        /*
        $this->joins = [
            'LEFT JOIN distributions AS distribution ON (distribution.id = t1.distribution_id)',
            'LEFT JOIN demographics AS demographic ON (demographic.id = t1.demographic_id)',
        ];
        */

        // REMEMBER: - $field_alias => $field_subquery
        // REMEMBER: - native fields "as is" while non-native need a (sub-query).
        // REMEMBER: - t1 is the alias for the initial dataset query.
        /*
        return [
            // Native--unaltered--fields for this table.
            'id'=>'id',
            'kit_identifier'=>'kit_identifier',
            'lot_number'=>'lot_number',

            // A relationship field from another table.
            'date_ordered'=>'(SELECT date_ordered FROM orders WHERE id = t1.order_id)',

            // A native field with a date format
            // WARNING: THIS WILL DRASTICALLY CHANGE THE BEHAVIOR OF THE DT SORT -- basically does an ascii sort on the value of the field.
            //'expiration_date'=>'DATE_FORMAT(expiration_date, "%m/%d/%Y")',
            'expiration_date'=>'expiration_date',

            // Relationships through a join
            'contact'=>'(SELECT CONCAT(first_name," ",last_name) FROM contacts WHERE id = distribution.contact_id)',
            'event'=>'(SELECT name FROM events WHERE id = distribution.event_id)',
            'distribution_date'=>'(SELECT distribution_date FROM distributions WHERE id = t1.distribution_id)',
            'final_distribution_date'=>'(SELECT final_distribution_date FROM demographics WHERE id = t1.demographic_id)',

            // A native field that requires a lookup value from ValidList.
            'location'=>$this->createValidListCASE('location','narcan_locations'),

        ];
        */
        ///////////////////////////////////////////////////////////////////////////////////////////

    }


}
