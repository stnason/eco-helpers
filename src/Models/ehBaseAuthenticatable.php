<?php

namespace ScottNason\EcoHelpers\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;

use ScottNason\EcoHelpers\Traits\ehGetLabels;
use ScottNason\EcoHelpers\Traits\ehHasUserstamps;
//use ScottNason\EcoHelpers\Traits\ehConvertDatesToSavable;
use ScottNason\EcoHelpers\Traits\ehConvertNumbersToSavable;
use ScottNason\EcoHelpers\Traits\ehAllModelsBoot;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

/**
 * The base model for the ehUser model to extend; this obviously extends the underlying Laravel Authenticatable.
 *
 * Pull together custom functionality that we need for all models
 * (like the ability to store and retrieve the label names; format data for saving, manage custom time stamps, etc.)
 *
 *  See also ehBaseModel that needs to be kept in sync with the functionality provided here.
 */
class ehBaseAuthenticatable extends Authenticatable
{

    public $timestamps = false;         // Use this to turn off the default Laravel "_at" timestamp fields

    //use ScottNason\EcoHelpers\Traits\CustomModelProperties;
                                        // getCustomProperty() to retrieve my custom model lists (mostly for Controls use on forms)
    use ehHasUserstamps;
    //use ehConvertDatesToSavable;      // An override of the Laravel fromDateTime() method.
    use ehConvertNumbersToSavable;      // My routine for removing comma separators from numbers before storing.
    use ehAllModelsBoot;                // The boot() method that sets up the listener events.
    //use CanResetPassword;
    use ehGetLabels;                    // A convenience method to retrieve the Label associated with a field name.

    use HasApiTokens, HasFactory, Notifiable;

}
