<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Location.php
 *
 * Copyright (c) 2009-2013 Ekansh Preet Singh, Mark Wilkie
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
 *
 * Location Object
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Ekansh Preet Singh, Mark Wilkie
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */

class Location {
    var $id;
    /**
     * @var str
     */
    var $short_name;
    /**
     * @var str
     */
    var $full_name;
    /**
     * @var str
     */
    var $latlng;

    /**
     * Constructor
     * @param array $val Array of key/value pairs
     */
    public function __construct($val) {
        $this->id = $val["id"];
        $this->short_name = $val["short_name"];
        $this->full_name = $val["full_name"];
        $this->latlng = $val["latlng"];
    }
}