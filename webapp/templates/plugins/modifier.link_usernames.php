<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */
 
/**
 * Smarty link usernames plugin
 *
 * Type:     modifier<br>
 * Name:     link_usernames<br>
 * Date:     July 4, 2009
 * Purpose:  links a Twitter username to their user page
 * Input:    status update text
 * Example:  {$status_html|link_usernames}
 * @author   Gina Trapani 
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_link_usernames($text) {
	//TODO: Find a more elegant (perhaps regex-based) way to do this
	global $cfg, $i; //icky but necessary
	$words = explode(" ", $text);
	for($k = 0; $k < count($words); $k++) {
		if ( substr($words[$k], 0, 1) == '@' ) {
			$words[$k] = '<a href="'.$cfg->site_root_path.'user/?u='.substr($words[$k],1).'&i='.$i->twitter_username.'">'.$words[$k].'</a>';
		} 
	}
	return implode($words, ' ');
}
?>