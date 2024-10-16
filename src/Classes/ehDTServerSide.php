<?php

namespace ScottNason\EcoHelpers\Classes;


use Illuminate\Support\Facades\DB;
use function App\Classes\Request;

/**
 * Datatables Server-Side paging processing.
 * Notes:
 *  - This is designed to (most commonly) have mekData() overridden to pull and format the data to hand back to dt.
 *  - buildSearchQuery() can be overridden to build custom searches (other than the standard f1 LIKE & or f2 LIKE %,
 *  etc.)
 *
 *
 */
class ehDTServerSide
{

    protected $draw;                                // Expected from dt ajax.
    protected $row;                                 // Expected from dt ajax.
    protected $rowperpage;                          // Expected from dt ajax.
    protected $columnIndex;                         // Expected from dt ajax.
    protected $columnName;                          // Expected from dt ajax.
    protected $columnSortOrder;                     // Expected from dt ajax.
    protected $searchQuery;                         // Initial WHERE (passed) + user input search value
    protected $totalRecords;                        // Total records unfiltered (using the initial_where)
    protected $totalRecordwithFilter;               // Total records after user (dt search bar) filtering.
    //protected $tableName;                           // Actual table name pulled from the passed model.
    //protected $model;                               // The passed Laravel model for this query.
    protected $use_fields;                          // $use_field $key=>$value pair array (from controller)
    protected $query_builder;
    protected $searchValue;                         // The user provided dt search box entry

    //protected $relationships;                     // An array with a list of ->with('name') relationships to use.
                                                    // This was originally thought to be a csv string but turns out to be an array ['r1','r2','r3']


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
                    {"column":"0","dir":"asc"}
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
     * @param $query_builder        - Illuminate\\Database\\Eloquent\\Builder
     *                              - pass in the initial query builder data set
     *                              // USAGE: Model::whereRaw('1')
     *                              // Note: we have to set the initial query builder with something that can be added to for the user search
     *                              // so we're just using a 'where 1' and the builder will add the 'and' after that for subsequent where clauses.
     *
     */
    public function __construct($request, $use_fields, $query_builder) {


        // FOR DEVELOPMENT TESTING ONLY. this class without an actual call from dt.
        // $request = $this->dummyRequest();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Pull in all of the needed information passed to us by the dt ajax request.

        // These are in the $request and came from dt (Note: columns were set up in the dt-init using the $use_fields)
        $this->draw = $request->input('draw');                                  // Just goes back out in the response (usually 1) ??
        $this->row = $request->input('start');                                  // limit ($row, $rowperpage) ($row is the offset part of the query)
        $this->rowperpage = $request->input('length');                          // Rows to display per page
        $this->columnIndex = $request->input('order')[0]['column'];             // Column index
        $this->columnName = $request->input('columns')[$this->columnIndex]['data'];  // Column name (defined in the dt init)
        $this->columnSortOrder = $request->input('order')[0]['dir'];            // asc or desc
        $this->searchValue = $request->input('search')['value'];                // User input from the dt search box.


        // Passed parameters from the calling controller.
        $this->use_fields = $use_fields;                                        // Same $use_fields list as the index template expects.
        $this->query_builder = $query_builder;                                  // A query builder instance that defines our starting (baseline) record-set.
                                                                                // DO NOT INCLUDE the trailing "->get()" - we'll do that after we filter it.


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Total number of records without filtering
        $this->totalRecords = $this->query_builder->count();


    }


