<?php

namespace ScottNason\EcoHelpers\Models;

/**
 * The model associated with the eh_roles table.
 * (formerly 'groups')
 *
 */
class ehRole extends ehBaseModel
{

    protected $table = 'eh_roles';


    /**
     * Let's the Controls class know which input data should be treated as date formats.
     *
     * @var string[]
     */
    public $dates = ['created_at', 'updated_at'];


    public $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];



    /**
     * The Controls class will automatically fill in the label names if they are defined in the model.
     * (If not, it will use the database field names.)
     *
     * @var string[]
     */
    public $labels = [

        'id'=>'Rec ID',
        'active' => 'Active',
        'act_as_role' => 'Act as Role',
        'site_admin' => 'Site Admin',

        'name' => 'Name',
        'description' => 'Description',
        'restrict_flag' => 'Restriction',       // Custom Group restriction (and be used bu controllers and views for additional restrictions for this group.
        'default_home_page' => 'Default Home Page',

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




    /* Moved over from ehRole before getting rid if it and renaming this to ehRole.
    public static function getGroupMembers($d) {
        $q = "SELECT * FROM eh_roles WHERE d = {$d};";
        return DB::select($q);
    }
    */


}
