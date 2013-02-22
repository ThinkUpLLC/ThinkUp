<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.filter_xss.php
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
 * Smarty XSS content filter plugin
 *
 * Type:     modifier<br>
 * Name:     filter_xss<br>
 * Date:     June 26, 2011
 * Purpose:  filters content for XSS
 * Input:    text
 * Example:  {$some_text|filter_xss}
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Mark Wilkie
 * @author   Mark Wilkie
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_filter_xss($text) {
    $text = html_entity_decode($text); # because Twitter sends html-encoded chars that get stored in the DB
    return filter_var($text, FILTER_SANITIZE_SPECIAL_CHARS);
}
