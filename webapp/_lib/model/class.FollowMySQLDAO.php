<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.FollowMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * Follow MySQL Data Access Object Implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Jason McPheron <jason[at]onebigword[dot]com>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 *
 */
class FollowMySQLDAO extends PDODAO implements FollowDAO {
    /**
     * @return str to add to field list to get average tweet count.
     */
    private function getAverageTweetCount() {
        $r  = "round(post_count/(datediff(curdate(), joined)), 2) ";
        $r .= " AS avg_tweets_per_day ";
        return $r;
    }

    public function followExists($user_id, $follower_id, $network) {
        $q = " SELECT user_id, follower_id ";
        $q .= " FROM #prefix#follows ";
        $q .= " WHERE user_id = :userid AND follower_id = :followerid AND network = :network;";
        $vars = array(
            ':userid'=>$user_id, 
            ':followerid'=>$follower_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataIsReturned($ps);
    }

    public function update($user_id, $follower_id, $network, $debug_api_call = '') {
        $q = " UPDATE #prefix#follows ";
        $q .= " SET last_seen=NOW(), debug_api_call = :debug";
        $q .= " WHERE user_id = :userid AND follower_id = :followerid AND network = :network ;";
        $vars = array(
            ':userid'=>$user_id, 
            ':followerid'=>$follower_id,
            ':network'=>$network,
            ':debug'=>$debug_api_call
        );
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function deactivate($user_id, $follower_id, $network, $debug_api_call = '') {
        $q = " UPDATE #prefix#follows ";
        $q .= " SET active = 0 , debug_api_call = :debug ";
        $q .= " WHERE user_id = :userid AND follower_id = :followerid AND network = :network ;";
        $vars = array(
            ':userid'=>$user_id, 
            ':followerid'=>$follower_id,
            ':network'=>$network,
            ':debug'=>$debug_api_call
        );
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function insert($user_id, $follower_id, $network, $debug_api_call = '') {
        $q  = " INSERT INTO #prefix#follows ";
        $q .= " (user_id, follower_id, last_seen, debug_api_call, network) ";
        $q .= " VALUES ( :userid, :followerid, NOW(), :debug, :network );";
        $vars = array(
            ':userid'=>$user_id, 
            ':followerid'=>$follower_id,
            ':network'=>$network,
            ':debug'=>$debug_api_call
        );
        $ps = $this->execute($q, $vars);

        return $this->getInsertCount($ps);
    }

    public function getUnloadedFollowerDetails($user_id, $network) {
        $q  = "SELECT follower_id FROM #prefix#follows AS f ";
        $q .= "WHERE f.user_id = :userid AND f.network=:network ";
        $q .= "AND f.follower_id NOT IN (SELECT user_id FROM #prefix#users WHERE network=:network) ";
        $q .= "AND f.follower_id NOT IN ";
        $q .= "   (SELECT user_id FROM #prefix#user_errors WHERE network=:network) ";
        $q .= " ORDER BY f.follower_id DESC LIMIT 100;";
        $vars = array(
            ':userid'=>$user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function countTotalFollowsWithErrors($user_id, $network) {
        $q  = " SELECT count(follower_id) as count ";
        $q .= " FROM  #prefix#follows AS f ";
        $q .= " WHERE f.user_id= :user_id AND f.network = :network AND ";
        $q .= "f.follower_id IN ";
        $q .= "(SELECT user_id FROM #prefix#user_errors ";
        $q .= " WHERE error_issued_to_user_id= :user_id AND network=:network);";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function countTotalFriendsWithErrors($user_id, $network){
        $q  = "SELECT count(follower_id) AS count ";
        $q .= " FROM #prefix#follows AS f ";
        $q .= " WHERE f.follower_id= :user_id AND f.network=:network AND";
        $q .= " f.user_id ";
        $q .= " IN ( ";
        $q .= "   SELECT user_id ";
        $q .= "   FROM #prefix#user_errors ";
        $q .= "   WHERE error_issued_to_user_id = :user_id AND network=:network )";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function countTotalFollowsWithFullDetails($user_id, $network) {
        $q  = "SELECT count( * ) AS count ";
        $q .= " FROM #prefix#follows AS f ";
        $q .= " INNER JOIN #prefix#users u ON u.user_id = f.follower_id ";
        $q .= " WHERE f.user_id = :user_id AND f.network = :network AND f.network = u.network";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function countTotalFollowsProtected($user_id, $network) {
        $q = "SELECT count( * ) AS count FROM #prefix#follows AS f ";
        $q .= "INNER JOIN #prefix#users u ON u.user_id = f.follower_id ";
        $q .= "WHERE f.user_id = :user_id AND u.is_protected = 1 AND u.network=:network AND f.network = u.network";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function countTotalFriends($user_id, $network) {
        $q = "SELECT count(f.user_id) AS count FROM #prefix#follows AS f ";
        $q .= "WHERE f.follower_id = :userid AND f.network=:network ";
        $vars = array(
            ':userid'=>$user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function countTotalFriendsProtected($user_id, $network) {
        $q = "SELECT count( * ) AS count FROM #prefix#follows AS f ";
        $q .= "INNER JOIN #prefix#users u ON u.user_id = f.user_id ";
        $q .= "WHERE f.follower_id = :userid AND u.is_protected=1 AND u.network=:network AND f.network = u.network";
        $vars = array(
            ':userid'=>$user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function getStalestFriend($user_id, $network) {
        $q  = " SELECT u.* FROM #prefix#users AS u ";
        $q .= " INNER JOIN #prefix#follows AS f ON f.user_id = u.user_id ";
        $q .= " WHERE f.follower_id= :userid AND f.network=:network ";
        $q .= " AND u.user_id NOT IN ";
        $q .= "   (SELECT user_id FROM #prefix#user_errors WHERE network=:network) ";
        $q .= " AND u.last_updated < DATE_SUB(NOW(), INTERVAL 1 DAY) ";
        $q .= " ORDER BY u.last_updated ASC LIMIT 1;";
        $vars = array(
            ':userid'=>$user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "User");
    }

    public function getOldestFollow($network) {
        $q  = " SELECT user_id AS followee_id, follower_id ";
        $q .= " FROM #prefix#follows AS f ";
        $q .= " WHERE network=:network AND active = 1 ORDER BY f.last_seen ASC LIMIT 1;";
        $vars = array( ':network'=>$network );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsArray($ps);
    }

    public function getMostFollowedFollowers($user_id, $network, $count = 20) {
        $q  = " SELECT *, ".$this->getAverageTweetCount()." ";
        $q .= " FROM  #prefix#follows AS f INNER JOIN #prefix#users AS u ";
        $q .= " ON u.user_id = f.follower_id ";
        $q .= " WHERE f.user_id = :userid AND f.network = :network and u.network=f.network AND active=1 ";
        $q .= " ORDER BY u.follower_count DESC, u.user_name DESC ";
        $q .= " LIMIT :count ;";
        $vars = array(
            ':userid'=>$user_id, 
            ':network'=>$network,
            ':count'=>(int)$count
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    //TODO: Remove hardcoded 10k follower threshold in query below
    public function getLeastLikelyFollowers($user_id, $network, $count = 20) {
        $q  = "SELECT u.*, ROUND(100*friend_count/follower_count,4) ";
        $q .= "AS LikelihoodOfFollow, ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users AS u INNER JOIN #prefix#follows AS f ";
        $q .= "ON u.user_id = f.follower_id ";
        $q .= "WHERE f.user_id = :userid AND f.network=:network AND f.network=u.network AND active=1 ";
        $q .= "AND follower_count > 10000 AND friend_count > 0 ";
        $q .= "ORDER BY LikelihoodOfFollow ASC, u.follower_count DESC ";
        $q .= "LIMIT :count ;";
        $vars = array(
            ':userid'=>$user_id, 
            ':network'=>$network,
            ':count'=>(int)$count
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    public function getEarliestJoinerFollowers($user_id, $network, $count = 20) {
        $q  = " SELECT u.*, ".$this->getAverageTweetCount()." ";
        $q .= " FROM #prefix#users AS u ";
        $q .= " INNER JOIN #prefix#follows f ON u.user_id = f.follower_id ";
        $q .= " WHERE f.user_id = :userid AND f.network=:network AND u.network=f.network AND active=1 ";
        $q .= " ORDER BY u.user_id ASC LIMIT :count ;";
        $vars = array(
            ':userid'=>$user_id, 
            ':network'=>$network,
            ':count'=>(int)$count
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getMostActiveFollowees($user_id, $network, $count = 20) {
        $q  = " SELECT u.*, ".$this->getAverageTweetCount()." ";
        $q .= " FROM #prefix#users AS u ";
        $q .= " INNER JOIN #prefix#follows AS f ON f.user_id = u.user_id ";
        $q .= " WHERE f.follower_id = :userid AND f.network=:network AND u.network=f.network AND active=1 ";
        $q .= " ORDER BY avg_tweets_per_day DESC LIMIT :count ";
        $vars = array(
            ':userid'=>$user_id, 
            ':network'=>$network,
            ':count'=>(int)$count
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getFormerFollowees($user_id, $network, $count = 20) {
        $q  = " SELECT u.* FROM #prefix#users AS u ";
        $q .= " INNER JOIN #prefix#follows AS f ON f.user_id = u.user_id ";
        $q .= " WHERE f.follower_id = :userid AND active=0 AND f.network=:network AND f.network=u.network ";
        $q .= " ORDER BY u.follower_count DESC LIMIT :count";
        $vars = array(
            ':userid'=>$user_id, 
            ':network'=>$network,
            ':count'=>(int)$count
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getFormerFollowers($user_id, $network, $count = 20) {
        $q = "select u.* FROM #prefix#users u inner join #prefix#follows f ";
        $q .= "on f.follower_id = u.user_id WHERE f.user_id = :userid AND f.network=:network AND active=0 ";
        $q .= " AND f.network=u.network order by u.follower_count DESC LIMIT :count ";
        $vars = array(
            ':userid'=>$user_id, 
            ':network'=>$network,
            ':count'=>(int)$count
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getLeastActiveFollowees($user_id, $network, $count = 20) {
        $q  = " SELECT *, ".$this->getAverageTweetCount()." ";
        $q .= " FROM #prefix#users AS u ";
        $q .= " INNER JOIN #prefix#follows AS f ON f.user_id = u.user_id ";
        $q .= " WHERE f.follower_id = :userid AND f.network=:network AND f.network=u.network AND active=1 ";
        $q .= " ORDER BY avg_tweets_per_day ASC, u.user_name ASC ";
        $q .= " LIMIT :count ";
        $vars = array(
            ':userid'=>$user_id, 
            ':network'=>$network,
            ':count'=>(int)$count
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getMostFollowedFollowees($user_id, $network, $count = 20) {
        $q  = " SELECT *, ".$this->getAverageTweetCount()." ";
        $q .= " FROM #prefix#users AS u ";
        $q .= " INNER JOIN #prefix#follows AS f ON f.user_id = u.user_id ";
        $q .= " WHERE f.follower_id = :userid AND f.network=:network AND u.network = f.network AND active=1 ";
        $q .= " ORDER BY follower_count DESC LIMIT :count ";
        $vars = array(
            ':userid'=>$user_id, 
            ':network'=>$network,
            ':count'=>(int)$count
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getMutualFriends($uid, $instance_uid, $network) {
        $q  = "SELECT u.*, ".$this->getAverageTweetCount()." ";
        $q .= " FROM #prefix#follows AS f ";
        $q .= " INNER JOIN #prefix#users AS u ON u.user_id = f.user_id ";
        $q .= " WHERE follower_id = :userid AND f.network=:network AND f.network=u.network AND active=1 ";
        $q .= " AND f.user_id IN ";
        $q .= "   (SELECT user_id FROM #prefix#follows ";
        $q .= "   WHERE follower_id = :instanceuserid AND active=1 AND network=:network) ";
        $q .= " ORDER BY follower_count ASC;";
        $vars = array(
            ':userid'=>$uid, 
            ':network'=>$network,
            ':instanceuserid'=>$instance_uid
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getFriendsNotFollowingBack($user_id, $network) {
        $q  = "SELECT u.*, ".$this->getAverageTweetCount();
        $q .= " FROM #prefix#follows AS f INNER JOIN #prefix#users AS u ";
        $q .= " ON f.user_id = u.user_id WHERE f.follower_id = :userid AND f.network=:network ";
        $q .= " AND f.user_id NOT IN ";
        $q .= "   (SELECT follower_id ";
        $q .= "   FROM #prefix#follows ";
        $q .= "   WHERE user_id=:userid AND network=:network)";
        $q .= " ORDER BY follower_count ";
        $vars = array(
            ':userid'=>$user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }
}