<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.link_usernames.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author   Gina Trapani
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_link_usernames($text, $instance_username, $network) {
    //TODO: Find a more elegant way to do this that's totally regex-based, not loving this explod/implode approach
    $config = Config::getInstance();
    $words = explode(" ", $text);
    $pattern = '/^@[a-zA-Z0-9_]+/';
    for($k = 0; $k < count($words); $k++) {
        if ( substr($words[$k], 0, 1) == '@' ) {
            preg_match($pattern, $words[$k], $matches);
            $words[$k] = '<a href="'.$config->getValue('site_root_path').'user/?u='.substr($matches[0],1).
            '&n='.$network.'&i='.$instance_username.'">'.$words[$k].'</a>';
        } else if ( substr($words[$k], 0, 2) == '(@' ) { //for usersnames in parentheses
            preg_match($pattern, substr($words[$k], 1, strlen($words[$k])), $matches);
            $words[$k] = '<a href="'.$config->getValue('site_root_path').'user/?u='.substr($matches[0],1).
            '&n='.$network.'&i='.$instance_username.'">'.$words[$k].'</a>';
        }
    }
    return implode($words, ' ');
}
?>