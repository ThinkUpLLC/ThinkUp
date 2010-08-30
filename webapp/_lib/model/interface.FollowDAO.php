<?php
/**
 * Follow Data Access Object Interface
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 */

interface FollowDAO {
    /**
     * Checks weather a given 'follow' exist in storage.
     * @param int $user_id
     * @param int $follower_id
     * @param str $network
     * @return bool true if follow exist.
     */
    public function followExists($user_id, $follower_id, $network);

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
    public function getMostFollowedFollowers($user_id, $network, $count = 20);

    /**
     * Gets the followes with highest follower:friend count.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getLeastLikelyFollowers($user_id, $network, $count = 20);

    /**
     * Gets the followers with the earliest join date.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getEarliestJoinerFollowers($user_id, $network, $count = 20);

    /**
     * Gets the friends with the highest tweet per day count.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getMostActiveFollowees($user_id, $network, $count = 20);

    /**
     * Gets a list of inactive friends.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getFormerFollowees($user_id, $network, $count = 20);

    /**
     * Gets a list of inactive followers.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getFormerFollowers($user_id, $network, $count = 20);

    /**
     * Gets the followers with the lowest tweet-per-day ratio.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getLeastActiveFollowees($user_id, $network, $count = 20);

    /**
     * Gets the friends with the most followers
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getMostFollowedFollowees($user_id, $network, $count = 20);

    /**
     * Gets friends that the two inputed user IDs both follow.
     * @param int $uid
     * @param int $instance_uid
     * @param str $network
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
}
