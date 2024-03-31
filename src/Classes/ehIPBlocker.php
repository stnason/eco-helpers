<?php

namespace ScottNason\EcoHelpers\Classes;


/**
 * Block known bad-actor IP addresses.
 * Thanks to: https://perishablepress.com/how-to-block-ip-addresses-with-php/
 *
 */
class ehIPBlocker
{

    /**
     * Hard coded list of IP addresses to block.
     *
     * @var string[]
     */
    protected static $blacklist = [
        "74.138.60.72",
    ];

    /**
     * Where to redirect to when one of the blocked IP addresses makes a request.
     * @var string
     */
    protected static $on_block_redirect = "https://example.com/";

    /**
     * Execute the ip blocking check and redirect if address is found to be in the black-list.
     * @param $ip
     * @return void
     */
    public static function checkIP($ip=null) {

        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        if (in_array($ip, self::$blacklist))
        {
            header("location: ".self::$on_block_redirect);
            exit();
        }
    }


}

