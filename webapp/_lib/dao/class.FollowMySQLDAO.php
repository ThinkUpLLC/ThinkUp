<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.FollowMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Follow MySQL Data Access Object Implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
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

    public function followExists($user_id, $follower_id, $network, $is_active=false) {
        $q = "SELECT user_id, follower_id ";
        $q .= "FROM #prefix#follows ";
        $q .= "WHERE user_id = :user_id AND follower_id = :follower_id AND network = :network ";
        if ($is_active) {
            $q .= "AND active=1";
        }
        $q .= ";";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':follower_id'=>(string)$follower_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataIsReturned($ps);
    }

    public function update($user_id, $follower_id, $network, $debug_api_call = '') {
        $q = " UPDATE #prefix#follows ";
        $q .= "SET last_seen=NOW(), debug_api_call = :debug ";
        $q .= "WHERE user_id = :user_id AND follower_id = :follower_id AND network = :network;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':follower_id'=>(string)$follower_id,
            ':network'=>$network,
            ':debug'=>$debug_api_call
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function deactivate($user_id, $follower_id, $network, $debug_api_call = '') {
        $q = "UPDATE #prefix#follows ";
        $q .= "SET active = 0 , debug_api_call = :debug ";
        $q .= "WHERE user_id = :user_id AND follower_id = :follower_id AND network = :network;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':follower_id'=>(string)$follower_id,
            ':network'=>$network,
            ':debug'=>$debug_api_call
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function insert($user_id, $follower_id, $network, $debug_api_call = '') {
        $q  = "INSERT INTO #prefix#follows ";
        $q .= "(user_id, follower_id, first_seen, last_seen, debug_api_call, network) ";
        $q .= "VALUES ( :user_id, :follower_id, NOW(), NOW(), :debug, :network );";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':follower_id'=>(string)$follower_id,
            ':network'=>$network,
            ':debug'=>$debug_api_call
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getInsertCount($ps);
    }

    public function getUnloadedFollowerDetails($user_id, $network) {
        $q  = "SELECT follower_id FROM #prefix#follows AS f ";
        $q .= "WHERE f.user_id = :user_id AND f.network=:network ";
        $q .= "AND f.follower_id NOT IN (SELECT user_id FROM #prefix#users WHERE network=:network) ";
        $q .= "AND f.follower_id NOT IN ";
        $q .= "   (SELECT user_id FROM #prefix#user_errors WHERE network=:network) ";
        $q .= " ORDER BY f.follower_id DESC LIMIT 100;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function countTotalFollowsWithErrors($user_id, $network) {
        $q  = "SELECT count(follower_id) as count ";
        $q .= "FROM  #prefix#follows AS f ";
        $q .= "WHERE f.user_id= :user_id AND f.network = :network AND ";
        $q .= "f.follower_id IN ";
        $q .= "(SELECT user_id FROM #prefix#user_errors ";
        $q .= " WHERE error_issued_to_user_id= :user_id AND network=:network);";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function countTotalFollowsWithFullDetails($user_id, $network) {
        $q  = "SELECT count( * ) AS count ";
        $q .= " FROM #prefix#follows AS f ";
        $q .= " INNER JOIN #prefix#users u ON u.user_id = f.follower_id ";
        $q .= " WHERE f.user_id = :user_id AND f.network = :network AND f.network = u.network";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function countTotalFollowsProtected($user_id, $network) {
        $q = "SELECT count( * ) AS count FROM #prefix#follows AS f ";
        $q .= "INNER JOIN #prefix#users u ON u.user_id = f.follower_id ";
        $q .= "WHERE f.user_id = :user_id AND u.is_protected = 1 AND u.network=:network AND f.network = u.network";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function countTotalFriends($user_id, $network) {
        $q = "SELECT count(f.user_id) AS count FROM #prefix#follows AS f ";
        $q .= "WHERE f.follower_id = :user_id AND f.network=:network ";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function countTotalFriendsProtected($user_id, $network) {
        $q = "SELECT count( * ) AS count FROM #prefix#follows AS f ";
        $q .= "INNER JOIN #prefix#users u ON u.user_id = f.user_id ";
        $q .= "WHERE f.follower_id = :user_id AND u.is_protected=1 AND u.network=:network AND f.network = u.network";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function getStalestFriend($user_id, $network) {
        $q  = "SELECT u.* FROM #prefix#users AS u ";
        $q .= "INNER JOIN #prefix#follows AS f ON f.user_id = u.user_id ";
        $q .= "WHERE f.follower_id= :user_id AND f.network=:network ";
        $q .= "AND u.user_id NOT IN ";
        $q .= "   (SELECT user_id FROM #prefix#user_errors WHERE network=:network) ";
        $q .= "AND u.last_updated < DATE_SUB(NOW(), INTERVAL 1 DAY) ";
        $q .= "ORDER BY u.last_updated ASC LIMIT 1;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "User");
    }

    public function getOldestFollow($network) {
        $q  = "SELECT user_id AS followee_id, follower_id, last_seen ";
        $q .= "FROM #prefix#follows AS f ";
        $q .= "WHERE network=:network AND active = 1 ORDER BY f.last_seen ASC LIMIT 1;";
        $vars = array( ':network'=>$network );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsArray($ps);
    }

    public function getMostFollowedFollowers($user_id, $network, $count = 20, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q  = "SELECT *, ".$this->getAverageTweetCount()." ";
        $q .= "FROM  #prefix#follows AS f INNER JOIN #prefix#users AS u ";
        $q .= "ON u.user_id = f.follower_id ";
        $q .= "WHERE f.user_id = :user_id AND f.network = :network and u.network=f.network AND active=1 ";
        $q .= "ORDER BY u.follower_count DESC, u.user_name DESC ";
        $q .= "LIMIT :start_on_record, :count ;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':count'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    //TODO: Remove hardcoded 10k follower threshold in query below
    public function getLeastLikelyFollowers($user_id, $network, $count = 20, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q  = "SELECT u.*, ROUND((100*(friend_count/follower_count)),4) ";
        $q .= "AS LikelihoodOfFollow, ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users AS u INNER JOIN #prefix#follows AS f ";
        $q .= "ON u.user_id = f.follower_id ";
        $q .= "WHERE f.user_id = :user_id AND f.network=:network AND f.network=u.network AND active=1 ";
        $q .= "AND follower_count > 10000 AND friend_count > 0 ";
        $q .= "ORDER BY LikelihoodOfFollow ASC, u.follower_count DESC ";
        $q .= "LIMIT :start_on_record, :count ;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':count'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    public function getLeastLikelyFollowersThisWeek($user_id, $network, $count = 20, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q  = "SELECT u.*, ROUND((100*(friend_count/follower_count)),4) ";
        $q .= "AS likelihood_of_follow, ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users AS u INNER JOIN #prefix#follows AS f ";
        $q .= "ON u.user_id = f.follower_id ";
        $q .= "WHERE f.first_seen >= date_sub(current_date, INTERVAL 7 day) ";
        $q .= "AND f.user_id = :user_id AND f.network=:network AND f.network=u.network AND active=1 ";
        $q .= "AND follower_count > 1000 AND friend_count > 0 ";
        $q .= "ORDER BY likelihood_of_follow ASC, u.follower_count DESC ";
        $q .= "LIMIT :start_on_record, :count ;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':count'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    public function getLeastLikelyFollowersByDay($user_id, $network, $days_ago=0, $limit=10) {
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':days_ago'=>(int)$days_ago,
            ':limit'=>(int)$limit
        );
        $q  = "SELECT u.*, ROUND((100*(friend_count/follower_count)),4) ";
        $q .= "AS likelihood_of_follow, ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users AS u INNER JOIN #prefix#follows AS f ";
        $q .= "ON u.user_id = f.follower_id ";
        $q .= "WHERE f.first_seen >= date_sub(current_date, INTERVAL :days_ago day) ";
        if ($days_ago > 0) {
            $end_days_ago = $days_ago-1;
            $q .= "AND f.first_seen <= date_sub(current_date, INTERVAL :end_days_ago day) ";
            $vars['end_days_ago'] = $end_days_ago;
        }
        $q .= "AND f.user_id = :user_id AND f.network=:network AND f.network=u.network AND active=1 ";
        $q .= "AND follower_count > 1000 AND friend_count > 0 AND friend_count < (follower_count/2) ";
        $q .= "AND u.is_verified = 0 ORDER BY likelihood_of_follow ASC, u.follower_count DESC LIMIT :limit;";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, 'User');
    }

    public function getVerifiedFollowersByDay($user_id, $network, $days_ago=0, $limit=10) {
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':days_ago'=>(int)$days_ago,
            ':limit'=>(int)$limit
        );
        $q  = "SELECT u.* FROM #prefix#users AS u ";
        $q .= "INNER JOIN #prefix#follows AS f ON u.user_id = f.follower_id ";
        $q .= "WHERE f.first_seen >= date_sub(current_date, INTERVAL :days_ago day) ";
        if ($days_ago > 0) {
            $end_days_ago = $days_ago-1;
            $q .= "AND f.first_seen <= date_sub(current_date, INTERVAL :end_days_ago day) ";
            $vars['end_days_ago'] = $end_days_ago;
        }
        $q .= "AND f.user_id = :user_id AND f.network = :network AND u.network=f.network AND active=1 ";
        $q .= "AND u.is_verified = 1 ";
        $q .= "LIMIT :limit;";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, 'User');
    }

    public function getFollowersFromLocationByDay($user_id, $network, $location, $days_ago=0, $limit=10) {
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':location'=>$location,
            ':days_ago'=>(int)$days_ago,
            ':limit'=>(int)$limit
        );
        $q  = "SELECT u.* FROM #prefix#users AS u ";
        $q .= "INNER JOIN #prefix#follows AS f ON u.user_id = f.follower_id ";
        $q .= "WHERE f.first_seen >= date_sub(current_date, INTERVAL :days_ago day) ";
        if ($days_ago > 0) {
            $end_days_ago = $days_ago-1;
            $q .= "AND f.first_seen <= date_sub(current_date, INTERVAL :end_days_ago day) ";
            $vars['end_days_ago'] = $end_days_ago;
        }
        $q .= "AND f.user_id = :user_id AND f.network = :network AND u.network=f.network AND active=1 ";
        $q .= "AND u.location = :location ";
        $q .= "LIMIT :limit;";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, 'User');
    }

    public function getEarliestJoinerFollowers($user_id, $network, $count = 20, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q  = "SELECT u.*, ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users AS u ";
        $q .= "INNER JOIN #prefix#follows f ON u.user_id = f.follower_id ";
        $q .= "WHERE f.user_id = :user_id AND f.network=:network AND u.network=f.network AND active=1 ";
        $q .= "ORDER BY u.user_id ASC LIMIT :start_on_record, :count ;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':count'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getMostActiveFollowees($user_id, $network, $count = 20, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q  = "SELECT u.*, ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users AS u ";
        $q .= "INNER JOIN #prefix#follows AS f ON f.user_id = u.user_id ";
        $q .= "WHERE f.follower_id = :user_id AND f.network=:network AND u.network=f.network AND active=1 ";
        $q .= "ORDER BY avg_tweets_per_day DESC LIMIT :start_on_record, :count ";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':count'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getFormerFollowees($user_id, $network, $count = 20, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q  = "SELECT u.* FROM #prefix#users AS u ";
        $q .= "INNER JOIN #prefix#follows AS f ON f.user_id = u.user_id ";
        $q .= "WHERE f.follower_id = :user_id AND active=0 AND f.network=:network AND f.network=u.network ";
        $q .= "ORDER BY u.follower_count DESC LIMIT :start_on_record, :count";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':count'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getFormerFollowers($user_id, $network, $count = 20, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q = "select u.* FROM #prefix#users u inner join #prefix#follows f ";
        $q .= "on f.follower_id = u.user_id WHERE f.user_id = :user_id AND f.network=:network AND active=0 ";
        $q .= " AND f.network=u.network order by u.follower_count DESC LIMIT :start_on_record, :count ";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':count'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getLeastActiveFollowees($user_id, $network, $count = 20, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q  = "SELECT *, ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users AS u ";
        $q .= "INNER JOIN #prefix#follows AS f ON f.user_id = u.user_id ";
        $q .= "WHERE f.follower_id = :user_id AND f.network=:network AND f.network=u.network AND active=1 ";
        $q .= "ORDER BY avg_tweets_per_day ASC, u.user_name ASC ";
        $q .= "LIMIT :start_on_record, :count ";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':count'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getMostFollowedFollowees($user_id, $network, $count = 20, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q  = "SELECT *, ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users AS u ";
        $q .= "INNER JOIN #prefix#follows AS f ON f.user_id = u.user_id ";
        $q .= "WHERE f.follower_id = :user_id AND f.network=:network AND u.network = f.network AND active=1 ";
        $q .= "ORDER BY follower_count DESC LIMIT :start_on_record, :count ";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':count'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getMutualFriends($uid, $instance_uid, $network) {
        $q  = "SELECT u.*, ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#follows AS f ";
        $q .= "INNER JOIN #prefix#users AS u ON u.user_id = f.user_id ";
        $q .= "WHERE follower_id = :user_id AND f.network=:network AND f.network=u.network AND active=1 ";
        $q .= "AND f.user_id IN ";
        $q .= "   (SELECT user_id FROM #prefix#follows ";
        $q .= "   WHERE follower_id = :instanceuser_id AND active=1 AND network=:network) ";
        $q .= " ORDER BY follower_count ASC;";
        $vars = array(
            ':user_id'=>(string)$uid,
            ':network'=>$network,
            ':instanceuser_id'=>$instance_uid
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getFriendsNotFollowingBack($user_id, $network) {
        $q  = "SELECT u.*, ".$this->getAverageTweetCount();
        $q .= " FROM #prefix#follows AS f INNER JOIN #prefix#users AS u ";
        $q .= " ON f.user_id = u.user_id WHERE f.follower_id = :user_id AND f.network=:network ";
        $q .= " AND f.user_id NOT IN ";
        $q .= "   (SELECT follower_id ";
        $q .= "   FROM #prefix#follows ";
        $q .= "   WHERE user_id=:user_id AND network=:network)";
        $q .= " ORDER BY follower_count ";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getFolloweesRepliedToThisWeekLastYear($user_id, $network) {
        // Get dates for this week, last year
        $datetime = new DateTime('now');
        $day = $datetime->format('l');
        $datetime->modify("last year");
        $datetime->modify("last " . $day);
        $date_low = $datetime->format('Y-m-d');
        $datetime->modify("next " . $day);
        $date_high = $datetime->format('Y-m-d');

        $q = "SELECT DISTINCT u.* FROM #prefix#posts AS p ";
        $q .= "INNER JOIN #prefix#users AS u ON u.user_id = p.in_reply_to_user_id AND u.network = p.network ";
        $q .= "INNER JOIN #prefix#follows AS f ON f.user_id = p.in_reply_to_user_id AND f.network = p.network ";
        $q .= "WHERE p.author_user_id=:user_id AND p.network=:network ";
        $q .= "AND (p.pub_date>=:date_low AND p.pub_date<=:date_high) AND p.in_reply_to_user_id IS NOT NULL ";
        $q .= "AND f.follower_id=:user_id ";
        $q .= "LIMIT 12 ";

        $vars = array(
            ':user_id' => (string)$user_id,
            ':network' => $network,
            ':date_low' => $date_low,
            ':date_high' => $date_high
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_user_rows = $this->getDataRowsAsArrays($ps);
        $followees = array();
        foreach ($all_user_rows as $user_row) {
            $followee = new User($user_row);
            $followees[] = $followee;
        }

        return $followees;
    }

    public function searchFollowers(array $keywords, $network, $user_id, $page_number=1, $page_count=20) {
        //parse advanced operators
        $name_keywords = array();
        $description_keywords = array();
        foreach ($keywords as $keyword) {
            if (substr($keyword, 0, strlen('name:')) == 'name:') {
                $name_keywords[] = substr($keyword, strlen('name:'), strlen($keyword));
            } else {
                $description_keywords[] = $keyword;
            }
        }

        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
        );

        $q  = "SELECT u.*, ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users u ";
        $q .= "INNER JOIN #prefix#follows f ON f.follower_id = u.user_id AND f.network = u.network ";
        $q .= "WHERE f.user_id=:user_id AND u.network=:network AND (";

        if (count($name_keywords)>0 && count($description_keywords) >0 ) {
            $q .= "(";
            $counter = 0;
            foreach ($description_keywords as $keyword) {
                $q .= " u.description LIKE :keyword_d".$counter." ";
                if ($keyword != end($description_keywords)) {
                    $q .= "AND";
                }
                $counter++;
            }
            $q .= ") AND ( ";
            $counter = 0;
            foreach ($name_keywords as $keyword) {
                $q .= " u.full_name LIKE :keyword_n".$counter." ";
                if ($keyword != end($name_keywords)) {
                    $q .= "AND";
                }
                $counter++;
            }
            $q .= ")) ";
            $counter = 0;
            foreach ($description_keywords as $keyword) {
                $vars[':keyword_d'.$counter] = '%'.$keyword.'%';
                $counter++;
            }
            $counter = 0;
            foreach ($name_keywords as $keyword) {
                $vars[':keyword_n'.$counter] = '%'.$keyword.'%';
                $counter++;
            }
        } elseif (count($name_keywords)>0 ) {
            $counter = 0;
            foreach ($name_keywords as $keyword) {
                $q .= " u.full_name LIKE :keyword_n".$counter." ";
                if ($keyword != end($name_keywords)) {
                    $q .= "AND";
                }
                $counter++;
            }
            $q .= ") ";
            $counter = 0;
            foreach ($name_keywords as $keyword) {
                $vars[':keyword_n'.$counter] = '%'.$keyword.'%';
                $counter++;
            }
        } elseif (count($description_keywords)>0 ) {
            $counter = 0;
            foreach ($description_keywords as $keyword) {
                $q .= " u.description LIKE :keyword_d".$counter." ";
                if ($keyword != end($description_keywords)) {
                    $q .= "AND";
                }
                $counter++;
            }
            $q .= ") ";
            $counter = 0;
            foreach ($description_keywords as $keyword) {
                $vars[':keyword_d'.$counter] = '%'.$keyword.'%';
                $counter++;
            }
        }
        $q .= "ORDER BY first_seen DESC ";
        if ($page_count > 0) {
            $q .= "LIMIT :start_on_record, :limit;";
        } else {
            $q .= ';';
        }

        if ($page_count > 0) {
            $vars[':limit'] = (int)$page_count;
            $vars[':start_on_record'] = (int)$start_on_record;
        }

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsObjects($ps, 'User');
    }
}