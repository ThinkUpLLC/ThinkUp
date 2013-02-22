<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.csrf_token.php
 *
 * Copyright (c) 2011-2013 Mark Wilkie
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
 * Help Link
 *
 * Type:     insert<br>
 * Name:     csrf_token
 * Date:     April 26, 2011
 * Purpose:  Returns session CSRF token.
 * Input:    key
 * Example:  {insert name="csrf_token"}
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Mark Wilkie
 * @version 1.0
 */
function smarty_insert_csrf_token($params, &$smarty) {
    $csrf_token = Session::getCSRFToken();
    if (isset($csrf_token)) {
        return sprintf('<input type="hidden" name="csrf_token" value="%s" />', $csrf_token);
    } else {
        return '<!-- Error: no csrf token found in session -->';
    }
}
