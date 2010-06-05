<?php
/**
 * Follow MySQL Data Access Object Interface
 * 
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 */

interface FollowDAO {
    /**
     * Checks weather a given 'follow' exist in storage.
     * @param int $user_id
     * @param int $follower_id
     * @return bool true if follow exist.
     */
    public function followExists($user_id, $follower_id);

    /**
     * Updates 'last seen' in storage.
     * @param int $user_id
     * @param int $follower_id
     * @param string $debug_api_call
     * @return int update count
     */
    public function update($user_id, $follower_id, $debug_api_call = '');

    /**
     * Deactivates a 'follow' in storage.
     * @param int $user_id
     * @param int $follower_id
     * @param string $debug_api_call
     * @return int update count
     */
    public function deactivate($user_id, $follower_id, $debug_api_call = '');

    /**
     * Adds a 'follow' to storage
     * @param int $user_id
     * @param int $follower_id
     * @param string $debug_api_call
     * @return int insert count
     */
    public function insert($user_id, $follower_id, $debug_api_call = '');

    /**
     * Gets the number of follow(ers) with errors for a given user
     * @param int $user_id
     * @return int with the number
     */
    public function countTotalFollowsWithErrors($user_id);

    /**
     * Gets the number of friends with errors for a given user.
     * @param int $user_id
     * @return int with the number
     */
    public function countTotalFriendsWithErrors($user_id);

    /**
     * Gets the number of follows that have full datails.
     * @param int $user_id
     * @return int with the number
     */
    public function countTotalFollowsWithFullDetails($user_id);

    /**
     * Gets the number of follows that are protected.
     * Includes inactive friendships in count.
     * @param int $user_id
     * @return int with the number
     */
    public function countTotalFollowsProtected($user_id);

    /**
     * Count the total number of friends in storage related to a user.
     * Originally counts all the friends, also the inactive ones, 
     * this may be a subject to change. 
     * @param int $user_id
     * @return int with the number
     */
    public function countTotalFriends($user_id);

    /**
     * Gets the number of friends that is protected.
     * Includes inactive friendships in count.
     * @param int $user_id
     * @return int with the number
     */
    public function countTotalFriendsProtected($user_id);

    /**
     * Get a list of, friends without details in storage.
     * @param int $user_id
     * @return array - numbered keys, with arrays - named keys
     */
    public function getUnloadedFollowerDetails($user_id);

    /**
     * Get the friend updated the longest time ago, if age is more than 1 day.
     * @param int $user_id
     * @return User object
     */
    public function getStalestFriend($user_id);

    /**
     * Gets the person in storage seen the longest time ago.
     * @return array - named keys
     */
    public function getOldestFollow();

    /**
     * Gets the followers with most followers.
     * @param int $user_id
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getMostFollowedFollowers($user_id, $count = 20);

    /**
     * Gets the followes with highest follower:friend count.
     * @param int $user_id
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getLeastLikelyFollowers($user_id, $count = 20);

    /**
     * Gets the followers with the earliest join date.
     * @param int $user_id
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getEarliestJoinerFollowers($user_id, $count = 20);

    /**
     * Gets the friends with the highest tweet per day count.
     * @param int $user_id
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getMostActiveFollowees($user_id, $count = 20);

    /**
     * Gets a list of inactive friends.
     * @param int $user_id
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getFormerFollowees($user_id, $count = 20);

    /**
     * Gets a list of inactive followers. 
     * @param int $user_id
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getFormerFollowers($user_id, $count = 20);

    /**
     * Gets the followers with the lowest tweet-per-day ratio.
     * @param int $user_id
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getLeastActiveFollowees($user_id, $count = 20);

    /**
     * Gets the friends with the most followers
     * @param int $user_id
     * @param int $count
     * @return array - numbered keys, with arrays - named keys
     */
    public function getMostFollowedFollowees($user_id, $count = 20);

    /**
     * Gets friends that the two inputed user IDs both follow.
     * @param int $uid
     * @param int $instance_uid
     * @return array - numbered keys, with arrays - named keys
     */
    public function getMutualFriends($uid, $instance_uid);

    /**
     * Gets the friends that do not follow you back.
     * @param int $uid
     * @return array - numbered keys, with arrays - named keys
     */
    public function getFriendsNotFollowingBack($uid);

}

?>