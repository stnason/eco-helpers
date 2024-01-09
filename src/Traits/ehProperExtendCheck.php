<?php

namespace ScottNason\EcoHelpers\Traits;

/**
 * A shared trait for ehBaseController and ehLoginController.
 *
 */
trait ehProperExtendCheck
{

    /**
     * A simple check to ensure that subsequent page controllers have been setup to extend this one.
     * This function literally does nothing; except to exist() so Layout can check to see if it's there.
     *
     * @return bool|mixed
     */
    public function doesExtendBaseController() {
        return null;
    }

}