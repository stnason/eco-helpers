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
     * Execute the ip blocking check and redirect if address is found to be in the black-list.
     * For now, this is executed on a hard-coded list that is passed.
     * You can also pass a url to direct to and/or an ip address other than the default REMOTE_ADDR.
     *
     * @param $blacklist
     * @param $redirect_to
     * @param $remote_ip
     * @return void
     */
    public static function checkIP($blacklist=[], $redirect_to=null, $remote_ip=null) {

        // Use the remote host access ip if none provided.
        if (empty($remote_ip)) {
            $remote_ip = $_SERVER['REMOTE_ADDR'];
        }
        // Redirect to here when blacklisted if none provided
        if (empty($redirect_to)) {
            $redirect_to = 'https://example.com/';
        }
        
        if (in_array($remote_ip, $blacklist))
        {
            header("location: ".$redirect_to);
            exit();
        }
    }


}

