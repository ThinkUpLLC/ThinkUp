<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty wordwrap modifier plugin
 *
 * Type:     modifier<br>
 * Name:     use_https<br>
 * Purpose:  Turn http URLs stored in the database into
 * @author   Matt Jacobs
 * @param string
 * @return string
 */
function smarty_modifier_use_https($string)
{
    return preg_replace('/^http:(.+)$/', "https:$1", $string);
}

?>
