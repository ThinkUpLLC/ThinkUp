<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.use_https.php
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
 * Smarty https modifier plugin.
 *
 * Type:     modifier<br>
 * Name:     use_https<br>
 * Purpose:  Turn http URLs stored in the database into https.
 * @author   Matt Jacobs
 * @param string
 * @return string
 */
function smarty_modifier_use_https($string) {
	return preg_replace('/^http:(.+)$/', "https:$1", $string);
}
