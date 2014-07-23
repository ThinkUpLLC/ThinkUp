<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.LinkDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Christoffer Viken
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
 * Link Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Christoffer Viken
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 */
interface LinkDAO {
    /**
     * Inserts a link into the database.
     * @param Link $link
     * @return int insert ID
     */
    public function insert(Link $link);
    /**
     * Sets a expanded URL in storage.
     * @param str $url
     * @param str $expanded
     * @param str $title
     * @param str $image_src
     * @return int Update count
     */
    public function saveExpandedURL($url, $expanded, $title = '', $image_src = '');
    /**
     * Stores a error message.
     * @param str $url
     * @param str $error_text
     * @return int insert ID
     */
    public function saveExpansionError($url, $error_text);
    /**
     * Update a Link's title in the data store.
     * @param int $id
     * @param str $title
     * @return int Update count
     */
    public function updateTitle($id, $title);
    /**
     * Get the links posted by a user's friends.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page
     * @param bool $is_public
     * @return array with Link objects
     */
    public function getLinksByFriends($user_id, $network, $count = 15, $page = 1, $is_public = false);
    /**
     * Get the number of links posted by a user.
     * @param int $user_id
     * @param str $network
     * @param int $days_ago
     * @return int links count
     */
    public function countLinksPostedByUserSinceDaysAgo($user_id, $network, $days_ago = 7);
    /**
     * Get the links in a user's favorites.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page
     * @param bool $is_public
     * @return array with Link objects
     */
    public function getLinksByFavorites($user_id, $network, $count = 15, $page = 1, $is_public = false);
    /**
     * Get the images posted by a user's friends.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page
     * @param bool $is_public
     * @return array numbered keys, with Link objects
     */
    public function getPhotosByFriends($user_id, $network, $count = 15, $page = 1, $is_public = false);
    /**
     * Gets a number of links that has not been expanded.
     * Non standard output - Scheduled for deprecation.
     * @param int $limit
     * @return array of Link objects
     */
    public function getLinksToExpand($limit = 1500);
    /**
     * Gets all links with short URL starting with a prefix.
     * Non standard output - Scheduled for deprecation.
     * @param str $url
     * @param int $limit
     * @return array with numbered keys, with strings
     */
    public function getLinksToExpandByURL($prefix, $limit = 0);
    /**
     * Gets a link with a given ID
     * @param int $id
     * @return Link Object
     */
    public function getLinkById($id);
    /**
     * Gets the link with specified short URL.
     * @param $url
     * @return Link Object
     */
    public function getLinkByUrl($url);
    /**
     * Get links for a give post.
     * @param int $post_id
     * @param str $network
     * @return array of links table row arrays
     */
    public function getLinksForPost($post_id, $network = 'twitter');
    /**
     * Delete links given a hashtag ID.
     * @param int $hashtag_id
     * @return int Total number of affected rows
     */
    public function deleteLinksByHashtagId($hashtag_id);
    /**
     * Get links by user given a user_id.
     * @param int $user_id
     * @param str name of network
     * @param int max number of results returned.
     * @param int number of days of results needed.
     * @return array Links posted or shared by the user in the last X days, most recent first
     */
    public function getLinksByUserSinceDaysAgo($user_id, $network, $limit= 0, $days_ago = 0);
}
