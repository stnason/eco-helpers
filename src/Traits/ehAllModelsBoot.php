<?php

namespace ScottNason\EcoHelpers\Traits;

/**
 * This trait is used in the ehBaseModel and ehBaseAuthenticatable
 * and defines the boot() method that all models need access to.
 *   - handles the modified user time stamps that I built.
 *   - handles removing comma separators from numbers before saving.
 *   - handles formatting date and date-time formats to mysql format before saving.
 */
trait ehAllModelsBoot
{

    /**
     * Add a boot event handler to watch for updating, saving and creating events.
     * Used for maintaining the ecoFramework updated and created by fields along with number format cleaning.
     * Note: date format cleaning was moved to the override function fromDateTime() in ehConvertDatesToSavable.
     *
     */
    public static function boot()
    {
        // Boot listener events to trigger on saving, updating and creating.
        // NOTE: Make sure this same boot code stays in sync with BaseAuthenticate
        parent::boot();

        static::saving(function ($model) {

            self::convertNumbersToSavable($model);
            self::savingTimestamps($model);
        });
        static::updating(function ($model) {

            self::convertNumbersToSavable($model);
            self::updatingTimestamps($model);

        });
        static::creating(function ($model) {
            self::creatingTimestamps($model);
        });

    }


}
