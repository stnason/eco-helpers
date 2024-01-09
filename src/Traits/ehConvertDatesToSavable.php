<?php

/**
 * This trait is used in the EcoBaseModel and EcoBaseAuthenticatable to
 *  convert fields defined in the Model as "dates" to the sql savable format.
 *
 * Include "use App/Traits/ConvertDateToSavable" in the Model Class declaration that doesn't extend BaseModel.
 *
 */

namespace ScottNason\EcoHelpers\Traits;


use Carbon\Carbon;
use ScottNason\EcoHelpers\Classes\ehConfig;


trait ehConvertDatesToSavable
{

    /**
     * Convert a DateTime to a storable string.
     *
     * My override function; calls this on any [date] defined field from the model.
     * Checks its length to determine which config('app.date_format_php_short') to use for
     * either date only or the whole date time.
     *
     *
     * @param  mixed  $value
     * @return string|null
     */


    public function fromDateTime($value)
    {


        //return parent::fromDateTime($value);


        /*
        * NOTE: This appears to be part of an internal Laravel mechanism for turning dates into the savable format
        * needed by the datatbase before doing the save.
        * But not working for me so I overrode it and build this out.
        */

        /* Laravel original:
        return empty($value) ? $value : $this->asDateTime($value)->format(
            $this->getDateFormat()
        );
        */


        // If $value is empty or the mysql non-date error format then just return it "as is".
        if (empty($value) || $value == '0000-00-00') {
            return $value;
        }


        //Check to ensure that the datetime format config variables are available before attempting to use them.
        if (empty(ehConfig::get('date_format_sql_long')) or empty(ehConfig::get('date_format_sql_long'))) {
            throw new \Exception('Missing date time format configuration in eco-helpers.php');
        }


        // Otherwise see if this should be either a date only format or a date-time format.
        if (strlen($value) > 10) {          // 'mm/dd/yyyy' = 10
                                            // > 10 then we assume it's date-time.
            return Carbon::parse($value)->format(ehConfig::get('date_format_sql_long'));

        } else {

            // If <= 10 then return a date only format.
            return Carbon::parse($value)->format(ehConfig::get('date_format_sql_long'));

        }

    }


}
