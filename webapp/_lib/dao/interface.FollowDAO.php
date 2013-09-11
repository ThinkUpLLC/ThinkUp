<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.FollowDAO.php
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
 *
 * Follow Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Christoffer Viken
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 */
interface FollowDAO {
    /**
     * Checks whether a given 'follow' exist in storage.
     * @param int $user_id
     * @param int $follower_id
     * @param str $network
     * @param bool $is_active Whether or not relationship should be active only
     * @return bool true if follow exists
     */
    public function followExists($user_id, $follower_id, $network, $is_active=false);
    /**
     * Updates 'last seen' in storage.
     * @param int $user_id
     * @param int $follower_id
     * @param str $network
     * @param string $debug_api_call
     * @return int update count
     */
    public function update($user_id, $follower_id, $network, $debug_api_call = '');
    /**
     * Deactivates a 'follow' in storage.
     * @param int $user_id
     * @param int $follower_id
     * @param str $network
     * @param string $debug_api_call
     * @return int update count
     */
    public function deactivate($user_id, $follower_id, $network, $debug_api_call = '');
    /**
     * Adds a 'follow' to storage
     * @param int $user_id
     * @param int $follower_id
     * @param str $network
     * @param string $debug_api_call
     * @return int insert count
     */
    public function insert($user_id, $follower_id, $network, $debug_api_call = '');
    /**
     * Gets the number of follow(ers) with errors for a given user
     * @param int $user_id
     * @param str $network
     * @return int with the number
     */
    public function countTotalFollowsWithErrors($user_id, $network);
    /**
     * Gets the number of friends with errors for a given user.
     * @param int $user_id
     * @param str $network
     * @return int with the number
     */
    public function countTotalFriendsWithErrors($user_id, $network);
    /**
     * Gets the number of follows that have full datails.
     * @param int $user_id
     * @param str $network
     * @return int with the number
     */
    public function countTotalFollowsWithFullDetails($user_id, $network);
    /**
     * Gets the number of follows that are protected.
     * Includes inactive friendships in count.
     * @param int $user_id
     * @param str $network
     * @return int with the number
     */
    public function countTotalFollowsProtected($user_id, $network);
    /**
     * Count the total number of friends in storage related to a user.
     * Originally counts all the friends, also the inactive ones,
     * this may be a subject to change.
     * @param int $user_id
     * @param str $network
     * @return int with the number
     */
    public function countTotalFriends($user_id, $network);
    /**
     * Gets the number of friends that is protected.
     * Includes inactive friendships in count.
     * @param int $user_id
     * @param str $network
     * @return int Total protected friends
     */
    public function countTotalFriendsProtected($user_id, $network);
    /**
     * Get a list of, friends without details in storage.
     * @param int $user_id
     * @param str $network
     * @return array Numbered keys, with arrays - named keys
     */
    public function getUnloadedFollowerDetails($user_id, $network);
    /**
     * Get the friend updated the longest time ago, if age is more than 1 day.
     * @param int $user_id
     * @param str $network
     * @return User object
     */
    public function getStalestFriend($user_id, $network);
    /**
     * Gets the person in storage seen the longest time ago.
     * @param str $network
     * @return array Named keys
     */
    public function getOldestFollow($network);
    /**
     * Gets the followers with most followers.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @return array Numbered keys, with arrays - named keys
     */
    public function getMostFollowedFollowers($user_id, $network, $count = 20, $page = 1);
    /**
     * Gets the followers with highest follower:friend count.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page defaults to 1
     * @return array - numbered keys, with arrays - named keys
     */
    public function getLeastLikelyFollowers($user_id, $network, $count = 20, $page = 1);
    /**
     * Gets the followers with highest follower:friend count first seen by ThinkUp in the past 7 days.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page defaults to 1
     * @return array - numbered keys, with arrays - named keys
     */
    public function getLeastLikelyFollowersThisWeek($user_id, $network, $count = 20, $page = 1);
    /**
     * Gets the followers who are verified by the network first seen by ThinkUp a specified number of days ago.
     * @param str $user_id
     * @param str $network
     * @param int $days_ago
     * @param int $limit
     * @return array - numbered keys, with arrays - named keys
     */
    public function getVerifiedFollowersByDay($user_id, $network, $days_ago=0, $limit=10);
    /**
     * Gets the followers from a location by the network first seen by ThinkUp a specified number of days ago.
     * @param str $user_id
     * @param str $network
     * @param str $location
     * @param int $days_ago
     * @param int $limit
     **/
    public function getFollowersFromLocationByDay($user_id, $network, $location, $days_ago=0, $limit=10);
    /**
     * Gets the followers with the earliest join date.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page defaults to 1
     * @return array - numbered keys, with arrays - named keys
     */
    public function getEarliestJoinerFollowers($user_id, $network, $count = 20, $page = 1);
    /**
     * Gets the friends with the highest tweet per day count.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page defaults to 1
     * @return array - numbered keys, with arrays - named keys
     */
    public function getMostActiveFollowees($user_id, $network, $count = 20, $page = 1);
    /**
     * Gets a list of inactive friends.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page defaults to 1
     * @return array - numbered keys, with arrays - named keys
     */
    public function getFormerFollowees($user_id, $network, $count = 20, $page = 1);
    /**
     * Gets a list of inactive followers.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page defaults to 1
     * @return array - numbered keys, with arrays - named keys
     */
    public function getFormerFollowers($user_id, $network, $count = 20, $page = 1);
    /**
     * Gets the followers with the lowest tweet-per-day ratio.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page defaults to 1
     * @return array - numbered keys, with arrays - named keys
     */
    public function getLeastActiveFollowees($user_id, $network, $count = 20, $page = 1);
    /**
     * Gets the friends with the most followers
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page defaults to 1
     * @return array - numbered keys, with arrays - named keys
     */
    public function getMostFollowedFollowees($user_id, $network, $count = 20, $page = 1);
    /**
     * Gets friends that the two inputed user IDs both follow.
     * @param int $uid
     * @param int $instance_uid
     * @param str $network
     * @param int $page defaults to 1
     * @return array - numbered keys, with arrays - named keys
     */
    public function getMutualFriends($uid, $instance_uid, $network);
    /**
     * Gets the friends that do not follow you back.
     * @param int $uid
     * @param str $network
     * @return array - numbered keys, with arrays - named keys
     */
    public function getFriendsNotFollowingBack($uid, $network);
    /**
     * Gets the people you follow and replied to on this week, a year ago.
     * @param int $user_id
     * @param str $network
     * @return array - numbered keys, with arrays - named keys
     */
    public function getFolloweesRepliedToThisWeekLastYear($user_id, $network);
    /**
     * Search a user's followers names and bio. (Use name:term to search only name field.)
     * @param arr $keywords
     * @param str $network
     * @param str $user_id
     * @param int $page_number
     * @param int $page_count
     */
    public function searchFollowers(array $keywords, $network, $user_id, $page_number=1, $page_count=20);
}
