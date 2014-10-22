<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.FavoritePostDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Amy Unruh
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
 * FavoritePost Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Amy Unruh
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Amy Unruh
 *
 */
interface FavoritePostDAO extends PostDAO {
    /**
     * Inserts the given post record (if it does not already exist), then creates a row in the favorites 'join' table
     * to store information about the 'favorited' relationship. $vals holds the parsed post information.
     * @param int $favoriter_id
     * @param array $vals
     * @param array $entities Defaults to null
     * @param array $user_array Defaults to null
     * @return int
     */
    public function addFavorite($favoriter_id, array $vals, $entities = null, $user_array = null);
    /**
     * 'Unfavorites' a post with respect to a given user, by removing the relevant entry from
     * the favorites table.
     * @param int $tid
     * @param int $uid
     * @param str $network
     * @return int
     */
    public function unFavorite($tid, $uid, $network="twitter");
    /**
     * Wrapper function for getAllFavoritePostsByUserID. Supports pagination.
     * @param int $owner_id
     * @param str $network
     * @param int $count
     * @param int $page
     * @param bool $is_public
     * @return array Posts with link object set
     */
    public function getAllFavoritePosts($owner_id, $network, $count, $page=1, $is_public = false);
    /**
     * Wrapper function for getAllFavoritePostsByUserID. Takes an 'upper bound' argument ($ub)-- if set,
     * only posts with id < $ub are retrieved.
     * @param int $owner_id
     * @param str $network
     * @param int $count
     * @param int $ub
     * @return array Posts with link object set
     */
    public function getAllFavoritePostsUpperBound($owner_id, $network, $count, $ub);
    /**
     * Wrapper function for getAllFavoritePostsByUsernameOrderedBy
     * @param str $username
     * @param str $network
     * @param int $count
     * @return array Posts with link object set
     */
    public function getAllFavoritePostsByUsername($username, $network, $count);
    /**
     * Iterator wrapper for getAllFavoritePostsByUsernameOrderedBy
     * @param str $username
     * @param str $network
     * @param int $count
     * @return PostIterator
     */
    public function getAllFavoritePostsByUsernameIterator($username, $network, $count=0);
    /**
     * Iterator wrapper for getAllFavoritePostsByUserID
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @return PostIterator
     */
    public function getAllFavoritePostsIterator($user_id, $network, $count);
    /**
     * Get the recently favorited posts of a user.
     * @param int $author_user_id
     * @param str $network
     * @param int $count
     * @param int $page
     * @returns array Post objects
     */
    public function getRecentlyFavoritedPosts($author_user_id, $network, $count, $page=1);
    /**
     * Get all the favorited posts of a user.
     * @param int $author_user_id
     * @param str $network
     * @param int $count
     * @param int $page
     * @returns array Post objects
     */
    public function getAllFavoritedPosts($author_user_id, $network, $count, $page=1);
    /**
     * Get all the users who have favorited a post.
     * @TODO Return User objects, not array of db table rows.
     * @param int $post_id
     * @param str $network
     * @param bool $is_public
     * @return array users table array of rows who have favorited a post
     */
    public function getUsersWhoFavedPost($post_id, $network='twitter', $is_public = false);
    /**
     * Get the posts a user favorited a year ago today.
     * @param str $fav_of_user_id
     * @param str $network
     * @param str $from_date
     * @return arr Post objects
     */
    public function getFavoritesFromOneYearAgo($fav_of_user_id, $network, $from_date=null);
    /**
     * Get users who favorited most of an author's posts over the past specified number of days.
     * @param str $author_user_id
     * @param str $network
     * @param int $last_x_days
     * @return array User objects
     */
    public function getUsersWhoFavoritedMostOfYourPosts($author_user_id, $network, $last_x_days);
    /**
     * Get gender of users who favorited a post.
     * @param str $post_id
     * @param str $network
     * @return arr of count for male and female
     */
    public function getGenderOfFavoriters($post_id, $network);
    /**
     * Get gender of users who commented on a post.
     * @param str $post_id
     * @param str $network
     * @return arr of count for male and female
     */
    public function getGenderOfCommenters($post_id, $network);
    /**
     * Get bithday of users who favorited post.
     * @param $post_id
     * @return array with favoriter's birthdays
     */
    public function getBirthdayOfFavoriters($post_id);
    /**
     * Get bithday  of users who commented post.
     * @param $post_id
     * @return array with commenter's birthdays
     */
    public function getBirthdayOfCommenters($post_id);
}
