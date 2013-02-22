<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.truncate_for_select.php
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.truncate_for_select.php
 * Type:     modifier
 * Name:     truncate
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string.
 * -------------------------------------------------------------
 */
function smarty_modifier_truncate_for_select($string, $length = 50, $etc = '...', $break_words = false) {
    if ($length == 0) {
        return '';
    }

    if (strlen($string) > $length) {
        $length -= strlen($etc);
        $fragment = substr($string, 0, $length+1);
        if ($break_words) {
            $fragment = substr($fragment, 0, -1);
        } else {
            $fragment = preg_replace('/\s+(\S+)?$/', '', $fragment);
        }
        return $fragment.$etc;
    } else {
        return $string;
    }
}