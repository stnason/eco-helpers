<?php

namespace ScottNason\EcoHelpers\Traits;

use Illuminate\Support\Facades\DB;

/**
 * This trait is used in the ehBaseModel to convert fields defined as "numbers"
 * into mysql savable format (mostly removing commas).
 *
 * Note: this works in conjunction with the 'number_format' parameter in Controls
 * (which is adding the thousands comma separators).
 *
 * Note: MySQL seems to be albel to deal fine with decimals being passed to integer types (i.e. 10.6 rounds up to 11)
 * Just can't deal withe comma -- it must think it's a string then.
 *
 * Include "use App/Traits/ehConvertNumbersToSavable" in any Model Class declaration
 * that doesn't extend the BaseModel (if needed).
 */
trait ehConvertNumbersToSavable
{

    /*
     * MySQL numeric types:
     *
     * Integer - INTEGER, INT, SMALLINT, TINYINT, MEDIUMINT, BIGINT
     * Fixed-Point - DECIMAL, NUMERIC    (exact)
     * Floating-Point - FLOAT, DOUBLE    (approximate)
     *
     */

    protected static $number_types = [
        'INTEGER', 'INT', 'SMALLINT', 'TINYINT', 'MEDIUMINT', 'BIGINT',
        'DECIMAL', 'NUMERIC', 'FLOAT', 'DOUBLE'
    ];



    /**
     * Convert any number to a sql savable format;
     *  - basically just removing any commas for now.
     *      (may add an appropriate cast and decimal checking later if needed)
     *
     */
    public static function convertNumbersToSavable($model) {

        // Get a list of Field => Type from the current table.
        $field_list = self::getFieldTypeList($model);


        // Loop the request fields and fix any that should be numeric.
        // For now, just removing the commas (thousand separators) that are displayed in the form.
        foreach(request()->input() as $key => $value) {

            // Is this request field name defined in mysql as one of the numeric data types (or missing = null)?
            if (self::isANumericType($field_list[$key] ?? null)) {      // Some request fields (like _token) are not in the table schema.

                // If the value is null then just leave it alone.
                if ($value != null) {
                    // If numeric, then remove the commas and fix the field in place.
                    // $request->request->set($key, str_replace(',','',$value));
                    // request()->request->set($key, str_replace(',','',$value));

                    // If there is no comma in the field then leave it alone.
                    if (strpos($value,',') > 0) {
                        $model->$key = str_replace(',','',$value);
                    }

                }
            }

        }

        //dd(request()->input());
        //return request()->input();
    }


    /**
     * Check the schema Type definition to see if it's a numeric field.
     *
     *  - Note: We're checking it against the $number_format list defined at the top.
     *
     * @param $type
     * @return bool
     */
    protected static function isANumericType($type) {

        // There are other fields in the input data (like _token) that are not in the table schema.
        if ($type === null) {
            return false;
        }

        // Test to see if that passed $type is contained in our pre-defined list of numeric types (at the top).
        if (in_array($type, self::$number_types)) {
            return true;
        } else {
            return false;
        }

    }


    /**
     * Returns a field name list along with the shortened (base) of its data type; (Field => Type)
     *
     * i.e  - changes int(11) to INT
     *      - changes decimal(10,2) to DECIMAL
     *
     * @return array
     */
    protected static function getFieldTypeList($model) {

        // Get a list of fields from the table.
        $tmp = DB::select('SHOW FIELDS FROM '.$model->getTable());

        // Convert that indexed array to an associative array using the field name as the key.  ($field_name => $field_type)
        $field_list = [];
        foreach($tmp as $row) {

            // Convert the Type from the schema to uppercase with no trailing parameters: (int(11) -> INT
            $field_list[$row->Field] = strtoupper(preg_replace("/[^A-Za-z]/", "", $row->Type));

            // Remove the word 'UNSIGNED' if it's in there
            $field_list[$row->Field] = str_replace('UNSIGNED','',$field_list[$row->Field]);

        }

        return $field_list;

    }


}
