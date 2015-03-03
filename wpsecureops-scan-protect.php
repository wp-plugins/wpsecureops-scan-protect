<?php

/*
Plugin Name: WPSecureOps Scan Protect
Plugin URI: http://wpsecureops.com/
Description: Prevent most of the hack attempts before they even start, by detecting any malicious scan attempts.
Version: 1.3
Author: WPSecureOps
Author URI: http://wpsecureops.com/
License: GPLv2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
*/

require_once "plugin_info.php";
require_once "utils.php";

require_once "settings.php";

global $wpsecureops_scan_protect_error;
$wpsecureops_scan_protect_error = false;

add_action('plugins_loaded', 'wpsecureops_scan_protect_log_request');

function wpsecureops_scan_protect_log_request()
{
    if (is_user_logged_in()) {
        return;
    }
    $timeout = intval(wpsecureops_scan_protect_get_interval());
    $timeout = $timeout  ? $timeout : 5 /* 5sec */;

    $max_attempts = intval(wpsecureops_scan_protect_get_max_attempts());
    $max_attempts = $max_attempts ? $max_attempts : 15;

    $cnt = get_transient($k = "wpso_sp_" . wpsecureops_scan_protect_get_ip_address());
    if ($cnt === false) {
        $cnt = 0;
    } else {
        $cnt = intval($cnt);
    }

    if (is_404()) {
        $cnt += 1.2;
    } else {
        $cnt ++;
    }

    set_transient($k, "" . $cnt . "", $timeout);

    if ($cnt >= $max_attempts) {
        // is this a google/bing/etc bot?
        $host = gethostbyaddr(wpsecureops_scan_protect_get_ip_address());
        foreach (wpsecureops_scan_protect_get_allowed_bots() as $allowed_host) {
            if (wpsecureops_scan_protect_wpsecureops_fnmatch($allowed_host, $host) === true) {
                delete_transient($k);

                return;
            }
        }
        set_transient($k, "" . $cnt . "", wpsecureops_scan_protect_get_lock_duration());

        global $wpsecureops_scan_protect_error;
        $wpsecureops_scan_protect_error = true;
        // send notification
        $email = wpsecureops_scan_protect_get_notify_email();
        if ($cnt === $max_attempts && !empty($email)) {
            $r = wp_mail(
                $wpsecureops_scan_protect_error['notify_email'],
                __('WPSecureOps Notification: Scan attack', 'wpsecureops_scan_protect'),
                __('WPSecureOps Scan Protect has detected a new scan from: ' . wpsecureops_scan_protect_get_ip_address() . '

However, you can relax, because we\'d blocked the user for a while, so everything is just fine :)

Cheers,
WPSecureOps Team', 'wpsecureops_scan_protect')
            );
        }

        wp_die(
            __("You have been banned because we'd detected a scan attempt.", 'wpsecureops_scan_protect')
        );
    }
}
