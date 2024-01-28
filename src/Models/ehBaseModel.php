<?php
/**
 * ecoFramework.
 * Pull together custom functionality that we need for all models
 * (like the ability to store and retrieve the label names; format data for saving, manage custom time stamps, etc.)
 *
 */

namespace ScottNason\EcoHelpers\Models;

use ScottNason\EcoHelpers\Traits\ehAllModelsBoot;
use ScottNason\EcoHelpers\Traits\ehConvertNumbersToSavable;
use ScottNason\EcoHelpers\Traits\ehGetLabels;
use ScottNason\EcoHelpers\Traits\ehHasUserstamps;

use Illuminate\Database\Eloquent\Model;


class ehBaseModel extends Model
{

    public $timestamps = false;         // Use this to turn off the default Laravel "_at" timestamp fields

    use ehHasUserstamps;

    // 01/15/2023; This broke after normalizing all the date_format variables so I took it out and now it works without it.
    //                At least the system timestamps do.
    //                We'll have to test this with other date fields and see if it still works. (that would be awesome!)

    //use ehConvertDatesToSavable;        // An override of the Laravel fromDateTime() method.
    use ehConvertNumbersToSavable;        // My routine for removing comma separators from numbers before storing.
    use ehAllModelsBoot;                  // The boot() method that sets up the listener events.
    use ehGetLabels;                      // A convenience method to retrieve the Label associated with a field name.


    /*
    // This might be a good basis for a field change logging routine.
    // Maybe create a log changes trait?
   static::updating(function($model)
   {
       $changes = [];
       foreach($model->getDirty() as $key => $value)
       {
           $original = $model->getOriginal($key);=
           $changes[$key] = [
               'old' => $original,
               'new' => $value,
           ];
       }
       $model->changes = $changes;
   });
   */


}

