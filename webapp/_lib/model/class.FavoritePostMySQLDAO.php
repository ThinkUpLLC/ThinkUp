<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.FavoritePostMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Amy Unruh, Gina Trapani
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
 * FavoritePost Data Access Object
 *
 * The data access object for retrieving and saving favorited posts in the ThinkUp database.
 * This class extends PostMySQLDAO, and adds access to a favorites 'join' table that is used to record information
 * about favorited posts.  The favorites table stores post id, user (author) id, favoriter id, and network.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Amy Unruh
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Amy Unruh
 *
 */
class FavoritePostMySQLDAO extends PostMySQLDAO implements FavoritePostDAO  {

    /**
     * Inserts the given post record (if it does not already exist), then creates a row in the favorites 'join' table
     * to store information about the 'favorited' relationship. $vals holds the parsed post information.
     * @param array $vals
     * @param int $favoriter_id
     * @return int
     */
    public function addFavorite($favoriter_id, $vals) {
        if (!$favoriter_id) {
            throw new Exception("error: favoriter/owner id not set");
        }
        // first add the post (if need be-- this post may have already been inserted).
        $retval = $this->addPost($vals);
        $q = " INSERT IGNORE INTO #prefix#favorites
         (status_id, author_user_id, fav_of_user_id, network)
        VALUES ( :pid, :uid, :fid, :network) ";
        $vars = array(
            ':pid' => $vals['post_id'],
            ':uid' => $vals['user_id'],
            ':fid' => $favoriter_id,
            ':network' => $vals['network']
        );
        $res = $this->execute($q, $vars);
        return $this->getUpdateCount($res);
    }

    /**
     * 'Unfavorites' a post with respect to a given user, by removing the relevant entry from
     * the favorites table.
     * @param int $tid
     * @param int $uid
     * @param str $network
     * @return array
     */
    public function unFavorite($tid, $uid, $network = 'twitter') {
        $q = " DELETE FROM #prefix#favorites where status_id = :tid AND fav_of_user_id = :uid AND network = :network";
        $vars = array(
            ':tid' => $tid,
            ':uid' => $uid,
            ':network' => $network,
        );
        $res = $this->execute($q, $vars);
        return $res;
    }

    public function getAllFPosts($owner_id, $network, $count, $page=1) {
        return $this->getAllFPostsByUserID($owner_id, $network, $count, "pub_date", "DESC", null, $page);
    }

    public function getAllFPostsUB($owner_id, $network, $count, $ub) {
        return $this->getAllFPostsByUserID($owner_id, $network, $count, "pub_date", "DESC", $ub);
    }

    public function getAllFPostsByUsername($username, $network, $count, $page=1) {
        return $this->getAllFPostsByUsernameOrderedBy($username, $network, $count, "pub_date");
    }

    /**
     * Get all favorited posts by a given user id, with configurable order by field and direction.
     * Returns either an iterator or an array, as specified by $iterator.
     * @param int $owner_id
     * @param str $network
     * @param int $count
     * @param str $order_by field name
     * @param str $direction either "DESC" or "ASC
     * @param int $ubound
     * @param int $page
     * @param bool $iterator
     * @return array Posts with link object set
     */
    private function getAllFPostsByUserID($owner_id, $network, $count, $order_by="pub_date", $direction="DESC",
    $ubound = null, $page=1, $iterator = false) {
        $direction = $direction=="DESC" ? "DESC": "ASC";
        $start_on_record = ($page - 1) * $count;
        if ( !in_array($order_by, $this->REQUIRED_FIELDS) && !in_array($order_by, $this->OPTIONAL_FIELDS  )) {
            $order_by="pub_date";
        }
        $q = "select l.*, p.*, pub_date - interval #gmt_offset# hour as adj_pub_date from (#prefix#posts p
        LEFT JOIN #prefix#favorites f on f.status_id = p.post_id) LEFT JOIN #prefix#links l on l.post_id = p.post_id 
        where f.fav_of_user_id = :owner_id AND p.network=:network ";

        if ($order_by == 'reply_count_cache') {
            $q .= "AND reply_count_cache > 0 ";
        }
        if ($order_by == 'retweet_count_cache') {
            $q .= "AND retweet_count_cache > 0 ";
        }
        if ($ubound > 0) {
            $q .= "AND p.post_id < :ubound ";
        }
        $q .= " ORDER BY ".$order_by." ".$direction." ";
        if ($count > 0) {
            // $q .= " LIMIT :limit ";
            $q .= "LIMIT :start_on_record, :limit";
        }

        $vars = array(
          ':owner_id'=>$owner_id,
          ':network'=>$network,
          ':limit'=>$count,
          ':start_on_record'=>(int)$start_on_record
        );
        if ($ubound > 0) {
            $vars[':ubound'] = $ubound;
        }
        $ps = $this->execute($q, $vars);
        if($iterator) {
            return (new PostIterator($ps));
        }
        $all_rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        foreach ($all_rows as $row) {
            $posts[] = $this->setPostWithLink($row);
        }
        return $posts;
    }

    /**
     * get favorited posts by the given username.
     * Returns either an iterator or an array, as specified by $iterator.
     * @param str $author_username
     * @param str $network
     * @param int $count
     * @param str $order_by
     * @param int $in_last_x_days
     * @param bool $iterator
     */
    private function getAllFPostsByUsernameOrderedBy($author_username, $network="twitter", $count=0,
    $order_by="pub_date", $in_last_x_days = 0, $iterator = false) {
        if ( !in_array($order_by, $this->REQUIRED_FIELDS) && !in_array($order_by, $this->OPTIONAL_FIELDS  )) {
            $order_by="pub_date";
        }
        $vars = array(
          ':author_username'=>$author_username,
          ':network'=>$network
        );
        $q = "select l.*, p.*, pub_date - interval #gmt_offset# hour as adj_pub_date from
        ((#prefix#posts p LEFT JOIN #prefix#favorites f on f.status_id = p.post_id) LEFT JOIN 
        #prefix#links l on l.post_id = p.post_id) LEFT JOIN #prefix#users u on u.user_id = f.fav_of_user_id 
        where u.user_name = :author_username AND p.network=:network ";

        if ($in_last_x_days > 0) {
            $q .= "AND pub_date >= DATE_SUB(CURDATE(), INTERVAL :in_last_x_days DAY) ";
            $vars[':in_last_x_days'] = (int)$in_last_x_days;
        }
        if ($order_by == 'reply_count_cache') {
            $q .= "AND reply_count_cache > 0 ";
        }
        if ($order_by == 'retweet_count_cache') {
            $q .= "AND retweet_count_cache > 0 ";
        }
        $q .= " ORDER BY ".$order_by." DESC ";
        if ($count) {
            $q .= " LIMIT :limit";
            $vars[':limit'] = (int)$count;
        }
        $ps = $this->execute($q, $vars);
        if($iterator) {
            return (new PostIterator($ps));
        }
        $all_rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        foreach ($all_rows as $row) {
            $posts[] = $this->setPostWithLink($row);
        }
        return $posts;
    }

    /**
     * iterator wrapper for getAllFPostsByUsernameOrderedBy
     */
    public function getAllFPostsByUsernameIterator($user_id, $network, $count = 0) {
        return $this->getAllFPostsByUsernameOrderedBy($user_id, $network="twitter", $count, null, null,
        $iterator = true);
    }

    /**
     * iterator wrapper for getAllFPostsByUserID
     */
    public function getAllFPostsIterator($user_id, $network, $count, $include_replies=true) {
        return $this->getAllFPostsByUserID($user_id, $network, $count, "pub_date",
        "DESC", null, $iterator = true);
    }
}
