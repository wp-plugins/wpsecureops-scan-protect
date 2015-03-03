<?PHP
defined('ABSPATH') or exit;

global $WPSecureOps;
if (!isset($WPSecureOps)) {
    $WPSecureOps = array();
}

$pluginId = "wpsecureops_scan_protect";

$WPSecureOps[$pluginId] = array(
    "title"   => "WPSecureOps Scan Protect",
    "id"      => $pluginId,
    "version" => "1.3",
);

return $pluginId;
