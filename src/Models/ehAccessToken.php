<?php

namespace ScottNason\EcoHelpers\Models;

/**
 * The model associated with the eh_access_tokens table which stores role access permissions.
 *
 */
class ehAccessToken extends ehBaseModel
{

    protected $table = 'eh_access_tokens';

    public $guarded = [
        '_token',
        'new',
        'delete',
        'save'
    ];


}
