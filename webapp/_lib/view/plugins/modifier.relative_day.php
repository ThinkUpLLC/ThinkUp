<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.relative_day.php
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
 * Smarty relative date / time plugin
 *
 * Type:     modifier<br>
 * Name:     relative_day<br>
 * Date:     March 18, 2009
 * Purpose:  converts a date to a relative time
 * Input:    date to format
 * Example:  {$datetime|relative_day}
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author   Eric Lamb <eric@ericlamb.net>
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_relative_day($timestamp) {
    if(!$timestamp){
        return 'N/A';
    }

    $timestamp = (int)strtotime($timestamp);
    $difference = time() - $timestamp;

    $periods = array("sec", "min", "hour", "day", "week","month", "year", "decade");
    $lengths = array("60","60","24","7","4.35","12","10");
    $total_lengths = count($lengths);

    for($j = 0; $difference > $lengths[$j] && $total_lengths > $j; $j++) {
        $difference /= $lengths[$j];
    }

    $difference = round($difference);

    if ($periods[$j] == 'sec' || $periods[$j] == 'min' || $periods[$j] == 'hour') {
        $text = "today";
    } else {
        if ($difference != 1) {
            $periods[$j].= "s";
        }
        $text = "$difference $periods[$j] $ending ago";
    }

    return $text;
}
