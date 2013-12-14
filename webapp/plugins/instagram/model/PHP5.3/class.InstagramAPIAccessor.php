<?php
/**
 *
 * ThinkUp/webapp/plugins/instagram/model/PHP5.3/class.InstagramAPIAccessor.php
 *
 * Copyright (c) 2013 Dimosthenis Nikoudis
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
 * Instagram API Accessor
 *
 * @author Dimosthenis Nikoudis <dnna[at]dnna[dot]gr>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Dimosthenis Nikoudis
 */
class InstagramAPIAccessor {
    /**
     * Make an API request.
     * @param str $path
     * @param str $access_token
     * @param str $fields Comma-delimited list of fields to return from Instagram API
     * @return array Decoded JSON response
     */
    public static function apiRequest($type, $id, $access_token, $params = array()) {
        $logger = Logger::getInstance();
        $instagram = new Instagram\Instagram($access_token);
        if ($type == 'user') {
            return $instagram->getUser($id);
        } else if ($type == 'friends') {
            $user = $instagram->getUser($id);
            return $user->getFollowers();
        } else if ($type == 'media') {
            $user = $instagram->getUser($id);
            $media = $user->getMedia($params);
            return $media;
        }
    }
}
