<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.FavoritePostMySQLDAO.php
 *
 * Copyright (c) 2009-2012 Amy Unruh, Gina Trapani
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
 * @copyright 2009-2012 Gina Trapani, Amy Unruh
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Amy Unruh
 *
 */
class FavoritePostMySQLDAO extends PostMySQLDAO implements FavoritePostDAO  {

    public function addFavorite($favoriter_id, array $vals, $entities = null, $user_array = null) {
        if (!$favoriter_id) {
            throw new Exception("Error: favoriter/author user ID not set");
        }
        // first add the post (if need be-- this post may have already been inserted).
        $retval = $this->addPostAndAssociatedInfo($vals, $entities, $user_array);
        $q = "INSERT IGNORE INTO #prefix#favorites (post_id, author_user_id, fav_of_user_id, network) ";
        $q .= "VALUES ( :post_id, :user_id, :fav_of_user_id, :network) ";
        $vars = array(
            ':post_id' => (string) $vals['post_id'],
            ':user_id' => (string) $vals['author_user_id'],
            ':fav_of_user_id' => (string) $favoriter_id,
            ':network' => $vals['network']
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $res = $this->execute($q, $vars);
        return $this->getUpdateCount($res);
    }

    public function unFavorite($post_id, $user_id, $network = 'twitter') {
        $q = "DELETE FROM #prefix#favorites WHERE post_id = :post_id ";
        $q .= "AND fav_of_user_id = :user_id AND network = :network";
        $vars = array(
            ':post_id' => (string) $post_id,
            ':user_id' => (string) $user_id,
            ':network' => $network,
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $res = $this->execute($q, $vars);
        return $this->getUpdateCount($res);
    }
    public function getAllFavoritePosts($owner_id, $network, $count, $page=1, $is_public = false) {
        return $this->getAllFavoritePostsByUserID($owner_id, $network, $count, "pub_date", "DESC", null,
        $page, false, $is_public);
    }
    public function getAllFavoritePostsUpperBound($owner_id, $network, $count, $ub) {
        return $this->getAllFavoritePostsByUserID($owner_id, $network, $count, "pub_date", "DESC", $ub);
    }
    public function getAllFavoritePostsByUsername($username, $network, $count) {
        return $this->getAllFavoritePostsByUsernameOrderedBy($username, $network, $count, "pub_date");
    }

    /**
     * Get all favorited posts by a given user id, with configurable order by field and direction.
     * Returns either an iterator or an array, as specified by $iterator. Supports pagination.
     * @param int $owner_id
     * @param str $network
     * @param int $count
     * @param str $order_by field name
     * @param str $direction either "DESC" or "ASC
     * @param int $ubound
     * @param int $page
     * @param bool $iterator
     * @return array Posts with link object set or PostIterator
     */
    private function getAllFavoritePostsByUserID($owner_id, $network, $count, $order_by="pub_date", $direction="DESC",
    $ubound = null, $page=1, $iterator = false, $is_public = false) {
        $direction = $direction=="DESC" ? "DESC": "ASC";
        $start_on_record = ($page - 1) * $count;
        if ( !in_array($order_by, $this->REQUIRED_FIELDS) && !in_array($order_by, $this->OPTIONAL_FIELDS  )) {
            $order_by="pub_date";
        }
        if ($is_public) {
            $protected = ' AND p.is_protected = 0 ';
        } else {
            $protected = '';
        }
        $q = "SELECT p.*, pub_date - interval #gmt_offset# hour AS adj_pub_date
        FROM (#prefix#posts p
        INNER JOIN #prefix#favorites f on f.post_id = p.post_id)
        WHERE f.fav_of_user_id = :owner_id AND p.network=:network ";
        $q .= $protected;
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
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        if ($iterator) {
            return (new PostIterator($ps));
        }
        $all_post_rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        if ($all_post_rows) {
            $post_keys_array = array();
            foreach ($all_post_rows as $row) {
                $post_keys_array[] = $row['id'];
            }

            // Get links
            $q = "SELECT * FROM #prefix#links WHERE post_key in (".implode(',', $post_keys_array).")";
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q);
            $all_link_rows = $this->getDataRowsAsArrays($ps);

            // Combine posts and links
            $posts = array();
            foreach ($all_post_rows as $post_row) {
                $post = new Post($post_row);
                foreach ($all_link_rows as $link_row) {
                    if ($link_row['post_key'] == $post->id) {
                        $post->addLink(new Link($link_row));
                    }
                }
                $posts[] = $post;
            }
        }
        return $posts;
    }

    /**
     * Get favorited posts by the given username.
     * Returns either an iterator or an array, as specified by $iterator.
     * @param str $author_username
     * @param str $network
     * @param int $count
     * @param str $order_by
     * @param int $in_last_x_days
     * @param bool $iterator
     * @return array Posts with link object set or PostIterator
     */
    private function getAllFavoritePostsByUsernameOrderedBy($author_username, $network="twitter", $count=0,
    $order_by="pub_date", $in_last_x_days = 0, $iterator = false) {
        if ( !in_array($order_by, $this->REQUIRED_FIELDS) && !in_array($order_by, $this->OPTIONAL_FIELDS  )) {
            $order_by="pub_date";
        }
        $vars = array(
          ':author_username'=>$author_username,
          ':network'=>$network
        );
        $q = "SELECT p.*, pub_date - interval #gmt_offset# hour as adj_pub_date FROM
        (#prefix#posts p INNER JOIN #prefix#favorites f on f.post_id = p.post_id)
         LEFT JOIN #prefix#users u on u.user_id = f.fav_of_user_id
        WHERE u.user_name = :author_username AND p.network=:network ";

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
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        if ($iterator) {
            return (new PostIterator($ps));
        }
        $all_post_rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        if ($all_post_rows) {
            $post_keys_array = array();
            foreach ($all_post_rows as $row) {
                $post_keys_array[] = $row['id'];
            }

            // Get links
            $q = "SELECT * FROM #prefix#links WHERE post_key in (".implode(',', $post_keys_array).")";
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q);
            $all_link_rows = $this->getDataRowsAsArrays($ps);

            // Combine posts and links
            $posts = array();
            foreach ($all_post_rows as $post_row) {
                $post = new Post($post_row);
                foreach ($all_link_rows as $link_row) {
                    if ($link_row['post_key'] == $post->id) {
                        $post->addLink(new Link($link_row));
                    }
                }
                $posts[] = $post;
            }
        }
        return $posts;
    }

    public function getAllFavoritePostsByUsernameIterator($username, $network, $count = 0) {
        return $this->getAllFavoritePostsByUsernameOrderedBy($username, $network, $count, null, null, true);
    }

    public function getAllFavoritePostsIterator($user_id, $network, $count) {
        return $this->getAllFavoritePostsByUserID($user_id, $network, $count, "pub_date", "DESC", null, 1, true);
    }

    public function getAllFavoritedPosts($author_user_id, $network, $count, $page=1) {
        return $this->getAllFavoritedPostsForUserID($author_user_id, $network, $count, $order_by="pub_date", $page);
    }

    /**
     * Get all the favorited posts of a user.
     * @TODO Use $order_by parameter to customize sort order.
     * @param int $author_user_id
     * @param str $network
     * @param int $count
     * @param str $order_by
     * @param int $page
     * @param bool $iterator Whether or not to return an iterator
     * @returns array Post objects
     */
    private function getAllFavoritedPostsForUserID($author_user_id, $network, $count, $order_by="pub_date",
    $page=1, $iterator = false) {
        // $direction = $direction=="DESC" ? "DESC": "ASC";
        $start_on_record = ($page - 1) * $count;
        // order-by information currently hardwired; this will probably change
        // if ( !in_array($order_by, $this->REQUIRED_FIELDS) && !in_array($orderaa_by, $this->OPTIONAL_FIELDS  )) {
        //     $order_by="pub_date";
        // }
        $q = "SELECT p.*, pub_date - interval #gmt_offset# hour AS adj_pub_date, ";
        //TODO: Store favlike_count_cache during Twitter crawl so we don't do this dynamic GROUP BY fakeout
        $q .= "count(*) AS favlike_count_cache ";
        $q .= "FROM (#prefix#posts p INNER JOIN #prefix#favorites f on f.post_id = p.post_id) ";
        $q .= "WHERE p.author_user_id = :author_user_id AND p.network = :network ";
        $q .= "GROUP BY p.post_text ORDER BY YEARWEEK(p.pub_date) DESC, favlike_count_cache DESC, p.pub_date DESC ";
        $q .= "LIMIT :start_on_record, :limit";
        $vars = array(
          ':author_user_id'=>(string) $author_user_id,
          ':network'=>$network,
          ':limit'=>$count,
          ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        if ($iterator) {
            return (new PostIterator($ps));
        }
        $all_post_rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        if ($all_post_rows) {
            $post_keys_array = array();
            foreach ($all_post_rows as $row) {
                $post_keys_array[] = $row['id'];
            }

            // Get links
            $q = "SELECT * FROM #prefix#links WHERE post_key in (".implode(',', $post_keys_array).")";
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q);
            $all_link_rows = $this->getDataRowsAsArrays($ps);

            // Combine posts and links
            $posts = array();
            foreach ($all_post_rows as $post_row) {
                $post = new Post($post_row);
                foreach ($all_link_rows as $link_row) {
                    if ($link_row['post_key'] == $post->id) {
                        $post->addLink(new Link($link_row));
                    }
                }
                $posts[] = $post;
            }
        }
        return $posts;
    }

    public function getUsersWhoFavedPost($post_id, $network='twitter', $is_public = false) {
        $q = "SELECT u.*  FROM #prefix#posts p ";
        $q .= "INNER JOIN #prefix#favorites as f on f.post_id = p.post_id ";
        $q .= "INNER JOIN #prefix#users u on f.fav_of_user_id = u.user_id ";
        $q .= "WHERE p.network=:network AND f.post_id=:post_id ";
        if ($is_public) {
            $q .= "AND p.is_protected = 0 ";
        }
        // could potentially order by follower count instead
        $q .= " ORDER BY fav_timestamp desc ";
        $vars = array(
            ':post_id'=>(string) $post_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        return $all_rows;
    }
}
