<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.UserMySQLDAO.php
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
 * User Data Access Object MySQL Implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class UserMySQLDAO extends PDODAO implements UserDAO {
    /**
     * Get the SQL to generate average_tweets_per_day number
     * @TODO rename "tweets" "posts"
     * @return str SQL calcuation
     */
    private function getAverageTweetCount() {
        return "round(post_count/(datediff(curdate(), joined)), 2) as avg_tweets_per_day";
    }

    public function isUserInDB($user_id, $network) {
        $q = "SELECT user_id ";
        $q .= "FROM #prefix#users ";
        $q .= "WHERE user_id = :user_id AND network = :network;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }

    public function isUserInDBByName($username, $network) {
        $q = "SELECT user_id ";
        $q .= "FROM #prefix#users ";
        $q .= "WHERE user_name = :username AND network = :network";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }

    public function updateUsers($users_to_update) {
        $count = 0;
        $status_message = "";
        if (sizeof($users_to_update) > 0) {
            $status_message .= count($users_to_update)." users queued for insert or update; ";
            foreach ($users_to_update as $user) {
                $count += $this->updateUser($user);
            }
            $status_message .= "$count users affected.";
        }
        $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
        $status_message = "";
        return $count;
    }

    public function updateUser($user) {
        if (!isset($user->username)) {
            return 0;
        }
        $status_message = "";
        $has_friend_count = $user->friend_count != '' ? true : false;

        $has_favorites_count = $user->favorites_count != '' ? true : false;
        $has_last_post = $user->last_post != '' ? true : false;
        $has_last_post_id = $user->last_post_id != '' ? true : false;
        $network = $user->network != '' ? $user->network : 'twitter';
        $user->follower_count = $user->follower_count != '' ? $user->follower_count : 0;
        $user->post_count = $user->post_count != '' ? $user->post_count : 0;

        $vars = array(
            ':user_id'=>(string)$user->user_id,
            ':username'=>$user->username,
            ':full_name'=>$user->full_name,
            ':avatar'=>$user->avatar,
        	':gender'=>$user->gender,
            ':location'=>$user->location,
            ':description'=>$user->description,
            ':url'=>$user->url,
            ':is_verified'=>$this->convertBoolToDB($user->is_verified),
            ':is_protected'=>$this->convertBoolToDB($user->is_protected),
            ':follower_count'=>$user->follower_count,
            ':post_count'=>$user->post_count,
            ':found_in'=>$user->found_in,
            ':joined'=>$user->joined,
            ':network'=>$user->network
        );

        $is_user_in_storage = false;
        $is_user_in_storage = $this->isUserInDB($user->user_id, $user->network);
        if (!$is_user_in_storage) {
            $q = "INSERT INTO #prefix#users (user_id, user_name, full_name, avatar, gender, location, description, url, ";
            $q .= "is_verified, is_protected, follower_count, post_count, ".
            ($has_friend_count ? "friend_count, " : "")." ".
            ($has_favorites_count ? "favorites_count, " : "")." ".
            ($has_last_post ? "last_post, " : "")." found_in, joined, network  ".
            ($has_last_post_id ? ", last_post_id" : "").") ";
            $q .= "VALUES ( :user_id, :username, :full_name, :avatar, :gender, :location, :description, :url, :is_verified, ";
            $q .= ":is_protected, :follower_count, :post_count, ".($has_friend_count ? ":friend_count, " : "")." ".
            ($has_favorites_count ? ":favorites_count, " : "")." ".
            ($has_last_post ? ":last_post, " : "")." :found_in, :joined, :network ".
            ($has_last_post_id ? ", :last_post_id " : "")." )";
        } else {
            $q = "UPDATE #prefix#users SET full_name = :full_name, avatar = :avatar, gender = :gender, location = :location, ";
            $q .= "user_name = :username, description = :description, url = :url, is_verified = :is_verified, ";
            $q .= "is_protected = :is_protected, follower_count = :follower_count, post_count = :post_count,  ".
            ($has_friend_count ? "friend_count= :friend_count, " : "")." ".
            ($has_favorites_count ? "favorites_count= :favorites_count, " : "")." ".
            ($has_last_post ? "last_post= :last_post, " : "")." last_updated = NOW(), found_in = :found_in, ";
            $q .= "joined = :joined,  network = :network ".
            ($has_last_post_id ? ", last_post_id = :last_post_id" : "")." ";
            $q .= "WHERE user_id = :user_id AND network = :network;";
        }

        if ($has_friend_count) {
            $vars[':friend_count'] = $user->friend_count;
        }

        if ($has_favorites_count) {
            $vars[':favorites_count'] = $user->favorites_count;
        }
        if ($has_last_post) {
            $vars[':last_post'] = $user->last_post;
        }
        if ($has_last_post_id) {
            $vars[':last_post_id'] = $user->last_post_id;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $results = $this->getUpdateCount($ps);
        return $results;
    }

    public function getDetails($user_id, $network) {
        $q = "SELECT * , ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users u ";
        $q .= "WHERE u.user_id = :user_id AND u.network = :network;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, "User");
    }

    public function getUserByName($user_name, $network) {
        $q = "SELECT * , ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users u ";
        $q .= "WHERE u.user_name = :user_name AND u.network = :network";
        $vars = array(
            ':user_name'=>$user_name,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, "User");
    }

    public function deleteUsersByHashtagId($hashtag_id){
        $q = "DELETE u.* ";
        $q .= "FROM #prefix#users u INNER JOIN #prefix#posts t ON u.user_id=t.author_user_id ";
        $q .= "INNER JOIN #prefix#hashtags_posts hp ON t.post_id = hp.post_id ";
        $q .= "WHERE hp.hashtag_id= :hashtag_id;";
        $vars = array(':hashtag_id'=>$hashtag_id);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDeleteCount($ps);
    }
}
