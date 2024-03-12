<?php

namespace ScottNason\EcoHelpers\Traits;

/**
 * This trait provides a helper function to getLabel('field_name') from the model.
 *
 * This is included in the ehBaseModel.
 *
 */
trait ehGetLabels
{

    /**
     * Return the associated label name for this field.
     *
     * @param field_name        // Name of the underlying field in the model.
     * @return void
     */
    public function getLabel($field_name) {
        if (!empty($this->labels[$field_name])) {
            return $this->labels[$field_name];
        } else {
            return $field_name;
        }
    }

}