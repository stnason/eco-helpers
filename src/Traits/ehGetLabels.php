<?php

/**
 * This trait is responsible for maintaining the ecoFramework fields:
 * created_by
 * created_at
 * updated_by
 * updated_at
 *
 * Include "use App/Traits/ehHasUserstamps" in the Model Class declaration
 *
 */

namespace ScottNason\EcoHelpers\Traits;


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