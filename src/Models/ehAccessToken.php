<?php

namespace ScottNason\EcoHelpers\Models;

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
