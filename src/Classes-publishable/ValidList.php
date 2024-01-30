<?php

namespace App\Classes
;
use ScottNason\EcoHelpers\Classes\ehValidList;

/**
 * Add your own $key=>value pair lists here.
 * Then can be statically created arrays and then easily switched to database calls later.
 *
 * Build in functions for:
 *
 *
 */

class ValidList extends \ScottNason\EcoHelpers\Classes\ehValidList
{

    protected static $_surface_type = [
        1=>'Dirt',
        2=>'Grass',
        3=>'Gravel',
        4=>'Asphalt',
        5=>'Concrete',
        6=>'Sand',
        9=>'Other'
    ];

    /**
     * getList() must be implemented here. (Do not remove it.)
     * It initializes all the added lists and then returns the individual key ($list_name) called for.
     *
     * @param $list_name
     * @return mixed|string
     */
    public static function getList($list_name)
    {
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Don't remove this. This is the global init and has to stay here.
        parent::initLists();


        ///////////////////////////////////////////////////////////////////////////////////////////
        // This is where you add all your custom lists:
        parent::addList('surface_type', self::$_surface_type);



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Don't change this.
        return parent::getList($list_name);
    }


}
