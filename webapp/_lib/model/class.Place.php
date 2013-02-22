<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Place.php
 *
 * Copyright (c) 2012-2013 Aaron Kalair
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Aaron Kalair
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 */

class Place {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var str Place ID on a given network.
     */
    var $place_id;
    /**
     * @var str Type of place.
     */
    var $place_type;
    /**
     * @var str Short name of a place.
     */
    var $name;
    /**
     * @var str Full name of a place.
     */
    var $full_name;
    /**
     * @var str Country code where the place is located.
     */
    var $country_code;
    /**
     * @var str Country where the place is located.
     */
    var $country;
    /**
     * @var str The network this place appears on in lower-case, e.g. twitter or facebook.
     */
    var $network;
    /**
     * @var point Longitude/lattitude point.
     */
    var $longlat;
    /**
     * @var polygon Bounding box of place.
     */
    var $bounding_box;
    /**
     * @var icon Icon that represents the place
     */
    var $icon;
    /**
     * @var map_image URL to a image of the map representing the area this location is in
     */
    var $map_image;

    public function __construct($row = false) {
        if ($row) {
            $this->id = $row['id'];
            $this->place_id = $row['place_id'];
            $this->place_type = $row['place_type'];
            $this->name = $row['name'];
            $this->full_name = $row['full_name'];
            $this->country_code = $row['country_code'];
            $this->country = $row['country'];
            $this->network = $row['network'];
            $this->longlat = $row['longlat'];
            $this->bounding_box = $row['bounding_box'];
            $this->icon = $row['icon'];
            $this->map_image = $row['map_image'];
        }
    }
}

