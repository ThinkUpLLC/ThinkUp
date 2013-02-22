<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.PlaceDAO.php
 *
 * Copyright (c) 2011-2013 Amy Unruh
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
 * Place Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @author Amy Unruh
 */
interface PlaceDAO {
    /**
     * Create a place if it does not already exist, as determined by ID.
     * @TODO Pass in a place object instead of an array, better design by contract
     * @param array $place $place['id'] (optional), $place['bounding_box'], $place['coordinates'],
     * $place['place_type'], $place['name'], $place['full_name'], $place['country_code'], $place['country'],
     *  $place['point_coords'] (optional)
     * @param int $post_id
     * @param str $network
     */
    public function insertPlace(array $place, $post_id, $network);
    /**
     * Get place by place ID.
     * @param int $place_id
     * @return array tu_place row
     */
    public function getPlaceByID($place_id);
    /**
     * Get a post's place information.
     * @param str $post_id
     * @param str $network
     * @return array Rows/columns from tu_places_posts table
     */
    public function getPostPlace($post_id, $network = 'twitter');
    /**
     * Insert a place into storage. Performs minimal pre-processing on the data passed in.
     * Note: longlat needs to be of type 'point' and bounding box of type 'polygon'
     * @var arr $place Array of data to insert
     * @var str $network Network this place is from
     * @return mixed Update count or null
     */
    public function insertGenericPlace(array $place, $network);
}
