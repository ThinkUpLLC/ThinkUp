<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.link_usernames_to_twitter.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Smarty link usernames plugin
 *
 * Type:     modifier<br>
 * Name:     link_usernames_to_twitter<br>
 * Date:     July 4, 2009
 * Purpose:  links a Twitter username to their user page
 * Input:    status update text
 * Example:  {$status_html|link_usernames_to_twitter}
 * @TODO Find a more elegant way to do this that's totally regex-based, not loving this explode/implode approach
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author   Gina Trapani
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_link_usernames_to_twitter($text) {
    return preg_replace('/(^|[^a-z0-9_])@([a-z0-9_]+)/i',
    '$1<a href="https://twitter.com/intent/user?screen_name=$2">@$2</a>', $text);
}
