<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.LinkDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Christoffer Viken
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * Link Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Christoffer Viken
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 */
interface LinkDAO {
    /**
     * Inserts a link into the database.
     * @param str $url
     * @param str $expanded
     * @param str $title
     * @param int $post_id
     * @param str $network
     * @param bool $is_image
     * @return int insert ID
     */
    public function insert($url, $expanded, $title, $post_id, $network, $is_image = false );

    /**
     * Sets a expanded URL in storage.
     * @param str $url
     * @param str $expanded
     * @param str $title
     * @param bool $is_image
     * @return int Update count
     */
    public function saveExpandedURL($url, $expanded, $title = '', $is_image = false );

    /**
     * Stores a error message.
     * @param str $url
     * @param str $error_text
     * @return int insert ID
     */
    public function saveExpansionError($url, $error_text);

    /**
     * Updates a URL in storage.
     * @param str $url
     * @param str $expanded
     * @param str $title
     * @param int $post_id
     * @param str $network
     * @param bool $is_image
     * @return int Update count
     */
    public function update($url, $expanded, $title, $post_id, $network, $is_image = false );

    /**
     * Get the links posted by a user's friends.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page
     * @return array with Link objects
     */
    public function getLinksByFriends($user_id, $network, $count = 15, $page = 1);

    /**
     * Get the links in a user's favorites.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page
     * @return array with Link objects
     */
    public function getLinksByFavorites($user_id, $network, $count = 15, $page = 1);

    /**
     * Get the images posted by a user's friends.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page
     * @return array numbered keys, with Link objects
     */
    public function getPhotosByFriends($user_id, $network, $count = 15, $page = 1);

    /**
     * Gets a number of links that has not been expanded.
     * Non standard output - Scheduled for deprecation.
     * @param int $limit
     * @return array with numbered keys, with strings
     */
    public function getLinksToExpand($limit = 1500);

    /**
     * Gets all links with short URL statring with a prefix.
     * Non standard output - Scheduled for deprecation.
     * @param str $url
     * @return array with numbered keys, with strings
     */
    public function getLinksToExpandByURL($prefix);

    /**
     * Gets a link with a given ID
     * @param int $id
     * @return Link Object
     */
    public function getLinkById($id);

    /**
     * Gets the link with spscified short URL
     * @param $url
     * @return Link Object
     */
    public function getLinkByUrl($url);
}
