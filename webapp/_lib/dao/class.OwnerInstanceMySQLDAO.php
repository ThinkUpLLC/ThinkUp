<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.OwnerInstanceMySQLDAO.php
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
 * OwnerInstance Data Access Object MySQL Implementationn
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class OwnerInstanceMySQLDAO extends PDODAO implements OwnerInstanceDAO {
    /**
     *
     * Cached query results for doesOwnerHaveAccessToPost() reduces query load while looping through post results
     * @var array $post_access_query_cache
     */
    static $post_access_query_cache = array();

    public function doesOwnerHaveAccessToInstance(Owner $owner, Instance $instance) {
        // verify $owner has an id
        if (!isset($owner->id)) {
            $message = 'doesOwnerHaveAccessToInstance() requires an "Owner" object with "id" defined';
            throw new BadArgumentException($message);
        }
        // verify $instance has an id
        if (!isset($instance->id)) {
            $message = 'doesOwnerHaveAccessToInstance() requires an "Instance" object with "id" defined';
            throw new BadArgumentException($message);
        }
        if ($owner->is_admin) {
            return true;
        } else {
            $q = '
                SELECT
                    *
                FROM
                    #prefix#owner_instances oi
                INNER JOIN
                    #prefix#instances i
                ON
                    i.id = oi.instance_id
                WHERE
                    i.id = :id AND oi.owner_id = :owner_id';
            $vars = array(':owner_id' => $owner->id, ':id' => $instance->id);
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $stmt = $this->execute($q, $vars);
            return $this->getDataIsReturned($stmt);
        }
    }

    public function doesOwnerHaveAccessToPost(Owner $owner, Post $post) {
        // verify $owner has an id
        if ( !isset($owner->id)) {
            $message = 'doesOwnerHaveAccessToPost() requires an "Owner" object with "id" defined';
            throw new BadArgumentException($message);
        }
        //if post is public OR the owner is an admin, show it
        if (!$post->is_protected || $owner->is_admin) {
            return true;
        }

        //select all the network user ID's that the owner auth'ed
        $q = "SELECT  i.network_user_id
        FROM  #prefix#owner_instances oi
        INNER JOIN #prefix#instances i ON i.id = oi.instance_id
        WHERE oi.owner_id = :owner_id AND i.network = :network";

        $vars = array(':owner_id' => $owner->id, ':network'=> $post->network);
        // we'll cache query results to speed up checks while looping through post iterators
        $network_id_cache_key = implode("-", $vars) . '-network_id_cache';
        if (isset(self::$post_access_query_cache[ $network_id_cache_key ])) {
            $owner_network_user_ids = self::$post_access_query_cache[ $network_id_cache_key ];
        } else {
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $stmt = $this->execute($q, $vars);
            $owner_network_user_ids = $this->getDataRowsAsArrays($stmt);
            self::$post_access_query_cache[ $network_id_cache_key ] = $owner_network_user_ids;
        }

        // select all the network user ID's which follow protected author
        $q = "SELECT f.follower_id
        FROM  #prefix#follows f
        WHERE f.user_id = :user_id AND f.network = :network";
        $vars = array(':user_id' => $post->author_user_id, ':network'=> $post->network);
        // we'll cache query results to speed up checks while looping through post iterators
        $follower_id_cache_key = implode("-", $vars) . '-follower_id_cache';
        if (isset(self::$post_access_query_cache[ $follower_id_cache_key ])) {
            $authed_network_user_ids = self::$post_access_query_cache[ $follower_id_cache_key ];
        } else {
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $stmt = $this->execute($q, $vars);
            $authed_network_user_ids = $this->getDataRowsAsArrays($stmt);
            self::$post_access_query_cache[ $follower_id_cache_key ] = $authed_network_user_ids;
        }
        // If there's overlap, return true else return false
        foreach ($owner_network_user_ids as $owner_network_user_id) {
            foreach ($authed_network_user_ids as $authed_network_user_id) {
                if ($owner_network_user_id["network_user_id"] == $authed_network_user_id["follower_id"]) {
                    return true;
                }
            }
        }
        return false;
    }

    public function get($owner_id, $instance_id) {
        $q = "SELECT
                id, owner_id, instance_id, oauth_access_token, oauth_access_token_secret, auth_error
            FROM
                #prefix#owner_instances
            WHERE
                owner_id = :owner_id AND instance_id = :instance_id";

        $vars = array(':owner_id' => $owner_id, ':instance_id' => $instance_id);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        $owner_instance = $this->getDataRowAsObject($stmt, 'OwnerInstance');
        return $owner_instance;
    }

    public function getByInstance($instance_id) {
        $q = "SELECT
                id, owner_id, instance_id, oauth_access_token, oauth_access_token_secret, auth_error
            FROM
                #prefix#owner_instances
            WHERE  instance_id = :instance_id";

        $vars = array(':instance_id' => $instance_id);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        $owner_instances = $this->getDataRowsAsObjects($stmt, 'OwnerInstance');
        return $owner_instances;
    }

    public function getByOwner($owner_id) {
        $q = "SELECT
                id, owner_id, instance_id, oauth_access_token, oauth_access_token_secret, auth_error
            FROM
                #prefix#owner_instances
            WHERE owner_id = :owner_id";

        $vars = array(':owner_id' => $owner_id);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        $owner_instances = $this->getDataRowsAsObjects($stmt, 'OwnerInstance');
        return $owner_instances;
    }

    public function insert($owner_id, $instance_id, $oauth_token = '', $oauth_token_secret = '') {
        $q = "INSERT INTO #prefix#owner_instances
                (owner_id, instance_id, oauth_access_token, oauth_access_token_secret)
                    VALUES (:owner_id,:instance_id,:oauth_access_token,:oauth_access_token_secret)";

        $vars = array(':owner_id' => $owner_id,
                      ':instance_id' => $instance_id,
                      ':oauth_access_token' => $oauth_token,
                      ':oauth_access_token_secret' => $oauth_token_secret
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        if ( $this->getInsertCount($stmt) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($owner_id, $instance_id) {
        $q  = "DELETE FROM #prefix#owner_instances ";
        $q .= "WHERE owner_id=:owner_id AND instance_id=:instance_id;";
        $vars = array(
            ':owner_id'=>$owner_id,
            ':instance_id'=>$instance_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function deleteByInstance($instance_id) {
        $q  = "DELETE FROM #prefix#owner_instances ";
        $q .= "WHERE instance_id=:instance_id;";
        $vars = array(
            ':instance_id'=>$instance_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function updateTokens($owner_id, $instance_id, $oauth_token, $oauth_token_secret) {
        $q = 'UPDATE
                #prefix#owner_instances
            SET
                oauth_access_token=:oauth_access_token, oauth_access_token_secret=:oauth_access_token_secret
            WHERE
                owner_id = :owner_id AND instance_id = :instance_id';
        $vars = array(  ':owner_id' => $owner_id,
                        ':instance_id' => $instance_id,
                        ':oauth_access_token' => $oauth_token,
                        ':oauth_access_token_secret' => $oauth_token_secret
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        $insert_count = $this->getInsertCount($stmt);
        return ($insert_count > 0) ? true : false;
    }

    public function setAuthErrorByTokens($instance_id, $oauth_access_token, $oauth_access_token_secret,
        $auth_error="") {
        $q = "UPDATE  #prefix#owner_instances SET auth_error=:auth_error ";
        $q .= "WHERE instance_id = :instance_id AND oauth_access_token = :oauth_access_token ";
        $q .= "AND oauth_access_token_secret = :oauth_access_token_secret ";
        $vars = array(
            ':instance_id' => $instance_id,
            ':oauth_access_token_secret' => $oauth_access_token_secret,
            ':oauth_access_token' => $oauth_access_token,
            ':auth_error' => $auth_error
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        $insert_count = $this->getInsertCount($stmt);
        return ($insert_count > 0);
    }

    public function getOwnerEmailByInstanceTokens($instance_id, $oauth_access_token, $oauth_access_token_secret='') {
        $q = "SELECT o.email FROM #prefix#owners o INNER JOIN #prefix#owner_instances oi ON oi.owner_id = o.id ";
        $q .= "WHERE oi.instance_id = :instance_id AND oi.oauth_access_token = :oauth_access_token ";
        $q .= "AND oi.oauth_access_token_secret = :oauth_access_token_secret;";
        $vars = array(
            ':instance_id' => $instance_id,
            ':oauth_access_token_secret' => $oauth_access_token_secret,
            ':oauth_access_token' => $oauth_access_token
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($stmt);
        if (isset($row['email'])) {
            return $row['email'];
        } else {
            return null;
        }
    }

    public function getOAuthTokens($id) {
        $q = "SELECT
            oauth_access_token, oauth_access_token_secret, auth_error
            FROM
            #prefix#owner_instances
            WHERE
            instance_id = :instance_id ORDER BY id ASC LIMIT 1";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, array(':instance_id' => $id));
        $tokens = $this->getDataRowAsArray($stmt);
        return $tokens;
    }
}
