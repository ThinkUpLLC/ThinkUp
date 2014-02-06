<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.urlencode_network_username.php
 *
 * Copyright (c) 2014 Matt Jacobs
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 */
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Custom URL-encode network usernames. Encode spaces but not accented characters.
 *
 * Type:     modifier
 * Name:     urlencode_network_username
 * Date:     February 6, 2014
 * Purpose:  Custom URL-encode network usernames. Encode spaces but not accented characters.
 * Input:    text
 * Example:  {$network_username|urlencode_network_username}
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Matt Jacobs
 * @author   Matt Jacobs
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_urlencode_network_username($text) {
	//Straight urlencode replaces accented characters in network usernames like Facebook names, and we don't want that
	//@TODO Add additional encoding of special characters as needed
    $text = str_replace(' ', '+', $text);
    return $text;
}