    /**
     * This is where the user's dt search-box entry is processed.
     *
     * This will create a rudimentary field LIKE %search-value% OR
     * for all fields in $use_fields.
     *
     * If you need more functionality, extend this class and override this method.
     *
     * Don't forget: we're applying the "->get()" here to execute the pull on the query that was passed ($query_builder)
     *
     * @return array
     */
    protected function searchFilter()
    {

        $query = $this->query_builder;

        if (!empty($this->searchValue)) {

            /*
            // Build out a standard "LIKE % OR" query for all $use_fields.
            foreach ($this->use_fields as $field=>$label) {
                $query->orWhere($field, 'like', '%' . $this->searchValue . '%');
            }
            */


            // Loop the $use_fields and create the LIKE / OR part of the search.
            $tmp = ' (';
            foreach ( $this->use_fields as $field=>$label) {

                // Ensure that all of these are "real" fields. (not virtual or fake)
                // $query_builder->getModel()->getTable()     // table name from query builder

                if ( DB::select("  SELECT COUNT(*) as total
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = '".$query->getModel()->getTable()."'
                AND COLUMN_NAME = '".$field."';")[0]->total > 0) {
                    $tmp .= $field . " LIKE '%" . $this->searchValue . "%' OR ";
                }

            }
            $tmp = rtrim($tmp, ' OR ');
            $tmp .= ') ';
            $query->whereRaw($tmp);

            // Custom search in extended class:
            // Note: This is the way to add the filter to the passed dataset ($query_builder) using a relationship
            // dd($query_builder->whereRelation('contacts','first_name','like','%'.$this->searchValue.'%')->get());

        }

        return $query->get();
    }



    /**
     * Make a result set that MUST! match the $use_field array.
     *
     *  Extend this class and override this method if you need to:
     *      - Deal w/any virtual fields (like contact_or_vendor)
     *      - Format any data; like dates, numbers, dollars...
     *
     * @param $tmpResult
     * @return array
     */
    protected function makeData($tmpResult) {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // USE THIS FORMAT WHEN EXTENDING THIS CLASS FOR A SPECIFIC TABLE/ APPLICATION.
        // Build out the result-set $data that matches the $use_fields array.
        // Note: you can easily return data on any relation here: Example: $record->contacts->last_name (contacts is the relation).
        /*
           $data = [];
           foreach ($tmpResult as $key => $record) {
               -- simple return of the table value as-is.
               $tmpData['field1'] = $record->field1;
               $tmpData['field2'] = $record->field2;

                -- With a link
               $tmpData['kit_identifier'] = '<a href="'.route('narcan.show',['narcan'=>$record->id]).'">'.$record->kit_identifier.'</a>';

               -- With date format
               $tmpData['field4'] = $record->field4->format('Y-m-d')       // Note; always using Y-m-d so dt column sorting works properly on dates.

                -- create a virtual field
                $tmpData['contact_or_event'] = '';
                   if (!empty($record->contacts)) {
                       $tmpData['contact_or_event'] = '<a href="'.route('contacts.show',['contact'=>$record->contacts->id]).'">'. $record->contacts->fullName() . "</a>";
                   }
                   if (!empty($record->events)) {
                       $tmpData['contact_or_event'] = '<a href="'.route('events.show',['event'=>$record->events->id]).'">'. $record->events->name . "</a>";
                   }

                 $data[] = $tmpData;
            }
            return $data;

       */


/*
 * THIS IS THE GENERIC data maker -- it only works with "real" fields and will provide no formatting.
 * You'll have to extend the class and override this method to get links, formatting and proper dealing w/custom fields.
 *
 *
 * $narcans->getModel()->getTable()     // table name from query builder
 * $narcans->getModel()->getRelations() // maybe relations ??
 *
    */    ///////////////////////////////////////////////////////////////////////////////////////////
        // Build out the result-set data using the $use_fields array.
        // This the generic way to do this and will work if all the fields exist and have real data (no id lookups involved)
        $data = [];
        foreach ($tmpResult as $key => $record) {
            $one_record = [];
            foreach ($this->use_fields as $field=>$label) {

                // This is the returned data that will populate the datatable so any formatting (including links)
                // must be done here!

                if (!empty($record->$field)) {

                    $one_record[$field] = $record->$field;

                } else {

                    /*
                     * NOTE: if your initial model includes relationships -- just go ahead and extend this class now and override this method.
                    // LOOP THE SUPPLIED RELATIONSHIPS TO SEE IF THE REQUESTED FIELD IS ON ONE OF THOSE.
                    // !!! Note: this will only work properly if the field name is unique across all tables. !!!
                    // So, if it's blank, see if it's on any of the relationships
                    foreach ($this->relationships as $relationship) {
                        if (!empty($record->$relationship->$field)) {
                            $one_record[$field] = $record->$relationship->$field;
                            break;
                        } else {
                            // Otherwise, just use blank.
                            $one_record[$field] = '';
                        }
                    }
                    */
                    $one_record[$field] = 'not in table; extend this class';

                }
            }
            $data[] = $one_record;
        }

        return $data;


    }



    /**
     * Build out and return the response back to dt.
     * @return array
     */
    public function response() {

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 1) See if we have to add any user filters to the query
        $tmpResult = $this->searchFilter();

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 2) Get the Total number of records AFTER user filtering.

        // umm..not sure how this is working but it looks like we do have to add "->get()" in the searchFilter() method
        // yet, ->count() still works here (?)
        //$this->totalRecordwithFilter = count($tmpResult);         // if you're doing the ->get() upstream.
        $this->totalRecordwithFilter = $tmpResult->count();         // if you're doing the -get() downstream.

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 3) Then populate and format the output $data based on the
        // Note: makeData() must return the same fields called for in $use_fields !
        //$data = $this->makeData($tmpResult->get());
        $data = $this->makeData( // Apply the dt paging
            $this->query_builder
                // #####################################################################
                // dt paging control coming through the $request each time.
                ->orderBy($this->columnName, $this->columnSortOrder)
                ->limit($this->rowperpage)
                ->offset($this->row)
                ->get()
        );

        ///////////////////////////////////////////////////////////////////////////////////////////
        // 4) Then finally, return the needed dt fields along with the finalized dataset ($data)
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
