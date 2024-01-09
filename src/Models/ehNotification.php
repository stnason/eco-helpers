<?php

namespace ScottNason\EcoHelpers\Models;

use Illuminate\Support\Facades\DB;


class ehNotification extends ehBaseModel
{

    protected $table = 'eh_notifications';

    /**
     * Lets the Controls class know which input data should be treated as date formats.
     *
     * @var string[]
     */
    public $dates = ['created_at', 'updated_at'];

    /**
     * The Controls class will automatically fill in the label names if they are defined in the model.
     * (If not, it will use the database field names.)
     *
     * @var string[]
     */
    public $labels = [


        'created_by'=>'created by',
        'created_at'=>'created date',
        'updated_by'=>'updated by',
        'updated_at'=>'updated date',

        ];

    public $guarded = [
        '_token',
        'new',
        'delete',
        'save'
    ];




}
