<?php
/**
 *
 * ThinkUp/webapp/_lib/dao/interface.PhotoDAO.php
 *
 * Copyright (c) 2013 Dimosthenis Nikoudis, Aaron Kalair, Gina Trapani
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
 * Photo Data Access Object Interface
 *
 * @author Dimosthenis Nikoudis, Aaron Kalair, Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Dimosthenis Nikoudis, Aaron Kalair, Gina Trapani
 *
 */
interface PhotoDAO {
    /**
     * Insert photo.
     * @param arr $vals post_key, standard_resolution_url, filter, low_resolution_url, thumbnail_url
     * @return int|bool New insert ID or false if not inserted
     */
    public function addPhoto($vals);

    /**
     * Get photo by post ID and network.
     * @param str $post_id
     * @param str $network
     * @return Photo Photo with the given post_id, null if photo doesn't exist
     */
    public function getPhoto($post_id, $network);
}