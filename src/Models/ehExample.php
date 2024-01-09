<?php

namespace ScottNason\EcoHelpers\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ehExample extends ehBaseModel
{

    use HasFactory;

    protected $table = 'eh_examples';

    /**
     * Tells the ehControls class which fields should be treated as dates.
     * This will prep them for date or datetime picker use when using the auto-loader.
     *
     * @var string[]
     */
    public $dates = ['birthdate','created_at', 'updated_at'];

    public $casts = [
        'birthdate' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * When building forms using the ehControls class, labels will automatically fill in if they are defined below.
     * Any labels that are undefined will show up as the field name.
     *
     * @var string[]
     */
    public $labels = [

        'id'=>'User ID',
        'active'=>'Active',
        'archived'=>'Archived',
        'name '=>'User Name',
        'address'=>'Full Address',
        'city'=>'City',
        'state'=>'ST',
        'zip'=>'Zip Code',
        'phone'=>'',                // Any labels that are undefined will show up as the field name.
        'email'=>'',                // Any labels that are undefined will show up as the field name.
        'birthdate'=>'',
        'title'=>'Title',
        'bio'=>'User Bio',

        'created_by'=>'created by',
        'created_at'=>'created date',
        'updated_by'=>'updated by',
        'updated_at'=>'updated date',

        ];

    /**
     * When accepting all posted data, this ensures that we drop out the "non-database" fields that are passed in the form.
     * @var string[]
     */
    public $guarded = [
        '_token',
        'new',
        'delete',
        'save'
    ];

}
