<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.get_plugin_path.php
 * Type:     modifier
 * Name:     get_plugin_path
 * Purpose:  For special source (like Facebook pages) return the
 *           correct path to the ThinkUp plugin.
 *           @TODO: Figure out a better way to handle this.
 * -------------------------------------------------------------
 */
function smarty_modifier_get_plugin_path($network) {
    if ($network == "facebook page") {
        return "facebook";
    } else {
        return $network;
    }
}
?>