<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.PostMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
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
 * Post Data Access Object
 *
 * The data access object for retrieving and saving posts in the ThinkUp database
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class PostMySQLDAO extends PDODAO implements PostDAO  {

    /**
     * The minimum number of characters required for fulltext queries.
     * @var int
     */
    const FULLTEXT_CHAR_MINIMUM = 4;

    /**
     * Fields required for a post.
     * @var array
     */
    var $REQUIRED_FIELDS =  array('post_id', 'author_username','author_fullname','author_avatar','author_user_id',
    'post_text','is_protected', 'pub_date','source','network');

    /**
     * Optional fields in a post
     * @var array
     */
    var $OPTIONAL_FIELDS = array('in_reply_to_user_id', 'in_reply_to_post_id','in_retweet_of_post_id',
    'in_rt_of_user_id', 'location', 'place', 'place_id', 'geo', 'retweet_count_cache',
    'retweet_count_api', 'old_retweet_count_cache', 'favlike_count_cache',
    'reply_count_cache', 'is_reply_by_friend', 'is_retweet_by_friend',
    'reply_retweet_distance', 'is_geo_encoded', 'author_follower_count', 'permalink');

    /**
     * Sanitizes an order_by argument to avoid SQL injection and ensure that the table you're ordering by is valid.
     * @param string $order_by Column to order on.
     * @return string Sanitized column name. If the column was invalid, "pub_date" is returned.
     */
    public function sanitizeOrderBy($order_by) {
        // some order_by clauses have a table attached to the front of them, remove this for checking.
        $sans_table = preg_replace('/^[A-Za-z]\./', '', $order_by);

        // 'retweets' is a add'l query term (it represents a sum of two fields) that is not in the posts table and
        // is used in retweet queries for fetching and ordering the retweets.
        // If this is the term, just return it, otherwise process normally by checking the fields lists.
        if ($order_by == "retweets") {
            return $order_by;
        }
        if ( !in_array($sans_table, $this->REQUIRED_FIELDS) && !in_array($sans_table, $this->OPTIONAL_FIELDS  )) {
            $order_by="pub_date";
        }

        return $order_by;
    }

    public function getPost($post_id, $network, $is_public = false) {
        $q = "SELECT  u.*, p.*, p.id as post_key, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "LEFT JOIN #prefix#users AS u ON p.author_user_id = u.user_id AND u.network = p.network ";
        $q .= "WHERE p.post_id=:post_id AND p.network=:network ";
        if ($is_public) {
            $q .= 'AND p.is_protected = 0 ';
        }
        $q .= ';';
        $vars = array(
            ':post_id'=>(string)$post_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $post_row = $this->getDataRowAsArray($ps);

        if ($post_row) {
            // Get links
            $q = "SELECT * FROM #prefix#links WHERE post_key in (".$post_row['id'].")";
            //echo Utils::mergeSQLVars($q, $vars);
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $ps = $this->execute($q);
            $all_link_rows = $this->getDataRowsAsArrays($ps);

            // Combine post and links
            $post = new Post($post_row);
            foreach ($all_link_rows as $link_row) {
                if ($link_row['post_key'] == $post->id) {
                    $post->addLink(new Link($link_row));
                }
            }
            $post->author = new User($post_row);
            return $post;
        } else {
            return null;
        }
    }

    /**
     * Add author object to post
     * @param array $row
     * @return Post post with author member variable set
     */
    private function setPostWithAuthor($row) {
        $user = new User($row, '');
        $post = new Post($row);
        $post->author = $user;
        return $post;
    }

    public function getRepliesToPost($post_id, $network, $order_by = 'default', $unit = 'km', $is_public = false,
    $count= 350, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q = "SELECT u.*, p.*, (CASE p.is_geo_encoded WHEN 0 THEN 9 ELSE p.is_geo_encoded END) AS geo_status, ";
        $q .= "pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id AND u.network = :user_network ";
        $q .= "WHERE p.network=:network AND in_reply_to_post_id=:post_id ";
        if ($is_public) {
            $q .= "AND u.is_protected = 0 ";
        }

        $class_name = ucfirst($network) . 'Plugin';
        $ordering = @call_user_func($class_name.'::repliesOrdering', $order_by);
        if (empty($ordering)) {
            $ordering = "pub_date ASC ";
        } else {
            $ordering .= ", pub_date ASC ";
        }
        $q .= "ORDER BY " . $ordering. " ";

        if ($count > 0) {
            $q .= "LIMIT :start_on_record, :limit;";
        } else {
            $q .= ';';
        }

        $vars = array(
            ':post_id'=>(string)$post_id,
            ':network'=>$network,
            ':user_network'=>($network == 'facebook page') ? 'facebook' : $network
        );

        if ($count > 0) {
            $vars[':limit'] = (int)$count;
            $vars['start_on_record'] = (int)$start_on_record;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_post_rows = $this->getDataRowsAsArrays($ps);

        $replies = array();
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
            $location = array();
            foreach ($all_post_rows as $post_row) {
                if ($post_row['is_geo_encoded'] == 1) {
                    $post_row['short_location'] = $this->processLocationRows($post_row['location']);
                    if ($unit == 'mi') {
                        $post_row['reply_retweet_distance'] =
                        $this->calculateDistanceInMiles($post_row['reply_retweet_distance']);
                    }
                }

                $post = new Post($post_row);
                $user = new User($post_row, '');
                $post->author = $user;
                foreach ($all_link_rows as $link_row) {
                    if ($link_row['post_key'] == $post->id) {
                        $post->addLink(new Link($link_row));
                    }
                }
                $replies[] = $post;
            }
        }
        return $replies;
    }

    public function getRepliesToPostInRange($post_id, $network, $from, $until, $order_by = 'default', $unit = 'km',
    $is_public = false, $count = 350, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q = "SELECT u.*, p.*, (CASE p.is_geo_encoded WHEN 0 THEN 9 ELSE p.is_geo_encoded END) AS geo_status, ";
        $q .= "pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id AND u.network = :user_network ";
        $q .= "WHERE p.network=:network AND in_reply_to_post_id=:post_id AND pub_date BETWEEN :from AND :until ";
        if ($is_public) {
            $q .= "AND u.is_protected = 0 ";
        }

        $class_name = ucfirst($network) . 'Plugin';
        $ordering = @call_user_func($class_name.'::repliesOrdering', $order_by);
        if (empty($ordering)) {
            $ordering = 'pub_date ASC ';
        } else {
            $ordering .= ', pub_date ASC ';
        }
        $q .= 'ORDER BY ' . $ordering. ';';

        $vars = array(
            ':post_id'=>(string)$post_id,
            ':network'=>$network,
            ':user_network'=>($network == 'facebook page') ? 'facebook' : $network,
            ':from' => $from,
            ':until' => $until
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_post_rows = $this->getDataRowsAsArrays($ps);

        $replies = array();
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
            $location = array();
            foreach ($all_post_rows as $post_row) {
                if ($post_row['is_geo_encoded'] == 1) {
                    $post_row['short_location'] = $this->processLocationRows($post_row['location']);
                    if ($unit == 'mi') {
                        $post_row['reply_retweet_distance'] =
                        $this->calculateDistanceInMiles($post_row['reply_retweet_distance']);
                    }
                }

                $post = new Post($post_row);
                $user = new User($post_row, '');
                $post->author = $user;
                foreach ($all_link_rows as $link_row) {
                    if ($link_row['post_key'] == $post->id) {
                        $post->addLink(new Link($link_row));
                    }
                }
                $replies[] = $post;
            }
        }
        return $replies;
    }

    public function getRepliesToPostIterator($post_id, $network, $order_by = 'default', $unit = 'km',
    $is_public = false, $count = 350, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q = "SELECT u.*, p.*, (CASE p.is_geo_encoded WHEN 0 THEN 9 ELSE p.is_geo_encoded END) AS geo_status, ";
        $q .= "pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id AND u.network = :user_network ";
        $q .= "WHERE p.network=:network AND in_reply_to_post_id=:post_id ";
        if ($is_public) {
            $q .= "AND p.is_protected = 0 ";
        }
        if ($order_by == 'location') {
            $q .= "ORDER BY geo_status, reply_retweet_distance, is_reply_by_friend DESC, follower_count DESC ";
        } else {
            $q .= "ORDER BY is_reply_by_friend DESC, follower_count DESC ";
        }
        $q .= "LIMIT :start_on_record, :limit;";
        $vars = array(
            ':post_id'=>(string)$post_id,
            ':network'=>$network,
            ':limit'=>(int)$count,
            ':user_network'=>($network=='facebook page')?'facebook':$network,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return new PostIterator($ps);
    }

    public function getRetweetsOfPost($post_id, $network='twitter', $order_by = 'default', $unit = 'km',
    $is_public = false, $count = null, $page = 1) {
        $q = "SELECT u.*, p.*, (CASE p.is_geo_encoded WHEN 0 THEN 9 ELSE p.is_geo_encoded END) AS geo_status, ";
        $q .= "pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "INNER JOIN #prefix#users u on p.author_user_id = u.user_id ";
        $q .= "WHERE p.network=:network AND in_retweet_of_post_id=:post_id ";
        if ($is_public) {
            $q .= "AND p.is_protected = 0 ";
        }
        if ($order_by == 'location') {
            $q .= "ORDER BY geo_status, reply_retweet_distance, is_reply_by_friend DESC, follower_count desc ";
        } else if ($order_by != 'default') {
            $order_by = $this->sanitizeOrderBy($order_by);
            $q .= "ORDER BY $order_by DESC ";
        } else {
            $q .= "ORDER BY is_reply_by_friend DESC, follower_count DESC, p.id DESC ";
        }

        $vars = array(
            ':post_id'=>(string)$post_id,
            ':network'=>$network
        );

        if ($count != null && $count > 0) {
            $start_on_record = ($page - 1) * $count;
            $q .= 'LIMIT :start_on_record, :limit';
            $vars[':limit'] = (int)$count;
            $vars[':start_on_record'] = (int)$start_on_record;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $retweets = array();
        $location = array();
        foreach ($all_rows as $row) {
            if ($row['is_geo_encoded'] == 1) {
                $row['short_location'] = $this->processLocationRows($row['location']);
                if ($unit == 'mi') {
                    $row['reply_retweet_distance'] = $this->calculateDistanceInMiles($row['reply_retweet_distance']);
                }
            }
            $retweets[] = $this->setPostWithAuthor($row);
        }
        return $retweets;
    }

    public function getRelatedPostsArray($post_id, $network='twitter', $is_public = false, $count = 350, $page = 1,
    $geo_encoded_only = true, $include_original_post = true) {
        $start_on_record = ($page - 1) * $count;
        $q = "SELECT * FROM (
        SELECT p.*, pub_date + interval #gmt_offset# hour as adj_pub_date
        FROM #prefix#posts p
        WHERE (in_retweet_of_post_id=:post_id OR in_reply_to_post_id=:post_id) ";

        if ($include_original_post) {
            $q .= "UNION
            SELECT p.*, pub_date + interval #gmt_offset# hour as adj_pub_date
            FROM #prefix#posts p
            WHERE p.post_id=:post_id  ";
        }
        $q .= ") s ";
        if ($geo_encoded_only) {
            $q .= "WHERE is_geo_encoded='1' ";
        }
        if ($is_public) {
            if (!$geo_encoded_only) {
                $q .= "WHERE ";
            } else {
                $q .= "AND ";
            }
            $q .= "is_protected = '0' ";
        }
        $q .= "ORDER BY reply_retweet_distance, location, id LIMIT :start_on_record, :limit;";
        $vars = array(
            ':post_id'=>(string)$post_id,
            ':network'=>$network,
            ':limit'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    public function getRelatedPosts($post_id, $network='twitter', $is_public = false, $count = 350, $page = 1,
    $geo_encoded_only = true, $include_original_post = true) {
        $all_rows = $this->getRelatedPostsArray($post_id, $network, $is_public, $count, $page, $geo_encoded_only,
        $include_original_post);
        $responses = array();
        $location = array();
        foreach ($all_rows as $row) {
            if ($row['is_geo_encoded'] == 1) {
                $row['short_location'] = $this->processLocationRows($row['location']);
            }
            $responses[] = new Post($row);
        }
        return $responses;
    }

    /**
     * @TODO: Figure out a better way to do this, only returns 1-1 exchanges, not back-and-forth threads
     */
    public function getPostsAuthorHasRepliedTo($author_id, $count, $network = 'twitter', $page=1, $public_only=true) {
        $start_on_record = ($page - 1) * $count;

        $q = "SELECT p1.author_username as questioner_username, p1.author_avatar as questioner_avatar, ";
        $q .= "p2.follower_count as answerer_follower_count, p1.post_id as question_post_id, ";
        $q .= "p1.post_text as question, p1.pub_date + interval #gmt_offset# hour as question_adj_pub_date, ";
        $q .= "p.post_id as answer_post_id, p.author_username as answerer_username, ";
        $q .= "p1.is_protected as question_is_protected, p.is_protected as answer_is_protected, ";
        $q .= "p.author_avatar as answerer_avatar, p3.follower_count as questioner_follower_count, ";
        $q .= "p.post_text as answer, p.network, p.pub_date + interval #gmt_offset# hour as answer_adj_pub_date ";
        $q .= "FROM #prefix#posts p INNER JOIN #prefix#posts p1 on p1.post_id = p.in_reply_to_post_id ";
        $q .= "JOIN #prefix#users p2 on p2.user_id = :author_id ";
        $q .= "JOIN #prefix#users p3 on p3.user_id = p.in_reply_to_user_id ";
        $q .= "WHERE p.author_user_id = :author_id AND p.network=:network AND p.in_reply_to_post_id IS NOT null ";
        if ($public_only) {
            $q .= "AND p.is_protected = 0 AND p1.is_protected = 0 ";
        }
        $q .= "ORDER BY p.pub_date desc LIMIT :start_on_record, :limit;";
        $vars = array(
            ':author_id'=>(string)$author_id,
            ':network'=>$network,
            ':start_on_record'=>(int)$start_on_record,
            ':limit'=>(int)$count
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $posts_replied_to = array();
        foreach ($all_rows as $row) {
            $posts_replied_to[] = $row;
        }
        return $posts_replied_to;
    }

    public function getExchangesBetweenUsers($author_id, $other_user_id, $network='twitter') {
        $q = "SELECT   p1.author_username as questioner_username, p1.author_avatar as questioner_avatar, ";
        $q .= "p2.follower_count as questioner_follower_count, p1.post_id as question_post_id, ";
        $q .= "p1.post_text as question, p1.pub_date + interval #gmt_offset# hour as question_adj_pub_date, ";
        $q .= "p.post_id as answer_post_id,  p.author_username as answerer_username, ";
        $q .= "p.author_avatar as answerer_avatar, p3.follower_count as answerer_follower_count, ";
        $q .= "p.post_text as answer, p.network, p.pub_date + interval #gmt_offset# hour as answer_adj_pub_date ";
        $q .= "FROM  #prefix#posts p INNER JOIN #prefix#posts p1 on p1.post_id = p.in_reply_to_post_id ";
        $q .= "JOIN #prefix#users p2 on p2.user_id = :author_id ";
        $q .= "JOIN #prefix#users p3 on p3.user_id = :other_user_id ";
        $q .= "WHERE p.in_reply_to_post_id is not null AND p.network=:network AND ";
        $q .= "(p.author_user_id = :author_id AND p1.author_user_id = :other_user_id) ";
        $q .= "OR (p1.author_user_id = :author_id AND p.author_user_id = :other_user_id) ";
        $q .= "ORDER BY answer_adj_pub_date DESC, question_adj_pub_date ASC ";
        $vars = array(
            ':author_id'=>(string)$author_id,
            ':other_user_id'=>(string)$other_user_id,
            ':network'=>$network
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        $all_rows = $this->getDataRowsAsArrays($ps);
        $posts_replied_to = array();
        foreach ($all_rows as $row) {
            $posts_replied_to[] = $row;
        }
        return $posts_replied_to;
    }

    public function isPostInDB($post_id, $network) {
        $q = "SELECT post_id FROM  #prefix#posts ";
        $q .= "WHERE post_id = :post_id AND network=:network;";
        $vars = array(
            ':post_id'=>(string)$post_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }

    public function isReplyInDB($post_id, $network) {
        return $this->isPostInDB($post_id, $network);
    }

    /**
     * Increment reply cache count
     * @param int $post_id
     * @param str $network
     * @return int Number of updated rows (1 if successful, 0 if not)
     */
    private function incrementReplyCountCache($post_id, $network) {
        return $this->incrementCacheCount($post_id, $network, "reply");
    }

    /**
     * Update retweet count value from the Twitter API for 'new-style' retweets.
     * @param int $post_id
     * @param int $retweet_count_api
     * @param str $network
     * @return int Number of affected rows
     */
    private function updateAPIRetweetCount($post_id, $retweet_count_api, $network) {
        $q = "UPDATE  #prefix#posts SET retweet_count_api = :count ";
        $q .= "WHERE post_id = :post_id AND network=:network";
        $vars = array(
            ':post_id'=>(string)$post_id,
            ':network'=>$network,
            ':count' => $retweet_count_api
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    /**
     * Update the 'in_retweet_of_post_id' field for an existing post. This will be done only if
     * this field is not already set, which could be the case if the post was originally processed via
     * the stream processing scripts.
     * @param int $post_id
     * @param int $in_retweet_of_post_id
     * @param str $network
     * @return int Number of affected rows
     */
    private function updateInRetweetOfPostID($post_id, $in_retweet_of_post_id, $network) {
        $q = "UPDATE  #prefix#posts SET in_retweet_of_post_id = :rpid ";
        $q .= "WHERE post_id = :post_id AND network=:network ";
        $q .= "AND in_retweet_of_post_id IS NULL ";
        $vars = array(
            ':post_id'=>(string)$post_id,
            ':network'=>$network,
            ':rpid' => (string)$in_retweet_of_post_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    /**
     * Increment retweet cache count, for 'old-style' retweets.
     * @param int $post_id
     * @param str $network
     * @return int number of updated rows (1 if successful, 0 if not)
     */
    private function incrementRepostCountCache($post_id, $network) {
        return $this->incrementCacheCount($post_id, $network, "old_retweet");
    }

    /**
     * Increment retweet cache count, for new-style/native retweets.
     * @param int $post_id
     * @param str $network
     * @return int number of updated rows (1 if successful, 0 if not)
     */
    private function incrementNativeRTCountCache($post_id, $network) {
        return $this->incrementCacheCount($post_id, $network, "native_retweet");
    }

    /**
     * Increment either reply_count_cache, old_retweet_count_cache, retweet_count_cache, or favlike_count_cache
     * @param int $post_id
     * @param str $network
     * @param str $fieldname either "reply" , "old_retweet" (old-style retweets), "native_retweet" or "favlike"
     * @return int Number of updated rows
     */
    private function incrementCacheCount($post_id, $network, $post_type) {
        $fieldname = null;
        if ($post_type == "reply") {
            $fieldname = "reply";
        } elseif ($post_type == "native_retweet") {
            $fieldname = "retweet";
        } elseif ($post_type == "old_retweet") {
            $fieldname = "old_retweet";
        } else if ($post_type == "favlike") {
            $fieldname ="favlike";
        }
        if ($fieldname) {
            $q = "UPDATE  #prefix#posts SET ".$fieldname."_count_cache = ".$fieldname."_count_cache + 1 ";
            $q .= "WHERE post_id = :post_id AND network=:network";
            $vars = array(
                ':post_id'=>(string)$post_id,
                ':network'=>$network
            );
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        } else {
            // log error -- unknown field name
            $this->logger->logError("unknown field name in incrementCacheCount with $post_id, $post_type",
            __METHOD__.','.__LINE__);
            return null;
        }
    }

    /**
     * Checks to see if the $vals array contains all the required fields to insert a post
     * @param array $vals
     * @return bool
     */
    private function hasAllRequiredFields($vals) {
        $result = true;
        foreach ($this->REQUIRED_FIELDS as $field) {
            if ( !isset($vals[$field]) ) {
                $this->logger->logError("Missing post $field value", __METHOD__.','.__LINE__);
                $result = false;
            }
        }
        return $result;
    }

    public function addPostAndAssociatedInfo(array $vals, $entities = null, $user_array = null) {
        $urls = null;
        // first add post
        $new_post_key = $this->addPost($vals);
        // if post did not already exist
        if ($new_post_key) {
            if ($user_array) {
                $u = new User($user_array);
                $user_dao = DAOFactory::getDAO('UserDAO');
                $user_dao->setLoggerInstance($this->logger);
                $user_dao->updateUser($u);
            }

            if ($entities && isset($entities['urls'])) {
                $urls = $entities['urls'];
            }
            $urls = URLProcessor::processPostURLs($vals['post_text'], $vals['post_id'], 'twitter', $this->logger,$urls);
            if ($vals['photos']) {
                $link_dao = DAOFactory::getDAO('LinkDAO');
                foreach ($vals['photos'] as $photo) {
                    $photo = (object) $photo;
                    $link_dao->saveExpandedURL($photo->url, $photo->expanded_url, null, $photo->media_url, null);
                }
            }

            if (isset($entities)) {
                if (isset($entities['mentions'])) {
                    $mention_dao = DAOFactory::getDAO('MentionDAO');
                    $mention_dao->setLoggerInstance($this->logger);
                    $mention_dao->insertMentions($entities['mentions'], $vals['post_id'], $vals['author_user_id'],
                    $vals['network']);
                }
                if (isset($entities['hashtags'])) {
                    $hashtagpost_dao = DAOFactory::getDAO('HashtagPostDAO');
                    $hashtagpost_dao->setLoggerInstance($this->logger);
                    $hashtagpost_dao->insertHashtagPosts($entities['hashtags'], $vals['post_id'], $vals['network']);
                }

                if (isset($entities['place'])) {
                    $place = $entities['place'];
                    if ($place) {
                        $place_dao = DAOFactory::getDAO('PlaceDAO');
                        $place_dao->setLoggerInstance($this->logger);
                        $place_dao->insertPlace($place, $vals['post_id'], $vals['network']);
                    }
                }
            }
        }
        return $new_post_key;
    }

    public function addPost($vals) {
        $vals['post_id'] = (string)$vals['post_id'];
        $retweeted_post_data = null;
        // first, check to see if this is a retweet, with the original post available.
        if (isset($vals['retweeted_post'])) {
            // $this->logger->logDebug("this is a retweet- first processing original.", __METHOD__.','.__LINE__);
            $retweeted_post_data = $vals['retweeted_post']['content'];
            // it turns out that for a native retweet, Twitter may not reliably update the count stored in the
            // original-- there may be a lag.  So, if there is a first retweet, the original post still
            // may show 0 rts. This is less common w/ the REST API than the streaming API, but does not hurt to
            // address it anyway. So, if we know there was a retweet, but the rt count is showing 0, set it to 1.
            // We know it is at least 1.
            if (isset($retweeted_post_data['retweet_count_api']) && ($retweeted_post_data['retweet_count_api'] == 0 )) {
                $retweeted_post_data['retweet_count_api'] = 1;
            }
            // since this was a retweet, process the original post first.
            if (isset($vals['retweeted_post']['entities'])) {// REST API won't set this
                $retweeted_post_entities = $vals['retweeted_post']['entities'];
            } else {
                $retweeted_post_entities = null;
            }
            if (isset($vals['retweeted_post']['user_array'])) {// REST API won't set this
                $retweeted_post_user_array = $vals['retweeted_post']['user_array'];
            } else {
                $retweeted_post_user_array = null;
            }
            $this->addPostAndAssociatedInfo($retweeted_post_data, $retweeted_post_entities,
                $retweeted_post_user_array);
        }

        if ($this->hasAllRequiredFields($vals)) {
            //process location information
            if (!isset($vals['location']) && !isset($vals['geo']) && !isset($vals['place'])) {
                $vals['is_geo_encoded'] = 6;
            }
            //process reply
            if (isset($vals['in_reply_to_post_id']) && $vals['in_reply_to_post_id'] != '') {
                $vals['in_reply_to_post_id'] = (string) $vals['in_reply_to_post_id'];
                $replied_to_post = $this->getPost($vals['in_reply_to_post_id'], $vals['network']);
                if (isset($replied_to_post)) {
                    //check if reply author is followed by the original post author
                    $follow_dao = DAOFactory::getDAO('FollowDAO');
                    if ($follow_dao->followExists($vals['author_user_id'], $replied_to_post->author_user_id,
                    $replied_to_post->network)) {
                        $vals['is_reply_by_friend'] = 1;
                        $this->logger->logInfo("Found reply by a friend!", __METHOD__.','.__LINE__);
                    }
                }
            }
            //process retweet
            if (isset($vals['in_retweet_of_post_id']) && $vals['in_retweet_of_post_id'] != '') {
                $vals['in_retweet_of_post_id'] = (string) $vals['in_retweet_of_post_id'];
                if (isset($retweeted_post_data)) {
                    // don't need database retrieval to get the necessary data-- use attached orig post info
                    $retweeted_post = $retweeted_post_data;
                    $orig_author_id = $retweeted_post_data['author_user_id'];
                    $orig_network = $retweeted_post_data['network'];
                } else {
                    // do database query
                    $retweeted_post = $this->getPost($vals['in_retweet_of_post_id'], $vals['network']);
                    if (isset($retweeted_post)) {
                        $orig_author_id = $retweeted_post->author_user_id;
                        $orig_network = $retweeted_post->network;
                    }
                }
                if (isset($orig_author_id) && isset($orig_network)) {
                    $follow_dao = DAOFactory::getDAO('FollowDAO');
                    if ($follow_dao->followExists($vals['author_user_id'], $orig_author_id, $orig_network)) {
                        $vals['is_retweet_by_friend'] = 1;
                        $this->logger->logInfo("Found retweet by a friend!", __METHOD__.','.__LINE__);
                    }
                }
            }
            //SQL variables to bind
            $vars = array();
            //SQL query
            $q = "INSERT IGNORE INTO #prefix#posts SET ";
            //Set up required fields
            foreach ($this->REQUIRED_FIELDS as $field) {
                $q .= $field."=:".$field.", ";
                $vars[':'.$field] = $vals[$field];
            }
            //Set up any optional fields
            foreach ($this->OPTIONAL_FIELDS as $field) {
                if (isset($vals[$field]) && $vals[$field] != '') {
                    $q .= "".$field."=:".$field.", ";
                    $vars[':'.$field] = $vals[$field];
                }
            }
            //Trim off that last comma and space
            $q = substr($q, 0, (strlen($q)-2));
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $ps = $this->execute($q, $vars);
            $res = $this->getInsertId($ps);

            if (isset($vals['retweet_count_api']) && ($vals['retweet_count_api'] > 0 ) && !$res) {
                // then the post already existed in database & has RT count > 0, so just update the retweet count.
                $this->updateAPIRetweetCount($vals['post_id'], $vals['retweet_count_api'], $vals['network']);
            }
            if (isset($vals['favlike_count_cache']) && ($vals['favlike_count_cache'] > 0 ) && !$res) {
                // then the post already existed in database & has fav count > 0, so just update the fav count.
                $this->updateFavLikeCount($vals['post_id'], $vals['network'], $vals['favlike_count_cache'] );
            }
            // if the post was already in the database, but its 'in_retweet_of_post_id' field was not set in the
            // earlier insert, then we need to update the existing post to set that info, then increment the old-style
            // retweet cache count for the original post it references as well.  This situation can arise if the
            // stream processor has already seen and saved the old-style retweet (the stream processor won't have
            // the in_retweet_of_post_id information for old-style RTs, so a post may first just be treated as a
            // mention, but the crawler may later ID it as a RT.).
            if (!$res && !isset($retweeted_post_data) && isset($vals['in_retweet_of_post_id']) &&
            $this->updateInRetweetOfPostID($vals['post_id'], $vals['in_retweet_of_post_id'], $vals['network'])) {
                $this->incrementRepostCountCache($vals['in_retweet_of_post_id'], $vals['network']);
                $status_message = "Repost of ".$vals['in_retweet_of_post_id']." by ".$vals["author_username"].
                " ID: ".$vals["post_id"]."; updating old-style retweet cache count";
                $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
            }
            //otherwise, only update the other post records if insert went through.
            //This avoids incorrect counts in case of attempt to insert dup.
            if ($res) {
                if (isset($replied_to_post)) {
                    $this->incrementReplyCountCache($vals['in_reply_to_post_id'], $vals['network']);
                    $status_message = "Reply found for ".$vals['in_reply_to_post_id'].", ID: ".$vals["post_id"].
                    "; updating reply cache count";
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                }
                if (isset($retweeted_post)) {
                    if (!isset($retweeted_post_data)) { // if not a native retweet
                        $this->incrementRepostCountCache($vals['in_retweet_of_post_id'], $vals['network']);
                        $status_message = "Repost of ".$vals['in_retweet_of_post_id']." by ".$vals["author_username"].
                        " ID: ".$vals["post_id"]."; updating old-style retweet cache count";
                        $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                    } else { // native retweet
                        $count = $this->incrementNativeRTCountCache($vals['in_retweet_of_post_id'], $vals['network']);
                        $status_message = "Retweet of ".$vals['in_retweet_of_post_id']." by ".
                        $vals["author_username"]. " ID: ".$vals["post_id"];
                        if ($count > 0) {
                            $status_message .= "; Updated native retweet cache count sucessfully.";
                        } else {
                            $status_message .= "; Attempt to update native retweet cache count failed";
                        }
                        $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                    }
                }
            }
            return $res;
        } else {
            $status_message = "Could not insert post ID ".$vals["post_id"].", missing values";
            $this->logger->logError($status_message, __METHOD__.','.__LINE__);
            //doesn't have all req'd values
            return false;
        }
    }

    public function getAllPosts($author_id, $network, $count, $page=1, $include_replies=true,
    $order_by = 'pub_date', $direction = 'DESC', $is_public = false) {
        return $this->getAllPostsByUserID($author_id, $network, $count, $order_by, $direction, $include_replies, $page,
        false, $is_public);
    }

    public function getAllPostsIterator($author_id, $network, $count, $include_replies=true,
    $order_by = 'pub_date', $direction = 'DESC', $is_public = false) {
        return $this->getAllPostsByUserID($author_id, $network, $count, $order_by, $direction, $include_replies, 1,
        $iterator = true, $is_public);
    }

    /**
     * Iterator wrapper for getPostsByFriends
     */
    public function getPostsByFriendsIterator($user_id, $network, $count, $is_public=false) {
        return $this->getPostsByFriends($user_id, $network, $count, 1, $is_public, true);
    }

    public function getPostsByFriends($user_id, $network, $count = 15, $page = 1, $is_public = false,
    $iterator = false) {

        $start_on_record = ($page - 1) * $count;
        if ($is_public) {
            $protected = 'AND p.is_protected = 0 ';
        } else {
            $protected = '';
        }

        $q  = "SELECT p.*, pub_date + interval #gmt_offset# hour AS adj_pub_date ";
        $q .= "FROM #prefix#posts AS p ";
        $q .= "WHERE p.network = :network ";
        $q .= $protected;
        $q .= "AND p.author_user_id IN ( ";
        $q .= "   SELECT user_id FROM #prefix#follows AS f ";
        $q .= "   WHERE f.follower_id=:user_id AND f.active=1 AND f.network=:network ";
        $q .= ")";
        $q .= "ORDER BY p.id DESC ";
        $q .= "LIMIT :start_on_record, :limit";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':limit'=>$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        if ($iterator) {
            return (new PostIterator($ps));
        }
        $all_rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        foreach ($all_rows as $row) {
            $posts[] = new Post($row);
        }
        return $posts;
    }

    public function getPostsToUserIterator($user_id, $network, $count, $is_public=false) {
        return $this->getPostsToUser($user_id, $network, $count, 1, $is_public, true);
    }

    public function getPostsToUser($user_id, $network, $count = 15, $page = 1, $is_public = false, $iterator = false) {
        $start_on_record = ($page - 1) * $count;
        if ($is_public) {
            $protected = 'AND p.is_protected = 0 ';
        } else {
            $protected = '';
        }

        $q  = "SELECT p.*, pub_date + interval #gmt_offset# hour AS adj_pub_date ";
        $q .= "FROM #prefix#posts AS p ";
        $q .= "WHERE p.network = :network ";
        $q .= $protected;
        $q .= "AND in_reply_to_post_id IS NULL ";
        $q .= "AND p.in_reply_to_user_id = :user_id ";
        $q .= "ORDER BY p.id DESC ";
        $q .= "LIMIT :start_on_record, :limit";
        $vars = array(
            ':user_id'=>(string)$user_id,
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

    public function getAllQuestionPosts($author_id, $network, $count, $page=1, $order_by = 'pub_date',
    $direction = 'DESC', $is_public = false) {
        $start_on_record = ($page - 1) * $count;

        $order_by = $this->sanitizeOrderBy($order_by);

        $direction = $direction == 'DESC' ? 'DESC' : 'ASC';

        if ($is_public) {
            $protected = 'AND p.is_protected = 0';
        } else {
            $protected = '';
        }

        $q = "SELECT p.*, pub_date + interval #gmt_offset# hour as adj_pub_date FROM ( SELECT * ";
        $q .= "FROM #prefix#posts p ";
        $q .= "WHERE p.author_user_id = :author_id AND p.network=:network ";
        $q .= "AND (in_reply_to_post_id IS null OR in_reply_to_post_id = 0) $protected) AS p ";
        $q .= "WHERE post_text RLIKE :format1 OR post_text like :format2 ";
        $q .= "ORDER BY " . $order_by. ' ' . $direction . ' ';
        $q .= "LIMIT :start_on_record, :limit";
        $vars = array(
            ':author_id'=>(string)$author_id,
            ':network'=>$network,
            ':format1'=>"\\?$",
            ':format2'=>"%\\? %",
            ':limit'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
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

    public function getAllQuestionPostsInRange($author_id, $network, $count, $from, $until, $page=1,
    $order_by = 'pub_date', $direction = 'DESC', $is_public = false) {

        $order_by = $this->sanitizeOrderBy($order_by);

        $direction = $direction == 'DESC' ? 'DESC' : 'ASC';

        if ($is_public) {
            $protected = 'AND p.is_protected = 0';
        } else {
            $protected = '';
        }

        $q = "SELECT p.*, pub_date + interval #gmt_offset# hour as adj_pub_date FROM ( SELECT * ";
        $q .= "FROM #prefix#posts p ";
        $q .= "WHERE p.author_user_id = :author_id AND p.network=:network AND pub_date BETWEEN :from AND :until ";
        $q .= "AND (in_reply_to_post_id IS null OR in_reply_to_post_id = 0) $protected) AS p ";
        $q .= "WHERE post_text RLIKE :format1 OR post_text like :format2 ";
        $q .= "ORDER BY " . $order_by. ' ' . $direction . ' ';
        $vars = array(
            ':author_id'=>(string)$author_id,
            ':network'=>$network,
            ':format1'=>"\\?$",
            ':format2'=>"%\\? %",
            ':from' => $from,
            ':until' => $until
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
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
     * Get all posts by a given user with configurable order by field and direction
     * @param int $author_id
     * @param str $network
     * @param int $count
     * @param str $order_by field name
     * @param str $direction either "DESC" or "ASC
     * @param bool $include_replies If true, return posts with in_reply_to_post_id set, if not don't
     * @param int $page Page number, defaults to 1
     * @param bool $iterator Specify whether or not you want a post iterator returned. Default to
     * false.
     * @param bool $is_public Whether or not these results are going to be displayed publicly. Defaults to false.
     * @return array Posts with link object set
     */
    private function getAllPostsByUserID($author_id, $network, $count, $order_by="pub_date", $direction="DESC",
    $include_replies=true, $page=1, $iterator=false, $is_public = false) {
        $direction = $direction=="DESC" ? "DESC": "ASC";
        $start_on_record = ($page - 1) * $count;
        $order_by = $this->sanitizeOrderBy($order_by);

        // if the 'order_by' string is 'retweets', add an add'l aggregate (sum of two fields) var to the select,
        // which we can then sort on.
        if ($order_by == 'retweets') {
            $q = "SELECT p.*, (p.retweet_count_cache + p.old_retweet_count_cache) as retweets, ".
            "pub_date + interval #gmt_offset# hour as adj_pub_date ";
        } else {
            $q = "SELECT p.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        }
        $q .= "FROM #prefix#posts p ";
        $q .= "WHERE author_user_id = :author_id AND p.network=:network ";
        if (!$include_replies) {
            $q .= "AND (in_reply_to_post_id IS null OR in_reply_to_post_id = 0) ";
        }
        if ($order_by == 'reply_count_cache') {
            $q .= "AND reply_count_cache > 0 ";
        }
        if ($order_by == 'retweets') {
            $q .= "AND (p.retweet_count_cache + p.old_retweet_count_cache) > 0 ";
        }
        if ($is_public) {
            $q .= 'AND p.is_protected = 0 ';
        }
        $q .= "ORDER BY $order_by $direction ";
        $vars = array(
            ':author_id'=>$author_id,
            ':network'=>$network,
        );
        if (isset($count) && $count > 0) {
            $q .= "LIMIT :start_on_record, :limit";
            $vars[':limit'] = (int)$count;
            $vars[':start_on_record'] = (int)$start_on_record;
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

    public function getHotPosts($author_user_id, $network, $count) {
        // Get posts
        $q  = "SELECT p.*, (p.retweet_count_cache + p.old_retweet_count_cache) as retweets, ";
        $q .= "pub_date + interval #gmt_offset# hour as adj_pub_date FROM #prefix#posts p ";
        $q .= "WHERE p.author_user_id = :author_user_id AND p.network=:network ";
        $q .= "AND (p.reply_count_cache + p.favlike_count_cache + p.retweet_count_cache + p.old_retweet_count_cache) ";
        $q .= "> 0 ";
        $q .= "AND (p.in_reply_to_post_id IS null OR p.in_reply_to_post_id = 0) ";
        $q .= "AND (p.in_reply_to_user_id IS null OR p.in_reply_to_user_id = 0) ";
        $q .= "ORDER BY p.pub_date DESC LIMIT :limit";
        $vars = array(
            ':author_user_id'=>$author_user_id,
            ':network'=>$network,
            ':limit'=>$count
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $posts = array();
        $all_post_rows = $this->getDataRowsAsArrays($ps);
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

    public function getPostsByUserInRange($author_id, $network, $from, $until, $order_by="pub_date", $direction="DESC",
    $iterator=false, $is_public = false) {
        $direction = $direction=="DESC" ? "DESC": "ASC";

        $order_by = $this->sanitizeOrderBy($order_by);

        $q = "SELECT p.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "WHERE author_user_id = :author_id AND p.network=:network AND pub_date BETWEEN :from AND :until ";
        if ($order_by == 'reply_count_cache') {
            $q .= "AND reply_count_cache > 0 ";
        }
        if ($order_by == 'retweet_count_cache') {
            $q .= "AND retweet_count_cache > 0 ";
        }
        if ($is_public) {
            $q .= 'AND p.is_protected = 0 ';
        }
        $q .= "ORDER BY $order_by $direction ";
        $vars = array(
            ':author_id'=> (string)$author_id,
            ':network'=> $network,
            ':from' => $from,
            ':until' => $until
        );
        //echo Utils::mergeSQLVars($q, $vars);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        if ($iterator) {
            return (new PostIterator($ps));
        }
        $all_rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        if ($all_rows) {
            $post_keys_array = array();
            foreach ($all_rows as $row) {
                $post_keys_array[] = $row['id'];
            }

            // Get links
            $q = "SELECT * FROM #prefix#links WHERE post_key in (".implode(',', $post_keys_array).")";
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $ps = $this->execute($q);
            $all_link_rows = $this->getDataRowsAsArrays($ps);

            // Combine posts and links
            foreach ($all_rows as $post_row) {
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
     * Get all posts by a given user with configurable order by field and direction
     * @param str $author_username
     * @param str $network Default "twitter"
     * @param int|bool $count False if no limit (ie, return all rows)
     * @param str $order_by field name Default "pub_date"
     * @return array Posts with link object set
     */
    public function getAllPostsByUsernameOrderedBy($author_username, $network="twitter", $count=0,
    $order_by="pub_date", $in_last_x_days = 0, $iterator = false, $is_public = false, $since = null) {
        $order_by = $this->sanitizeOrderBy($order_by);
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network
        );
        // if the 'order_by' string is 'retweets', add an add'l aggregate (sum of two fields) var to the select,
        // which we can then sort on.
        if ($order_by == 'retweets') {
            $q = "SELECT p.*, GREATEST((p.retweet_count_cache + p.old_retweet_count_cache), retweet_count_api) ".
            "as retweets, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        } else {
            $q = "SELECT p.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        }
        $q .= "FROM #prefix#posts p ";
        $q .= "WHERE author_username = :author_username AND p.network = :network ";

        if ($in_last_x_days > 0) {
            if ($since == null) {
                $q .= "AND pub_date >= DATE_SUB(CURDATE(), INTERVAL :in_last_x_days DAY) ";
            } else {
                $q .= "AND pub_date >= DATE_SUB(:since, INTERVAL :in_last_x_days DAY) ";
                $vars[':since'] = $since;
            }
            $vars[':in_last_x_days'] = (int)$in_last_x_days;
        }
        if ($order_by == 'reply_count_cache') {
            $q .= "AND reply_count_cache > 0 ";
        }
        if ($order_by == 'retweets') {
            $q .= "AND (p.retweet_count_cache + p.old_retweet_count_cache) > 0 ";
        }
        if ($is_public) {
            $q .= 'AND p.is_protected = 0 ';
        }
        $q .= "ORDER BY ".$order_by." DESC ";
        if ($count) {
            $q .= "LIMIT :limit";
            $vars[':limit'] = (int)$count;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        if ($iterator) {
            return (new PostIterator($ps));
        }
        $posts = array();
        $all_post_rows = $this->getDataRowsAsArrays($ps);
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

    public function getAllPostsByUsernameIterator($username, $network, $count = 0) {
        return $this->getAllPostsByUsernameOrderedBy($username, $network, $count, null, null, $iterator = true);
    }

    public function getAllPostsByUsername($username, $network) {
        return $this->getAllPostsByUsernameOrderedBy($username, $network, null, null, null, $iterator = false);
    }

    public function getMostRepliedToPostsInLastWeek($username, $network, $count, $is_public = false) {
        return $this->getAllPostsByUsernameOrderedBy($username, $network, $count, 'reply_count_cache', 7,
        $iterator = false, $is_public);
    }

    public function getMostFavedPostsInLastWeek($username, $network, $count, $is_public = false) {
        return $this->getAllPostsByUsernameOrderedBy($username, $network, $count, 'favlike_count_cache', 7,
        $iterator = false, $is_public);
    }

    public function getMostRetweetedPostsInLastWeek($username, $network, $count, $is_public = false) {
        return $this->getAllPostsByUsernameOrderedBy($username, $network, $count, 'retweets', 7,
        $iterator = false, $is_public);
    }

    public function getTotalPostsByUser($author_username, $network) {
        $q = "SELECT  COUNT(*) as total ";
        $q .= "FROM #prefix#posts p ";
        $q .= "WHERE author_username = :author_username AND network=:network ";
        $q .= "ORDER BY pub_date ASC";
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);
        return $result["total"];
    }

    public function getStatusSources($author_id, $network) {
        $q = "SELECT source, count(source) as total ";
        $q .= "FROM #prefix#posts WHERE ";
        $q .= "author_user_id = :author_id AND network=:network ";
        $q .= "GROUP BY source  ORDER BY total DESC;";
        $vars = array(
            ':author_id'=>(string)$author_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    public function getAllMentionsIterator($author_username, $count, $network = "twitter", $page=1, $public=false,
    $include_rts = true, $order_by = 'pub_date', $direction = 'DESC') {
        return $this->getMentions($author_username, $count, $network, $iterator = true, $page, $public, $include_rts,
        $order_by, $direction);
    }

    public function getAllMentions($author_username, $count, $network = "twitter", $page=1, $public=false,
    $include_rts = true, $order_by = 'pub_date', $direction = 'DESC') {
        return $this->getMentions($author_username, $count, $network, $iterator = false, $page, $public, $include_rts,
        $order_by, $direction);
    }

    private function getMentions($author_username, $count, $network, $iterator, $page=1, $public=false,
    $include_rts = true, $order_by = 'pub_date', $direction = 'DESC') {
        $start_on_record = ($page - 1) * $count;

        $order_by = $this->sanitizeOrderBy($order_by);

        $direction = ($direction == 'DESC') ? 'DESC' : 'ASC';

        $author_username = '@'.$author_username;
        $q = "SELECT p.*, u.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts AS p ";
        $q .= "INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id ";
        $q .= "WHERE p.network = :network AND ";
        //fulltext search only works for words longer than 4 chars
        if ( strlen($author_username) > PostMySQLDAO::FULLTEXT_CHAR_MINIMUM ) {
            $q .= "MATCH (`post_text`) AGAINST(:author_username IN BOOLEAN MODE) ";
        } else {
            $author_username = '%'.$author_username .'%';
            $q .= "post_text LIKE :author_username ";
        }
        if ($public) {
            $q .= "AND u.is_protected = 0 ";
        }
        if ($include_rts == false) {
            $q .= 'AND p.in_retweet_of_post_id IS NULL ';
        }
        $q .= "ORDER BY $order_by $direction ";
        $q .= "LIMIT :start_on_record, :limit;";
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network,
            ':start_on_record'=>(int)$start_on_record,
            ':limit'=>(int)$count
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

    public function getAllMentionsInRange($author_username, $count, $network = "twitter", $from, $until, $page=1,
    $public=false, $include_rts = true, $order_by = 'pub_date', $direction = 'DESC') {
        return $this->getMentionsInRange($author_username, $count, $network, $from, $until, $iterator = false, $page,
        $public, $include_rts, $order_by, $direction);
    }

    private function getMentionsInRange($author_username, $count, $network, $from, $until, $iterator, $page=1,
    $public=false, $include_rts = true, $order_by = 'pub_date', $direction = 'DESC') {
        $start_on_record = ($page - 1) * $count;

        $order_by = $this->sanitizeOrderBy($order_by);

        $direction = ($direction == 'DESC') ? 'DESC' : 'ASC';

        $author_username = '@'.$author_username;
        $q = " SELECT p.*, u.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts AS p ";
        $q .= "INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id ";
        $q .= "WHERE p.network = :network AND pub_date BETWEEN :from AND :until ";

        if ( strlen($author_username) > PostMySQLDAO::FULLTEXT_CHAR_MINIMUM ) {
            $q .= "AND MATCH (`post_text`) AGAINST(:author_username IN BOOLEAN MODE) ";
        } else {
            $author_username = '%'.$author_username .'%';
            $q .= "AND post_text LIKE :author_username ";
        }
        if ($public) {
            $q .= "AND u.is_protected = 0 ";
        }

        if ($include_rts == false) {
            $q .= 'AND p.in_retweet_of_post_id IS NULL ';
        }
        $q .= "ORDER BY $order_by $direction ";
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network,
            ':from' => $from,
            ':until' => $until
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

    public function getAllReplies($user_id, $network, $count, $page = 1, $order_by = 'pub_date', $direction = 'DESC',
    $is_public = false) {
        $start_on_record = ($page - 1) * $count;

        $order_by = $this->sanitizeOrderBy($order_by);

        $direction = $direction == 'DESC' ? 'DESC' : 'ASC';

        $q = "SELECT p.*, u.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "INNER JOIN #prefix#users u ON p.author_user_id = u.user_id ";
        $q .= "WHERE in_reply_to_user_id = :user_id AND p.network=:network ";
        if ($is_public) {
            $q .= 'AND p.is_protected = 0 ';
        }
        $q .= "ORDER BY $order_by $direction LIMIT :start_on_record, :limit;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':start_on_record'=>(int)$start_on_record,
            ':limit'=>(int)$count
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
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

    public function getAllRepliesInRange($user_id, $network, $count, $from, $until,$page = 1, $order_by = 'pub_date',
    $direction = 'DESC', $is_public = false) {
        $start_on_record = ($page - 1) * $count;

        $order_by = $this->sanitizeOrderBy($order_by);

        $direction = $direction == 'DESC' ? 'DESC' : 'ASC';

        $q = "SELECT p.*, u.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "INNER JOIN #prefix#users u ON p.author_user_id = u.user_id ";
        $q .= "WHERE in_reply_to_user_id = :user_id AND p.network=:network AND pub_date BETWEEN :from AND :until ";
        if ($is_public) {
            $q .= 'AND p.is_protected = 0 ';
        }
        $q .= "ORDER BY " . $order_by. ' ' . $direction . ' ';
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':from' => $from,
            ':until' => $until
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
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

    public function getMostRepliedToPosts($user_id, $network, $count, $page=1, $is_public = false) {
        return $this->getAllPostsByUserID($user_id, $network, $count, "reply_count_cache", "DESC", true, $page,
        $iterator = false, $is_public);
    }

    public function getMostFavedPosts($user_id, $network, $count, $page=1, $is_public = false) {
        return $this->getAllPostsByUserID($user_id, $network, $count, "favlike_count_cache", "DESC", true, $page,
        $iterator = false, $is_public);
    }

    public function getMostRetweetedPosts($user_id, $network, $count, $page=1, $is_public = false) {
        return $this->getAllPostsByUserID($user_id, $network, $count, "retweets", "DESC", true, $page,
        $iterator = false, $is_public);
    }

    public function getOrphanReplies($username, $count, $network = "twitter", $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $username = "@".$username;
        $q = "SELECT p.* , u.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "INNER JOIN #prefix#users u ON u.user_id = p.author_user_id WHERE ";
        //fulltext search only works for words longer than 4 chars
        if ( strlen($username) > PostMySQLDAO::FULLTEXT_CHAR_MINIMUM ) {
            $q .= "MATCH (`post_text`) AGAINST(:username IN BOOLEAN MODE) ";
        } else {
            $username = '%'.$username .'%';
            $q .= "post_text LIKE :username ";
        }
        $q .= "AND in_reply_to_post_id is null ";
        $q .= "AND in_retweet_of_post_id is null ";
        $q .= "AND p.network = :network ";
        $q .= "ORDER BY pub_date DESC LIMIT :start_on_record, :limit;";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network,
            ':limit'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $all_posts = array();
        foreach ($all_rows as $row) {
            $all_posts[] = $this->setPostWithAuthor($row);
        }
        return $all_posts;
    }

    /**
     * Decrement a post's reply_count_cache
     * @param int $post_id
     * @param str $network
     * @return in count of affected rows
     */
    private function decrementReplyCountCache($post_id, $network) {
        $q = "UPDATE #prefix#posts SET reply_count_cache = reply_count_cache - 1 ";
        $q .= "WHERE post_id = :post_id AND network=:network ";
        $vars = array(
            ':post_id'=>(string)$post_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function getStrayRepliedToPosts($author_id, $network) {
        $q = "SELECT in_reply_to_post_id FROM #prefix#posts p ";
        $q .= "WHERE p.author_user_id=:author_id AND p.network=:network ";
        $q .= "AND p.in_reply_to_post_id NOT IN (select post_id from #prefix#posts) ";
        $q .= "AND p.in_reply_to_post_id NOT IN (select post_id from #prefix#post_errors);";
        $vars = array(
            ':author_id'=>(string)$author_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    public function getPostsToGeoencode($limit = 5000) {
        $q = "SELECT q.post_id, q.location, q.geo, q.place, q.in_reply_to_post_id, q.in_retweet_of_post_id, ";
        $q.= "q.is_reply_by_friend, q.is_retweet_by_friend, q.network FROM ";
        $q .= "(SELECT * FROM #prefix#posts AS p WHERE ";
        $q .= "(p.geo IS NOT null OR p.place IS NOT null OR p.location IS NOT null) ";
        $q .= "AND (p.is_geo_encoded='0' OR p.is_geo_encoded='3') ";
        $q .= "ORDER BY id DESC LIMIT :limit) AS q ORDER BY q.id ";
        $vars = array(
            ':limit'=>(int)$limit
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        return $all_rows;
    }

    public function setGeoencodedPost($post_id, $network, $is_geo_encoded = 0, $location = null, $geodata = null,
    $distance = 0) {
        if ($location && $geodata && ($is_geo_encoded>=1 && $is_geo_encoded<=5)) {
            $q = "UPDATE #prefix#posts p SET p.location = :location, p.geo = :geo, ";
            $q .= "p.reply_retweet_distance = :distance, p.is_geo_encoded = :is_geo_encoded ";
            $q .= "WHERE p.post_id = :post_id AND p.network=:network";
            $vars = array(
                ':location'=>$location,
                ':geo'=>$geodata,
                ':distance'=>$distance,
                ':is_geo_encoded'=>$is_geo_encoded,
                ':post_id'=>(string)$post_id,
                ':network'=>$network
            );
        } else {
            $q = "UPDATE #prefix#posts p SET p.is_geo_encoded = :is_geo_encoded ";
            $q .= "WHERE p.post_id = :post_id AND p.network=:network";
            $vars = array(
                ':is_geo_encoded'=>$is_geo_encoded,
                ':post_id'=>(string)$post_id,
                ':network'=>$network
            );
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        if ($this->getUpdateCount($ps) > 0) {
            $logstatus = "Geolocation for $network post $post_id IS_GEO_ENCODED: $is_geo_encoded";
            $this->logger->logInfo($logstatus, __METHOD__.','.__LINE__);
            return true;
        } else {
            $logstatus = "Geolocation for $network post_id=$post_id IS_GEO_ENCODED: $is_geo_encoded not saved";
            $this->logger->logInfo($logstatus, __METHOD__.','.__LINE__);
            return false;
        }
    }

    public function updateAuthorUsername($author_user_id, $network, $author_username) {
        $q = "UPDATE  #prefix#posts SET author_username = :author_username ";
        $q .= "WHERE author_user_id = :author_user_id AND network=:network";
        $vars = array(
            ':author_user_id'=>(string)$author_user_id,
            ':author_username'=>$author_username,
            ':network' => $network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }


    public function deletePost($id) {
        $q = "DELETE from #prefix#posts WHERE id = :id LIMIT 1";
        $vars = array(
            ':id'=>$id,
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    /**
     * Extract location specific to city for each post
     * @param int $location Location as stored in the database
     * @return str short_location
     */
    private function processLocationRows($full_location) {
        $location = explode (', ', $full_location);
        $length = count($location);
        if ($length > 3) {
            return $location[$length-3].', '.$location[$length-2].', '.$location[$length-1];
        } else {
            return $full_location;
        }
    }

    /**
     * Convert Distance in kilometers to miles
     * @param int $distance_in_km Distance in KM
     * @return int $distance_in_miles
     */
    private function calculateDistanceInMiles($distance_in_km) {
        $distance_in_miles = round($distance_in_km/1.609);
        return $distance_in_miles;
    }

    /**
     * Calculate how much each client is used by a user on a specific network
     * @param int $author_id
     * @param string $network
     * @return array First element of the returned array is an array of all the clients the user used, ever.
     *               The second element is an array of the clients used for the last 25 posts.
     *               Both arrays are sorted by number of use, descending.
     */
    public function getClientsUsedByUserOnNetwork($author_id, $network) {
        $q  = "SELECT COUNT(*) AS num_posts, source ";
        $q .= "FROM #prefix#posts ";
        $q .= "WHERE author_user_id = :author_id AND network = :network ";
        $q .= "GROUP BY source";
        $vars = array(
            ':author_id'=>(string)$author_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $rows = $this->getDataRowsAsArrays($this->execute($q, $vars));
        $all_time_clients_usage = self::cleanClientsNames($rows);

        $q  = "SELECT COUNT(*) AS num_posts, source";
        $q .= "  FROM (";
        $q .= "       SELECT *";
        $q .= "         FROM #prefix#posts ";
        $q .= "        WHERE author_user_id = :author_id AND network = :network";
        $q .= "        ORDER BY pub_date DESC";
        $q .= "        LIMIT 25) p ";
        $q .= "GROUP BY source";
        $vars = array(
            ':author_id'=>(string)$author_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $rows = $this->getDataRowsAsArrays($this->execute($q, $vars));
        $latest_clients_usage = self::cleanClientsNames($rows);

        if (count($latest_clients_usage) == 1 && isset($latest_clients_usage[''])) {
            // Plugin doesn't support 'source'
            $latest_clients_usage = array();
        }

        return array($all_time_clients_usage, $latest_clients_usage);
    }

    /**
     * Clean up and sort (by number of use, descending) the source (client) information fetched in
     * getClientsUsedByUserOnNetwork. To clean up the clients names, we remove the HTML link tag.
     * @param array $rows obtained from the database (as array); columns should be 'num_posts' and 'source'
     * @return array Clients names as keys, number of uses as values.
     */
    protected static function cleanClientsNames($rows) {
        $clients = array();
        foreach ($rows as $row) {
            $client_name = preg_replace('@<a href.*>(.+)</a>@i', '\1', $row['source']);
            $clients_key = strtolower($client_name); // will merge together strings with different CaSeS
            if (!isset($clients[$clients_key])) {
                $clients[$clients_key] = array('name'=>$client_name, 'count'=>0);
            }
            $clients[$clients_key]['count'] += $row['num_posts'];
        }
        foreach ($clients as $key => $client) {
            unset($clients[$key]);
            $clients[$client['name']] = $client['count'];
        }
        arsort($clients);
        return $clients;
    }

    public function updateFavLikeCount($post_id, $network, $fav_like_count) {
        $q = "UPDATE #prefix#posts SET favlike_count_cache=:favlike_count_cache WHERE post_id=:post_id ";
        $q .= "AND network=:network;";
        $vars = array(
            ':favlike_count_cache'=>$fav_like_count,
            ':post_id'=>$post_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function updateReplyCount($post_id, $network, $reply_count) {
        $q = "UPDATE #prefix#posts SET reply_count_cache=:reply_count_cache WHERE post_id=:post_id ";
        $q .= "AND network=:network;";
        $vars = array(
            ':reply_count_cache'=>$reply_count,
            ':post_id'=>(string)$post_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function updateRetweetCount($post_id, $network, $retweet_count) {
        $q = "UPDATE #prefix#posts SET retweet_count_cache=:retweet_count_cache WHERE post_id=:post_id ";
        $q .= "AND network=:network;";
        $vars = array(
            ':retweet_count_cache'=>$retweet_count,
            ':post_id'=>(string)$post_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function updatePostText($post_id, $network, $post_text) {
        $q = "UPDATE #prefix#posts SET post_text=:post_text WHERE post_id=:post_id ";
        $q .= "AND network=:network;";
        $vars = array(
            ':post_text'=>$post_text,
            ':post_id'=>(string)$post_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function getOnThisDayFlashbackPosts($author_id, $network, $from_date=null) {
        $vars = array(
            ':author'=> strval($author_id),
            ':network'=>$network
        );
        if (!isset($from_date)) {
            $from_date = 'CURRENT_DATE()';
        } else {
            $vars[':from_date'] = $from_date;
            $from_date = ':from_date';
        }
        $q = "SELECT po.*, po.id AS post_key, po.pub_date + interval #gmt_offset# hour as adj_pub_date, ";
        $q .= "pl.place_type, pl.name, pl.full_name, pl.country_code, pl.country, pl.longlat, pl.bounding_box, ";
        $q .= "pl.icon, pl.map_image FROM #prefix#posts po ";
        $q .= "LEFT JOIN #prefix#places pl ON po.place_id = pl.place_id ";
        $q .= "WHERE  (YEAR(pub_date)!=YEAR(CURRENT_DATE())) ";
        $q .= "AND (DAYOFMONTH(pub_date)=DAYOFMONTH($from_date)) AND (MONTH(pub_date)=MONTH($from_date)) AND ";
        $q .= "author_user_id=:author AND po.network=:network AND ";
        $q .= "in_reply_to_post_id IS null AND in_reply_to_user_id IS NULL AND ";
        $q .= "in_retweet_of_post_id IS NULL AND in_rt_of_user_id IS NULL AND ";
        // Don't return posts without post text or place to display
        $q .= "(post_text != '' || po.place != '') ";
        $q .= "ORDER BY pub_date DESC ";

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_post_rows = $this->getDataRowsAsArrays($ps);

        $post_keys_array = array();
        foreach ($all_post_rows as $row) {
            $post_keys_array[] = $row['post_key'];
        }

        $all_posts = array();
        foreach ($all_post_rows as $row) {
            $post = new Post($row);
            // Create a place object incase this post has some associated place data
            $post->place_obj = new Place($row);
            $q = "SELECT * FROM #prefix#links WHERE post_key in (".implode(",", $post_keys_array).")";
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $ps = $this->execute($q);
            $all_link_rows = $this->getDataRowsAsArrays($ps);
            foreach ($all_link_rows as $link_row) {
                if ($link_row['post_key'] == $post->id) {
                    $post->addLink(new Link($link_row));
                }
            }
            $all_posts[] = $post;
        }
        return array_reverse($all_posts);
    }

    public function getAllCheckins($author_id, $network, $count=15, $page=1) {
        // Work out which is the first checkin we want to get
        $start_on_record = ($page - 1) * $count;

        // Get the checkins from the database for this user
        $q = "SELECT po.id AS post_key, po.post_id, po.author_user_id, po.author_username, po.author_fullname, ";
        $q .= "po.author_avatar, po.author_follower_count, po.post_text, po.is_protected, po.source, po.location, ";
        $q .= "po.place, po.place_id, po.geo, pub_date, po.in_reply_to_user_id,";
        $q .= "po.in_reply_to_post_id, ";
        $q .= "po.reply_count_cache, po.is_reply_by_friend, po.in_retweet_of_post_id, po.old_retweet_count_cache, ";
        $q .= "po.is_retweet_by_friend, po.reply_retweet_distance, po.network, po.is_geo_encoded, ";
        $q .= "po.in_rt_of_user_id, po.retweet_count_cache, po.retweet_count_api, po.favlike_count_cache, ";
        $q .= "po.pub_date + interval #gmt_offset# hour as adj_pub_date, pl.place_id, pl.place_type, pl.name, ";
        $q .= "pl.full_name, pl.country_code, pl.country, pl.network, pl.longlat, pl.bounding_box, pl.icon, ";
        $q .= "pl.map_image, pl.id  ";
        $q .= "FROM #prefix#posts po ";
        $q .= "JOIN #prefix#places pl ON po.place_id = pl.place_id ";
        $q .= "WHERE author_user_id=:author AND po.network=:network AND po.in_reply_to_post_id IS null ";
        $q .= "ORDER BY pub_date DESC ";
        $vars = array(
            ':author'=>$author_id,
            ':network'=>$network
        );
        if (isset($count) && $count > 0) {
            $q .= "LIMIT :start_on_record, :limit";
            $vars[':limit'] = (int)$count;
            $vars[':start_on_record'] = (int)$start_on_record;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }

        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);

        // Get all the post ids of the checkins (we use this later for our link query)
        $post_keys_array = array();
        foreach ($all_rows as $row) {
            $post_keys_array[] = $row['post_key'];
        }

        // An array to store each post object in for the checkins
        $all_posts = array();
        foreach ($all_rows as $row) {
            $data = new Post($row);
            // Create a place object for any place related data
            $data->place_obj = new Place($row);
            // Query for all the links related to these posts / checkins
            $q2 = "SELECT * FROM #prefix#links WHERE post_key in (".implode(",", $post_keys_array).")";
            $ps2 = $this->execute($q2);
            $all_link_rows = $this->getDataRowsAsArrays($ps2);

            // For each link returned if it equals the post id of this post add the link to this post
            foreach ($all_link_rows as $link_row) {
                if ($link_row['post_key'] == $data->id) {
                    $data->addLink(new Link($link_row));
                }
            }
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            // Now we have all the information for this post store it in our array of all posts
            $all_posts[] = $data;
        }
        return $all_posts;
    }

    public function countCheckinsToPlaceTypes($author_id, $network) {
        $q = "SELECT place_type, COUNT(place_type) AS place_count FROM #prefix#places WHERE place_id IN ";
        $q .= "(SELECT place_id FROM #prefix#posts WHERE author_user_id=:author AND network=:network) ";
        $q .= "GROUP BY place_type";
        $vars = array(
            'author'=>$author_id,
            'network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }

        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }

        // Convert the results into Google Charts Format
        foreach ($all_rows as $row) {
            /* Data needs to be in format
             {c:[{v: 'Title'}, {v: Value of data for this title}]}
             e.g. [{c:[{v: 'Park'}, {v: 5}]}
             */
            $resultset[] = array('c' => array(
            array('v' => $row['place_type'], 'f' => $row['place_type']),
            array('v' => intval($row['place_count']))
            ));
        }

        // Set the meta values like titles etc.
        $metadata = array(
        array('type' => 'string', 'label' => 'Place Type'),
        array('type' => 'number', 'label' => 'Number of Checkins to this place type'),
        );

        // Now encode this data as JSON for the Google Charts API
        $visdata = json_encode(array('rows' => $resultset, 'cols' => $metadata));

        if (count($all_rows) > 0) {
            return $visdata;
        } else {
            return "";
        }
    }

    public function countCheckinsToPlaceTypesLastWeek($author_id, $network) {
        $q = "SELECT place_type, COUNT(place_type) AS place_count FROM #prefix#places WHERE place_id IN ";
        $q .= "(SELECT place_id FROM #prefix#posts WHERE author_user_id=:author_id AND network=:network_name ";
        $q .= "AND YEARWEEK(pub_date) = YEARWEEK(CURRENT_DATE) AND in_reply_to_post_id IS null ) ";
        $q .= "GROUP BY place_type";
        $vars = array(
                        ':author_id'=>$author_id,
                        ':network_name'=>$network);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }

        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }

        // Convert the results into Google Charts Format
        foreach ($all_rows as $row) {
            /* Data needs to be in format
             {c:[{v: 'Title'}, {v: Value of data for this title}]}
             e.g. [{c:[{v: 'Park'}, {v: 5}]}
             */
            $resultset[] = array('c' => array(
            array('v' => $row['place_type'], 'f' => $row['place_type']),
            array('v' => intval($row['place_count']))
            ));
        }

        // Set the meta values like titles etc.
        $metadata = array(
        array('type' => 'string', 'label' => 'Place Type'),
        array('type' => 'number', 'label' => 'Number of Checkins to this place type'),
        );

        // Now encode this data as JSON for the Google Charts API
        $visdata = json_encode(array('rows' => $resultset, 'cols' => $metadata));

        /* As we are getting posts from the last week, we might not have any so return a blank string in that instance
         * So we know not to draw this graph on the dashboard.
         */

        if (count($all_rows) > 0) {
            return $visdata;
        } else {
            return "";
        }
    }

    public function getPostsPerHourDataVis($author_id, $network) {
        $posts_per_hour_all_time = $this->getCheckinsPerHourAllTime($author_id, $network);
        $posts_per_hour_last_week = $this->getCheckinsPerHourLastWeek($author_id, $network);

        // Convert the results into Google Charts Format
        $i = 0;
        while ($i < 24) { // Hour 0 through 23
            /* Data needs to be in format
            {c:[{v: 'Hour'}, {v: Number of checkins at this hour}]}
            e.g. [{c:[{v: '11'}, {v: 5}]}
            */
            foreach ($posts_per_hour_last_week as $row) {
                if ($row['hour'] == $i) {
                    $last_week_value = intval($row['total']);
                    break;
                }
                $last_week_value = 0;
            }

            foreach ($posts_per_hour_all_time as $row) {
                if ($row['hour'] == $i) {
                    $all_time_value = intval($row['total']);
                    break;
                }
                $all_time_value = 0;
            }

            $post_data[] = array('c' => array(
            array('v' => $i),
            array('v' => $last_week_value),
            array('v' => $all_time_value),
            ));
            $i++;
        }

        // Set the meta values like titles etc.
        $metadata = array(
        array('type' => 'string', 'label' => 'Hour of Day'),
        array('type' => 'number', 'label' => 'Checkins Last Week'),
        array('type' => 'number', 'label' => 'Checkins All Time'),
        );

        // Now encode this data as JSON for the Google Charts API
        return json_encode(array('rows' => $post_data, 'cols' => $metadata));
    }

    private function getCheckinsPerHourAllTime($author_id, $network) {
        $q = "SELECT HOUR(pub_date) AS hour, COUNT(HOUR(pub_date)) AS total FROM #prefix#posts ";
        $q .= "WHERE author_user_id=:author ";
        $q .= "AND network=:network AND in_reply_to_post_id IS null GROUP BY HOUR(pub_date)";
        $vars = array(
            'author'=>$author_id,
            'network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    private function getCheckinsPerHourLastWeek($author_id, $network) {
        $q = "SELECT CAST(HOUR(pub_date) AS UNSIGNED) AS hour, COUNT(HOUR(pub_date)) AS total FROM #prefix#posts ";
        $q .= "WHERE author_user_id=:author ";
        $q .= "AND network=:network AND YEARWEEK(pub_date) = YEARWEEK(CURRENT_DATE) AND in_reply_to_post_id IS null ";
        $q .= "GROUP BY HOUR(pub_date)";
        $vars = array(
            'author'=>$author_id,
            'network'=>$network);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    public function getAllCheckinsInLastWeekAsGoogleMap($author_id, $network) {
        $q = "SELECT geo FROM #prefix#posts WHERE author_user_id=:author_id AND network=:network_name ";
        $q .= "AND YEARWEEK(pub_date) = YEARWEEK(CURRENT_DATE) AND in_reply_to_post_id IS null ";
        $vars = array(
            ':author_id'=>$author_id,
            ':network_name'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);

        $url = 'http://maps.googleapis.com/maps/api/staticmap?size=708x500&maptype=roadmap&markers=';
        $url .= 'color:blue%7C';

        foreach($all_rows as $row) {
            $url .= "|".$row['geo'];
        }

        $url .= '&sensor=false';

        if (count($all_rows) > 0) {
            return $url;
        } else {
            return "";
        }
    }

    public function getMostPopularPostsOfTheYear($author_user_id, $network, $year, $count=25) {
        $vars = array(
            ':network_user_id'=> $author_user_id,
            ':network'=>$network,
            ':year'=>$year,
            ':count'=>$count
        );
        $q = "SELECT po.*, po.id AS post_key, po.pub_date + interval #gmt_offset# hour as adj_pub_date, ";
        $q .= "(greatest(retweet_count_cache, retweet_count_api) + reply_count_cache + favlike_count_cache) ";
        $q .= "AS total_responses FROM #prefix#posts po WHERE  YEAR(pub_date)=:year ";
        $q .= "AND po.author_user_id=:network_user_id AND po.network=:network AND ";
        $q .= "in_reply_to_post_id IS null AND in_reply_to_user_id IS NULL AND ";
        $q .= "in_retweet_of_post_id IS NULL AND in_rt_of_user_id IS NULL ";
        $q .= "ORDER BY total_responses DESC LIMIT :count";

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $post_rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        foreach ($post_rows as $row) {
            $post = new Post($row);
            $posts[] = $post;
        }
        return $posts;
    }
    public function getAverageRetweetCount($author_username, $network, $last_x_days, $since=null){
        if ($since==null) {
            $since = date('Y-m-d');
        }

        $q = "SELECT round(avg(old_retweet_count_cache + retweet_count_cache)) as average_retweet_count ";
        $q .= "FROM #prefix#posts WHERE network=:network and author_username=:author_username ";
        $q .= "AND in_reply_to_user_id IS null AND in_reply_to_post_id IS null AND in_retweet_of_post_id is null ";
        $q .= "AND (retweet_count_cache > 0 OR old_retweet_count_cache > 0) ";
        $q .= "AND pub_date >= DATE_SUB(:since, INTERVAL :last_x_days DAY);";
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network,
            ':last_x_days'=>(int)$last_x_days,
            ':since'=>$since
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);
        if (!isset($result["average_retweet_count"])) {
            $q = "SELECT round(avg(retweet_count_api)) as average_retweet_count ";
            $q .= "FROM #prefix#posts WHERE network=:network and author_username=:author_username ";
            $q .= "AND in_reply_to_user_id IS null AND in_reply_to_post_id IS null AND in_retweet_of_post_id is null ";
            $q .= "AND retweet_count_api > 0 ";
            $q .= "AND pub_date >= DATE_SUB(:since, INTERVAL :last_x_days DAY);";
            $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network,
            ':last_x_days'=>(int)$last_x_days,
            ':since'=>$since
            );
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $ps = $this->execute($q, $vars);
            $result = $this->getDataRowAsArray($ps);
        }
        return $result["average_retweet_count"];
    }

    public function getAverageFaveCount($author_username, $network, $last_x_days, $since=null){
        if ($since==null) {
            $since = date('Y-m-d');
        }

        $q = "SELECT round(avg(favlike_count_cache)) as average_favlike_count ";
        $q .= "FROM #prefix#posts WHERE network=:network and author_username=:author_username ";
        $q .= "AND in_reply_to_user_id IS null AND in_reply_to_post_id IS null AND in_retweet_of_post_id is null ";
        $q .= "AND favlike_count_cache > 0 ";
        $q .= "AND pub_date >= DATE_SUB(:since, INTERVAL :last_x_days DAY);";
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network,
            ':last_x_days'=>(int)$last_x_days,
            ':since'=>$since
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);
        return $result["average_favlike_count"];
    }

    public function getAverageReplyCount($author_username, $network, $last_x_days, $since=null){
        if ($since==null) {
            $since = date('Y-m-d');
        }

        $q = "SELECT round(avg(reply_count_cache)) as average_reply_count ";
        $q .= "FROM #prefix#posts WHERE network=:network and author_username=:author_username ";
        $q .= "AND in_reply_to_user_id IS null AND in_reply_to_post_id IS null AND in_retweet_of_post_id is null ";
        $q .= "AND reply_count_cache > 0 ";
        $q .= "AND pub_date >= DATE_SUB(:since, INTERVAL :last_x_days DAY);";
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network,
            ':last_x_days'=>(int)$last_x_days,
            ':since'=>$since
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);
        return $result["average_reply_count"];
    }

    public function doesUserHavePostsWithRetweetsSinceDate($author_username, $network, $last_x_days, $since=null) {
        if ($since==null) {
            $since = date('Y-m-d');
        }

        $q = "SELECT id FROM #prefix#posts WHERE network=:network and author_username=:author_username ";
        $q .= "AND in_reply_to_user_id IS null AND in_reply_to_post_id IS null AND in_retweet_of_post_id is null ";
        $q .= "AND (retweet_count_cache > 0 OR old_retweet_count_cache > 0 OR retweet_count_api > 0) ";
        $q .= "AND pub_date >= DATE_SUB(:since, INTERVAL :last_x_days DAY) LIMIT 1;";
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network,
            ':last_x_days'=>(int)$last_x_days,
            ':since'=>$since
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowsAsArrays($ps);
        if (sizeof($result) < 1 ) {
            return false;
        } else {
            return true;
        }
    }

    public function doesUserHavePostsWithFavesSinceDate($author_username, $network, $last_x_days, $since=null) {
        if ($since==null) {
            $since = date('Y-m-d');
        }

        $q = "SELECT id FROM #prefix#posts WHERE network=:network and author_username=:author_username ";
        $q .= "AND in_reply_to_user_id IS null AND in_reply_to_post_id IS null AND in_retweet_of_post_id is null ";
        $q .= "AND (favlike_count_cache > 0) ";
        $q .= "AND pub_date >= DATE_SUB(:since, INTERVAL :last_x_days DAY) LIMIT 1;";
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network,
            ':last_x_days'=>(int)$last_x_days,
            ':since'=>$since
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowsAsArrays($ps);
        if (sizeof($result) < 1 ) {
            return false;
        } else {
            return true;
        }
    }

    public function doesUserHavePostsWithRepliesSinceDate($author_username, $network, $last_x_days, $since=null) {
        if ($since==null) {
            $since = date('Y-m-d');
        }

        $q = "SELECT id FROM #prefix#posts WHERE network=:network and author_username=:author_username ";
        $q .= "AND in_reply_to_user_id IS null AND in_reply_to_post_id IS null AND in_retweet_of_post_id is null ";
        $q .= "AND (reply_count_cache > 0) ";
        $q .= "AND pub_date >= DATE_SUB(:since, INTERVAL :last_x_days DAY) LIMIT 1;";
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network,
            ':last_x_days'=>(int)$last_x_days,
            ':since'=>$since
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowsAsArrays($ps);
        if (sizeof($result) < 1 ) {
            return false;
        } else {
            return true;
        }
    }

    public function getRetweetsByAuthorsOverFollowerCount($post_id, $network, $follower_count_threshold) {
        $q = "SELECT u.* ";
        $q .= "FROM #prefix#posts p ";
        $q .= "INNER JOIN #prefix#users u on p.author_user_id = u.user_id ";
        $q .= "WHERE p.network=:network AND in_retweet_of_post_id=:post_id ";
        $q .= "AND p.is_protected = 0 AND u.follower_count > :follower_count_threshold ";
        $q .= "ORDER BY u.follower_count DESC, p.id DESC LIMIT 5";

        $vars = array(
            ':post_id'=>(string)$post_id,
            ':network'=>$network,
            ':follower_count_threshold'=>$follower_count_threshold
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsObjects($ps, 'User');
    }

    public function getDaysAgoSinceUserRepliedToRecipient($user_id, $recipient_id, $network) {
        $q = "SELECT TIMESTAMPDIFF(DAY, pub_date, NOW()) AS last_reply_days_ago ";
        $q .= "FROM #prefix#posts AS p ";
        $q .= "WHERE p.author_user_id=:user_id AND p.network=:network AND p.in_reply_to_user_id=:recipient_id ";
        $q .= "ORDER BY p.pub_date DESC LIMIT 1";

        $vars = array(
            ':user_id' => (string)$user_id,
            ':recipient_id' => (string)$recipient_id,
            ':network' => $network
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowsAsArrays($ps);

        return count($result) ? (int)$result[0]['last_reply_days_ago'] : null;
    }

    public function countAllPostsByUserSinceDaysAgo($author_id, $network, $days_ago=7) {
        $q = "SELECT COUNT(*) AS count FROM #prefix#posts AS p ";
        $q .= "WHERE p.author_user_id=:user_id AND p.network=:network ";
        $q .= "AND p.pub_date>=DATE_SUB(CURDATE(), INTERVAL :days_ago DAY) ";
        $vars = array(
            ':user_id'=>$author_id,
            ':network'=>$network,
            ':days_ago'=>$days_ago
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);

        return (int)$result['count'];
    }

    public function searchPostsByUser(array $keywords, $network, $author_username, $page_number=1, $page_count=20) {
        if (!is_array($keywords)) {
            return null;
        }
        $start_on_record = ($page_number - 1) * $page_count;

        $q = "SELECT p.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p WHERE  author_username=:author_username AND network = :network ";
        $q .= "AND (";
        $counter = 0;
        $search_terms = array();
        $unique_keywords = array_unique($keywords);
        foreach ($unique_keywords as $keyword) {
            $term = $keyword;
            for ($i = 1; $i < count(array_keys($keywords,$keyword)); $i++) {
                $term .= '%'.$keyword;
            }
            $search_terms[] = $term;
            $q .= " post_text LIKE :keyword".$counter." ";
            if ($keyword != end($unique_keywords)) {
                $q .= "AND";
            }
            $counter++;
        }
        $q .= ") ORDER BY pub_date DESC ";
        if ($page_count > 0) {
            $q .= "LIMIT :start_on_record, :limit;";
        } else {
            $q .= ';';
        }

        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network
        );
        $counter = 0;
        foreach ($search_terms as $term) {
            $vars[':keyword'.$counter] = '%'.$term.'%';
            $counter++;
        }
        if ($page_count > 0) {
            $vars[':limit'] = (int)$page_count;
            $vars[':start_on_record'] = (int)$start_on_record;
        }

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $post_rows = $this->getDataRowsAsArrays($ps);

        $posts = array();
        foreach ($post_rows as $row) {
            $post = new Post($row);
            $posts[] = $post;
        }
        return $posts;
    }

    /**
     * Get all posts by a given hashtag with configurable order by field and direction
     * @param int $hashtag_id
     * @param str $network
     * @param int $count
     * @param str $order_by field name
     * @param str $direction either "DESC" or "ASC
     * @param int $page Page number, defaults to 1
     * @param bool $is_public Whether or not these results are going to be displayed publicly. Defaults to false.
     * @return array Posts with link object set
     */
    public function getAllPostsByHashtagId($hashtag_id, $network, $count, $order_by="pub_date", $direction="DESC",
    $page=1, $is_public = false) {

        $direction = $direction=="DESC" ? "DESC": "ASC";
        $start_on_record = ($page - 1) * $count;
        $order_by = $this->sanitizeOrderBy($order_by);

        $q = "SELECT p.*, p.pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p, #prefix#hashtags_posts hp, #prefix#hashtags h ";
        $q .= "WHERE  p.post_id= hp.post_id AND hp.hashtag_id = h.id AND h.id = :hashtag_id AND p.network=:network ";

        if ($is_public) {
            $q .= 'AND p.is_protected = 0 ';
        }

        $q .= "ORDER BY p.$order_by $direction ";

        $vars = array(
                ':hashtag_id'=>$hashtag_id,
                ':network'=>$network,
        );

        if (isset($count) && $count > 0) {
            $q .= "LIMIT :start_on_record, :limit";
            $vars[':limit'] = (int)$count;
            $vars[':start_on_record'] = (int)$start_on_record;
        }

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

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

    public function deletePostsByHashtagId($hashtag_id) {
        $q  = "DELETE t.* FROM #prefix#posts t ";
        $q .= "INNER JOIN #prefix#hashtags_posts hp ON t.post_id = hp.post_id ";
        $q .= "WHERE hp.hashtag_id=:hashtag_id;";
        $vars = array(':hashtag_id'=>$hashtag_id);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDeleteCount($ps);
    }

    public function searchPostsByHashtag($keywords, Hashtag $hashtag, $network, $page_number=1, $page_count=20) {
        $start_on_record = ($page_number - 1) * $page_count;
        $q = "SELECT p.*, p.pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p, #prefix#hashtags_posts hp, #prefix#hashtags h ";
        $q .= "WHERE  p.post_id= hp.post_id AND hp.hashtag_id = h.id AND p.network=:network AND h.id = :hashtag_id ";
        if (sizeof($keywords) > 0) {
            $q .= "AND (";
            $counter = 0;
            foreach ($keywords as $keyword) {
                $q .= " post_text LIKE :keyword".$counter." ";
                if ($keyword != end($keywords)) {
                    $q .= "AND";
                }
                $counter++;
            }
            $q .= ") ";
        }
        $q .= "ORDER BY pub_date DESC ";
        if ($page_count > 0) {
            $q .= "LIMIT :start_on_record, :limit;";
        } else {
            $q .= ';';
        }
        $vars = array(
            ':network'=>$network,
            ':hashtag_id'=>$hashtag->id
        );
        if (sizeof($keywords) > 0) {
            $counter = 0;
            foreach ($keywords as $keyword) {
                $vars[':keyword'.$counter] = '%'.$keyword.'%';
                $counter++;
            }
        }
        if ($page_count > 0) {
            $vars[':limit'] = (int)$page_count;
            $vars[':start_on_record'] = (int)$start_on_record;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $post_rows = $this->getDataRowsAsArrays($ps);

        $posts = array();
        foreach ($post_rows as $row) {
            $post = new Post($row);
            $posts[] = $post;
        }
        return $posts;
    }

    public function getMostFavCommentPostsByUserId($author_id, $network) {
        $q  = "SELECT *, pub_date + interval #gmt_offset# hour AS adj_pub_date FROM #prefix#posts ";
        $q .= "WHERE author_user_id = :author_user_id AND network = :network ";
        $q .= "AND DATE(pub_date + interval #gmt_offset# hour) = CURRENT_DATE() ";
        $q .= "AND reply_count_cache + favlike_count_cache = ";
        $q .= "(SELECT MAX(reply_count_cache + favlike_count_cache) FROM #prefix#posts ";
        $q .= "WHERE author_user_id = :author_user_id AND network = :network ";
        $q .= "AND DATE(pub_date + interval #gmt_offset# hour) = CURRENT_DATE()) ";

        $vars = array (
            ':author_user_id' => $author_id,
            ':network' => $network
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod ( __METHOD__ ); }
        $ps = $this->execute ( $q, $vars );
        $post_rows = $this->getDataRowsAsArrays ( $ps );
        $posts = array ();
        foreach ( $post_rows as $row ) {
            $post = new Post ( $row );
            $posts [] = $post;
        }
        return $posts;
    }
}
