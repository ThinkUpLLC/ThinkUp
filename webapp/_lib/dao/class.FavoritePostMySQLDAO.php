<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.FavoritePostMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Amy Unruh, Gina Trapani
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
 * FavoritePost Data Access Object
 *
 * The data access object for retrieving and saving favorited posts in the ThinkUp database.
 * This class extends PostMySQLDAO, and adds access to a favorites 'join' table that is used to record information
 * about favorited posts.  The favorites table stores post id, user (author) id, favoriter id, and network.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Amy Unruh
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
        $post = $this->getPost($vals['post_id'], $vals['network']);
        if (!$post) {
            $added_post = $this->addPostAndAssociatedInfo($vals, $entities, $user_array);
            if (!$added_post) {
                throw new Exception("Error: favorited post ID ". $vals['post_id'] .
                " is not in storage and could not be inserted.");
            }
        }

        $q = "INSERT IGNORE INTO #prefix#favorites (post_id, author_user_id, fav_of_user_id, network) ";
        $q .= "VALUES ( :post_id, :user_id, :fav_of_user_id, :network) ";
        $vars = array(
            ':post_id' => (string) $vals['post_id'],
            ':user_id' => (string) $vals['author_user_id'],
            ':fav_of_user_id' => (string) $favoriter_id,
            ':network' => $vals['network']
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $res = $this->execute($q, $vars);
        return $this->getUpdateCount($res);
    }
    public function getRecentlyFavoritedPosts($owner_id, $network, $count, $page=1, $is_public = false) {
        return $this->getAllFavoritePostsByUserID($owner_id, $network, $count, "fav_timestamp", "DESC", null,
        $page, false, $is_public);
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
        if (!in_array($order_by, $this->REQUIRED_FIELDS) && !in_array($order_by, $this->OPTIONAL_FIELDS)
            && $order_by != 'fav_timestamp') { // This is added in the query as meta-data, so allowed
            $order_by="pub_date";
        }
        if ($is_public) {
            $protected = ' AND p.is_protected = 0 ';
        } else {
            $protected = '';
        }
        $q = "SELECT p.*, f.fav_timestamp AS favorited_timestamp,
                     pub_date - interval #gmt_offset# hour AS adj_pub_date
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
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        return $all_rows;
    }

    public function getFavoritesFromOneYearAgo($fav_of_user_id, $network, $from_date=null) {
        $q = "SELECT p.*, pub_date - interval #gmt_offset# hour AS adj_pub_date ";
        $q .= "FROM #prefix#posts p INNER JOIN #prefix#favorites f on f.post_id = p.post_id
        WHERE f.fav_of_user_id = :fav_of_user_id AND p.network=:network AND p.is_protected = 0 ";

        $vars = array(
            ':fav_of_user_id'=> $fav_of_user_id,
            ':network'=>$network
        );
        if (!isset($from_date)) {
            $from_date = 'CURRENT_DATE()';
        } else {
            $from_date = "'$from_date'";
        }
        $q .= "AND (YEAR(pub_date)!=YEAR(CURRENT_DATE())) ";
        $q .= "AND (DAYOFMONTH(pub_date)=DAYOFMONTH($from_date)) AND (MONTH(pub_date)=MONTH($from_date)) ";
        $q .= "ORDER BY pub_date DESC LIMIT 5 ";

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }

        $ps = $this->execute($q, $vars);
        $rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        foreach ($rows as $row) {
            if($row->network == 'instagram') {
                $photo_dao = DAOFactory::getDAO('PhotoDAO');
                $row = $photo_dao->getPhoto($row->post_id, 'instagram');
                $posts[] = new Photo($row);
            } else {
                $posts[] = new Post($row);
            }
        }
        return $posts;
    }

    public function getUsersWhoFavoritedMostOfYourPosts($author_user_id, $network, $last_x_days) {
        //$q = "SELECT u.user_name, fav_of_user_id, count(f.post_id) AS total_likes from tu_favorites f ";
        $q = "SELECT * FROM ( ";
        $q .= "SELECT u.*, count(f.post_id) AS total_likes from #prefix#favorites f ";
        $q .= "INNER JOIN #prefix#users u ON u.user_id = f.fav_of_user_id ";
        $q .= "INNER JOIN #prefix#posts p ON f.post_id = p.post_id ";
        $q .= "WHERE f.author_user_id = :author_user_id and f.network=:network ";
        $q .= "AND f.author_user_id != f.fav_of_user_id ";
        $q .= "AND p.pub_date >= date_sub(current_date, INTERVAL :last_x_days day) ";
        $q .= "GROUP BY f.fav_of_user_id ORDER BY total_likes DESC";
        $q .= ") favs WHERE favs.total_likes > 1 LIMIT 3";

        $vars = array(
            ':author_user_id'=> $author_user_id,
            ':network'=>$network,
            ':last_x_days'=>$last_x_days
        );

        //echo Utils::mergeSQLVars($q, $vars);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }

        $ps = $this->execute($q, $vars);
        $rows = $this->getDataRowsAsArrays($ps);
        $users = array();
        foreach ($rows as $row) {
            $user = new User($row);
            $user->total_likes = $row['total_likes'];
            $users[] = $user;
        }
        return $users;
    }

    public function getGenderOfFavoriters($post_id, $network) {
        $q = "SELECT u.gender, COUNT(*) as count_gender FROM #prefix#users u ";
        $q .= "INNER JOIN #prefix#favorites f ON f.fav_of_user_id = u.user_id ";
        $q .= "WHERE f.post_id = :post_id AND f.network = :network ";
        $q .= "GROUP BY gender";

        $vars = array (
            ':post_id' => $post_id,
            ':network' => $network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod ( __METHOD__ ); }

        $ps = $this->execute ( $q, $vars );
        $rows = $this->getDataRowsAsArrays ( $ps );
        $gender = array ();
        foreach ( $rows as $row ) {
            if ($row ['gender'] == "female") {
                $gender ['female_likes_count'] = $row ['count_gender'];
            }
            if ($row ['gender'] == "male") {
                $gender ['male_likes_count'] = $row ['count_gender'];
            }
        }
        return $gender;
    }

    public function getGenderOfCommenters($post_id, $network) {
        //Only count distinct commentors, don't count a commentor twice if she's commented twice
        $q = "SELECT u.gender, COUNT(DISTINCT u.id) as count_gender FROM #prefix#users u ";
        $q .= "INNER JOIN #prefix#posts p ON p.author_user_id = u.user_id AND p.network = u.network ";
        $q .= "WHERE p.in_reply_to_post_id = :post_id AND p.network = :network AND u.user_id != p.in_reply_to_user_id ";
        $q .= "GROUP BY gender";

        $vars = array (
            ':post_id' => $post_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod ( __METHOD__ ); }

        $ps = $this->execute ( $q, $vars );
        $rows = $this->getDataRowsAsArrays ( $ps );
        $gender = array ();
        foreach ( $rows as $row ) {
            if ($row ['gender'] == "female") {
                $gender ['female_comment_count'] = $row ['count_gender'];
            }
            if ($row ['gender'] == "male") {
                $gender ['male_comment_count'] = $row ['count_gender'];
            }
        }
        return $gender;
    }

	public function getBirthdayOfFavoriters($post_id) {
		$q = "SELECT #prefix#users.birthday as birthday FROM #prefix#favorites, #prefix#users ";
		$q .= "WHERE #prefix#favorites.post_id = :post_id ";
		$q .= "AND #prefix#favorites.fav_of_user_id = #prefix#users.user_id ";

		$vars = array (
				':post_id' => $post_id
		);
		if ($this->profiler_enabled) {
			Profiler::setDAOMethod ( __METHOD__ );
		}

		$ps = $this->execute ( $q, $vars );
		$rows = $this->getDataRowsAsArrays ( $ps );
		$age = array ();
		foreach ( $rows as $row ) {
			$age[] = $row ['birthday'];
		}
		return $age;
	}

	public function getBirthdayOfCommenters($post_id) {
		$q = "SELECT #prefix#users.birthday as birthday FROM #prefix#posts, #prefix#users ";
		$q .= "WHERE #prefix#posts.in_reply_to_post_id = :post_id ";
		$q .= "AND #prefix#posts.author_user_id = #prefix#users.user_id ";

		$vars = array (
				':post_id' => $post_id
		);
		if ($this->profiler_enabled) {
			Profiler::setDAOMethod ( __METHOD__ );
		}

		$ps = $this->execute ( $q, $vars );
		$rows = $this->getDataRowsAsArrays ( $ps );
		$age = array ();
		foreach ( $rows as $row ) {
			$age[] = $row ['birthday'];
		}
		return $age;
	}
}
