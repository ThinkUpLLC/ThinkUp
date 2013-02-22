<?php
/**
 *
 * ThinkUp/webapp/_lib/view/plugins/modifier.get_plugin_path.php
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.get_plugin_path.php
 * Type:     modifier
 * Name:     get_plugin_path
 * Purpose:  For special source (like Facebook pages) return the
 *           correct path to the ThinkUp plugin.
 *           @TODO: Figure out a better way to handle this.
 * -------------------------------------------------------------
 */
function smarty_modifier_get_plugin_path($network) {
    if ($network == "facebook page") {
        return "facebook";
    } elseif ($network == "google+") {
        return "googleplus";
    } else {
        return $network;
    }
}