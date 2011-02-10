<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.PostMySQLDAO.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Mark Wilkie
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
 * Post Data Access Object
 *
 * The data access object for retrieving and saving posts in the ThinkUp database
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Mark Wilkie
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
    'in_rt_of_user_id', 'location', 'place', 'geo', 'retweet_count_cache', 'old_retweet_count_cache', 
    'reply_count_cache', 'is_reply_by_friend', 'is_retweet_by_friend',
    'reply_retweet_distance', 'is_geo_encoded', 'author_follower_count');

    /**
     * Sanitises an order_by argument to avoid SQL injection and ensure that the table you're ordering by is valid.
     * @param string $order_by Column to order on.
     * @return string Sanitised column name. If the column was invalid, "pub_date" is returned.
     */
    public function sanitiseOrderBy($order_by) {
        // some order_by clauses have a table attached to the front of them, remove this for checking.
        $sans_table = preg_replace('/^[A-Za-z]\./', '', $order_by);
        
        if ( !in_array($sans_table, $this->REQUIRED_FIELDS) && !in_array($sans_table, $this->OPTIONAL_FIELDS  )) {
                $order_by="pub_date";
        }

        return $order_by;
    }

    public function getPost($post_id, $network, $is_public = false) {
        $q = "SELECT  p.*, l.id, l.url, l.expanded_url, l.title, l.clicks, l.is_image, l.error, ";
        $q .= "pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p LEFT JOIN #prefix#links l ON l.post_id = p.post_id AND l.network = p.network ";
        $q .= "WHERE p.post_id=:post_id AND p.network=:network ";
        if ($is_public) {
            $q .= 'AND p.is_protected = 0 ';
        }
        $q .= ';';
        $vars = array(
            ':post_id'=>$post_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        if ($row) {
            $post = $this->setPostWithLink($row);
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

    /**
     * Add author and link object to post
     * @param array $row
     * @return Post post object with author User object and link object member variables
     */
    private function setPostWithAuthorAndLink($row) {
        $user = new User($row, '');
        $link = new Link($row);
        $post = new Post($row);
        $post->author = $user;
        $post->link = $link;
        if (isset($row['short_location'])) {
            $post->short_location = $row['short_location'];
        }
        return $post;
    }

    /**
     * Add link object to post
     * @param arrays $row
     */
    protected function setPostWithLink($row) {
        $post = new Post($row);
        $link = new Link($row);
        $post->link = $link;
        return $post;
    }

    public function getStandaloneReplies($username, $network, $limit) {
        $username = '@'.$username;
        $q = " SELECT p.*, u.*, pub_date + INTERVAL #gmt_offset# hour AS adj_pub_date ";
        $q .= " FROM #prefix#posts AS p ";
        $q .= " INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id WHERE ";

        //fulltext search only works for words longer than 4 chars
        if ( strlen($username) > PostMySQLDAO::FULLTEXT_CHAR_MINIMUM ) {
            $q .= " MATCH (`post_text`) AGAINST(:username IN BOOLEAN MODE) ";
        } else {
            $username = '%'.$username .'%';
            $q .= " post_text LIKE :username ";
        }

        $q .= " AND p.network=:network AND in_reply_to_post_id is null ";
        $q .= " ORDER BY adj_pub_date DESC ";
        $q .= " LIMIT :limit";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network,
            ':limit'=>(int)$limit
        );

        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $replies = array();
        foreach ($all_rows as $row) {
            $replies[] = $this->setPostWithAuthor($row);
        }
        return $replies;
    }

    public function getRepliesToPost($post_id, $network, $order_by = 'default', $unit = 'km', $is_public = false,
    $count= 350, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q = "SELECT u.*, p.*, l.url, l.expanded_url, l.is_image, l.error, ";
        $q .= "(CASE p.is_geo_encoded WHEN 0 THEN 9 ELSE p.is_geo_encoded END) AS geo_status, ";
        $q .= "pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "LEFT JOIN #prefix#links AS l ON l.post_id = p.post_id AND l.network = p.network ";
        $q .= "INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id AND u.network = :user_network ";
        $q .= "WHERE p.network=:network AND in_reply_to_post_id=:post_id ";
        if ($is_public) {
            $q .= "AND u.is_protected = 0 ";
        }

        $class_name = ucfirst($network) . 'Plugin';
        $ordering = @call_user_func($class_name.'::repliesOrdering', $order_by);
        if (empty($ordering)) {
            $ordering = 'pub_date ASC';
        }
        $q .= ' ORDER BY ' . $ordering;

        if ($count > 0) {
            $q .= " LIMIT :start_on_record, :limit;";
        } else {
            $q .= ';';
        }

        $vars = array(
            ':post_id'=>$post_id,
            ':network'=>$network,
            ':user_network'=>($network == 'facebook page') ? 'facebook' : $network
        );

        if ($count > 0) {
            $vars[':limit'] = (int)$count;
            $vars['start_on_record'] = (int)$start_on_record;
        }

        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $replies = array();
        $location = array();
        foreach ($all_rows as $row) {
            if ($row['is_geo_encoded'] == 1) {
                $row['short_location'] = $this->processLocationRows($row['location']);
                if ($unit == 'mi') {
                    $row['reply_retweet_distance'] = $this->calculateDistanceInMiles($row['reply_retweet_distance']);
                }
            }
            $replies[] = $this->setPostWithAuthorAndLink($row);
        }
        return $replies;
    }

    public function getRepliesToPostIterator($post_id, $network, $order_by = 'default', $unit = 'km',
    $is_public = false, $count = 350, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q = "SELECT u.*, p.*, l.url, l.expanded_url, l.is_image, l.error, ";
        $q .= "(CASE p.is_geo_encoded WHEN 0 THEN 9 ELSE p.is_geo_encoded END) AS geo_status, ";
        $q .= "pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "LEFT JOIN #prefix#links AS l ON l.post_id = p.post_id AND l.network = p.network ";
        $q .= "INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id AND u.network = :user_network ";
        $q .= "WHERE p.network=:network AND in_reply_to_post_id=:post_id ";
        if ($is_public) {
            $q .= "AND p.is_protected = 0 ";
        }
        if ($order_by == 'location') {
            $q .= "ORDER BY geo_status, reply_retweet_distance, is_reply_by_friend DESC, follower_count desc ";
        } else {
            $q .= "ORDER BY is_reply_by_friend DESC, follower_count desc ";
        }
        $q .= " LIMIT :start_on_record, :limit;";
        $vars = array(
            ':post_id'=>$post_id,
            ':network'=>$network,
            ':limit'=>(int)$count,
            ':user_network'=>($network=='facebook page')?'facebook':$network,
            ':start_on_record'=>(int)$start_on_record
        );

        $ps = $this->execute($q, $vars);
        return new PostIterator($ps);
    }

    public function getRetweetsOfPost($post_id, $network='twitter', $order_by = 'default', $unit = 'km',
    $is_public = false, $count = null, $page = 1) {
        $q = "SELECT u.*, p.*, l.url, l.expanded_url, l.is_image, l.error, ";
        $q .= "(CASE p.is_geo_encoded WHEN 0 THEN 9 ELSE p.is_geo_encoded END) AS geo_status, ";
        $q .= "pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "LEFT JOIN #prefix#links AS l ON l.post_id = p.post_id AND p.network = l.network ";
        $q .= "INNER JOIN #prefix#users u on p.author_user_id = u.user_id ";
        $q .= "WHERE p.network=:network AND in_retweet_of_post_id=:post_id ";
        if ($is_public) {
            $q .= "AND p.is_protected = 0 ";
        }
        if ($order_by == 'location') {
            $q .= " ORDER BY geo_status, reply_retweet_distance, is_reply_by_friend DESC, follower_count desc ";
        } else if ($order_by != 'default') {
            $order_by = $this->sanitiseOrderBy($order_by);
            $q .= " ORDER BY $order_by DESC ";
        } else {
            $q .= " ORDER BY is_reply_by_friend DESC, follower_count desc ";
        }

        $vars = array(
            ':post_id'=>$post_id,
            ':network'=>$network
        );

        if ($count != null && $count > 0) {
            $start_on_record = ($page - 1) * $count;
            $q .= 'LIMIT :start_on_record, :limit';
            $vars[':limit'] = (int)$count;
            $vars[':start_on_record'] = (int)$start_on_record;
        }
        
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
            $retweets[] = $this->setPostWithAuthorAndLink($row);
        }
        return $retweets;
    }

    public function getRelatedPostsArray($post_id, $network='twitter', $is_public = false, $count = 350, $page = 1,
            $geo_encoded_only = true, $include_original_post = true) {
        $start_on_record = ($page - 1) * $count;
        
        $q = "(SELECT p.*, l.url, l.expanded_url, l.is_image, l.error, pub_date + interval #gmt_offset# hour as
        adj_pub_date
        FROM #prefix#posts p
        LEFT JOIN #prefix#links AS l
        ON l.post_id = p.post_id
        WHERE
        (in_retweet_of_post_id=:post_id OR in_reply_to_post_id=:post_id)
        AND p.network = :network ";
        if ($geo_encoded_only) {
            $q .= "AND is_geo_encoded='1' ";
        }
        if ($is_public) {
            $q .= "AND p.is_protected = 0 ";
        }
        $q .= ") ";
        if ($include_original_post) {
            $q .= "UNION (SELECT p.*, l.url, l.expanded_url, l.is_image, l.error, pub_date + interval #gmt_offset# hour
            as adj_pub_date
            FROM #prefix#posts p
            LEFT JOIN #prefix#links AS l
            ON l.post_id = p.post_id
            WHERE p.post_id=:post_id
            AND p.network = :network ";
            if ($geo_encoded_only) {
                $q .= "AND is_geo_encoded='1' ";
            }
            if ($is_public) {
                $q .= "AND p.is_protected = 0 ";
            }
            $q .= ") ";
        }
        $q .= "ORDER BY reply_retweet_distance, location, id LIMIT :start_on_record, :limit";

        $vars = array(
            ':post_id'=>$post_id,
            ':network'=>$network,
            ':limit'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
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
    public function getPostsAuthorHasRepliedTo($author_id, $count, $network = 'twitter', $page=1) {
        $start_on_record = ($page - 1) * $count;

        $q = "SELECT p1.author_username as questioner_username, p1.author_avatar as questioner_avatar, ";
        $q .= "p2.follower_count as answerer_follower_count, p1.post_id as question_post_id, ";
        $q .= "p1.post_text as question, p1.pub_date + interval #gmt_offset# hour as question_adj_pub_date, ";
        $q .= "p.post_id as answer_post_id, p.author_username as answerer_username, ";
        $q .= "p.author_avatar as answerer_avatar, p3.follower_count as questioner_follower_count, ";
        $q .= "p.post_text as answer, p.network, p.pub_date + interval #gmt_offset# hour as answer_adj_pub_date ";
        $q .= "FROM #prefix#posts p INNER JOIN #prefix#posts p1 on p1.post_id = p.in_reply_to_post_id ";
        $q .= "JOIN #prefix#users p2 on p2.user_id = :author_id ";
        $q .= "JOIN #prefix#users p3 on p3.user_id = p.in_reply_to_user_id ";
        $q .= "WHERE p.author_user_id = :author_id AND p.network=:network AND p.in_reply_to_post_id IS NOT null ";
        $q .= "ORDER BY p.pub_date desc LIMIT :start_on_record, :limit;";
        $vars = array(
            ':author_id'=>$author_id,
            ':network'=>$network,
            ':start_on_record'=>(int)$start_on_record,
            ':limit'=>(int)$count
        );
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
        $q .= " p2.follower_count as questioner_follower_count, p1.post_id as question_post_id, ";
        $q .= " p1.post_text as question, p1.pub_date + interval #gmt_offset# hour as question_adj_pub_date, ";
        $q .= " p.post_id as answer_post_id,  p.author_username as answerer_username, ";
        $q .= " p.author_avatar as answerer_avatar, p3.follower_count as answerer_follower_count, ";
        $q .= " p.post_text as answer, p.network, p.pub_date + interval #gmt_offset# hour as answer_adj_pub_date ";
        $q .= " FROM  #prefix#posts p INNER JOIN #prefix#posts p1 on p1.post_id = p.in_reply_to_post_id ";
        $q .= " JOIN #prefix#users p2 on p2.user_id = :author_id ";
        $q .= " JOIN #prefix#users p3 on p3.user_id = :other_user_id ";
        $q .= " WHERE p.in_reply_to_post_id is not null AND p.network=:network AND ";
        $q .= " (p.author_user_id = :author_id AND p1.author_user_id = :other_user_id) ";
        $q .= " OR (p1.author_user_id = :author_id AND p.author_user_id = :other_user_id) ";
        $q .= " ORDER BY answer_adj_pub_date DESC, question_adj_pub_date ASC ";
        $vars = array(
            ':author_id'=>$author_id,
            ':other_user_id'=>$other_user_id,
            ':network'=>$network
        );
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
        $q .= " WHERE post_id = :post_id AND network=:network;";
        $vars = array(
            ':post_id'=>$post_id,
            ':network'=>$network
        );
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
     * Update retweet count value for 'new-style' retweets.
     * @param int $post_id
     * @param int $retweet_count_cache
     * @param str $network
     * @return int Number of affected rows
     */
    private function updateRetweetCount($post_id, $retweet_count_cache, $network) {
        $q = " UPDATE  #prefix#posts SET retweet_count_cache = :count ";
        $q .= " WHERE post_id = :post_id AND network=:network";
        $vars = array(
            ':post_id'=>$post_id,
            ':network'=>$network,
            ':count' => $retweet_count_cache
        );
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
     * Increment either reply_count_cache or old_retweet_count_cache
     * @param int $post_id
     * @param str $network
     * @param str $fieldname either "reply" or "old_retweet"
     * @return int Number of updated rows
     */
    private function incrementCacheCount($post_id, $network, $fieldname) {
        $fieldname = $fieldname=="reply"?"reply":"old_retweet";
        $q = " UPDATE  #prefix#posts SET ".$fieldname."_count_cache = ".$fieldname."_count_cache + 1 ";
        $q .= "WHERE post_id = :post_id AND network=:network";
        $vars = array(
            ':post_id'=>$post_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
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
                $result = false;
            }
        }
        return $result;
    }

    public function addPostAndEntities($vals, $entities) {
        $urls = null;
        // first add post
        $retval = $this->addPost($vals);
        // if post did not already exist
        if ($retval) {
            // then process entity information as available.
            if (isset($entities) && isset($entities['urls'])) {
                $urls = $entities['urls'];
            }
            // if $urls is null, will extract from tweet content.
            URLProcessor::processTweetURLs($this->logger, $vals, $urls);
        }
        return $retval;
    }

    public function addPost($vals) {
        $retweeted_post_data = null;
        // first, check to see if this is a retweet, with the original post available.
        if (isset($vals['retweeted_post'])) {
            $retweeted_post_data = $vals['retweeted_post']['content'];
            // $this->logger->logInfo("this is a retweet- first processing original " .
            // $retweeted_post_data['post_id'] . ".", __METHOD__.','.__LINE__);
            // it turns out that for a native retweet, Twitter may not reliably update the count stored in the
            // original-- there may be a lag.  So, if there is a first retweet, the original post still
            // may show 0 rts. This is less common w/ the REST API than the streaming API, but does not hurt to
            // address it anyway. So, if we know there was a retweet, but the rt count is showing 0, set it to 1.
            // We know it is at least 1.
            if (isset($retweeted_post_data['retweet_count_cache']) &&
            ($retweeted_post_data['retweet_count_cache'] == 0 )) {
                $retweeted_post_data['retweet_count_cache'] = 1;
            }
            // if so, process the original post first.
            $this->addPostAndEntities($retweeted_post_data, null);
        }
        if ($this->hasAllRequiredFields($vals)) {
            //process location information
            if (!isset($vals['location']) && !isset($vals['geo']) && !isset($vals['place'])) {
                $vals['is_geo_encoded'] = 6;
            }
            //process reply
            if (isset($vals['in_reply_to_post_id']) && $vals['in_reply_to_post_id'] != '') {
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
            // @TODO -- if 'in_rt_of_user_id' is set even though 'in_retweet_of_post_id' is not, we can
            // infer the 'is_retweet_by_friend' information from that instead.
            // [This functionality will be added in the succeeding branch that builds on this work.]
            if (isset($vals['in_retweet_of_post_id']) && $vals['in_retweet_of_post_id'] != '') {
                if (isset($retweeted_post_data)) {
                    // don't need database retrieval to get the necessary data-- use attached orig post info
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
                    $q .= " ".$field."=:".$field.", ";
                    $vars[':'.$field] = $vals[$field];
                }
            }
            //Trim off that last comma and space
            $q = substr($q, 0, (strlen($q)-2));
            $ps = $this->execute($q, $vars);
            $res = $this->getUpdateCount($ps);

            if (isset($vals['retweet_count_cache']) && ($vals['retweet_count_cache'] > 0 ) && !$res) {
                // then the post already existed in database & has RT count > 0, so just update the retweet count.
                $this->updateRetweetCount($vals['post_id'], $vals['retweet_count_cache'], $vals['network']);
            }
            //only update the other post records if insert went through.
            //This avoids incorrect counts in case of attempt to insert dup.
            if ($res) {
                if (isset($replied_to_post)) {
                    $this->incrementReplyCountCache($vals['in_reply_to_post_id'], $vals['network']);
                    $status_message = "Reply found for ".$vals['in_reply_to_post_id'].", ID: ".$vals["post_id"].
                    "; updating reply cache count";
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                }
                // if this is a retweet, but not a new-style retweet, increment the old-style retweet count cache.
                if (isset($retweeted_post) && !(isset($retweeted_post_data))) {
                    $this->incrementRepostCountCache($vals['in_retweet_of_post_id'], $vals['network']);
                    $status_message = "Repost of ".$vals['in_retweet_of_post_id']." by ".$vals["author_username"].
                    " ID: ".$vals["post_id"]."; updating old-style retweet cache count";
                    $this->logger->logInfo($status_message, __METHOD__.','.__LINE__);
                }
            }
            return $res;
        } else {
            //doesn't have all req'd values
            return 0;
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

    public function getAllQuestionPosts($author_id, $network, $count, $page=1, $order_by = 'pub_date',
            $direction = 'DESC', $is_public = false) {
        $start_on_record = ($page - 1) * $count;

        $order_by = $this->sanitiseOrderBy($order_by);

        $direction = $direction == 'DESC' ? 'DESC' : 'ASC';

        if ($is_public) {
            $protected = 'AND p.is_protected = 0';
        } else {
            $protected = '';
        }

        $q = "SELECT l.*, p.*, pub_date + interval #gmt_offset# hour as adj_pub_date FROM ( SELECT * ";
        $q .= "FROM #prefix#posts p ";
        $q .= "WHERE p.author_user_id = :author_id AND p.network=:network ";
        $q .= "AND (in_reply_to_post_id IS null OR in_reply_to_post_id = 0) $protected) AS p ";
        $q .= "LEFT JOIN #prefix#links l ON p.post_id = l.post_id AND p.network = l.network ";
        $q .= "WHERE post_text RLIKE '\\\\?$' OR post_text like '%? %' ";
        $q .= "ORDER BY " . $order_by. ' ' . $direction . ' ';
        $q .= "LIMIT :start_on_record, :limit";
        $vars = array(
            ':author_id'=>$author_id,
            ':network'=>$network,
            ':limit'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        foreach ($all_rows as $row) {
            $posts[] = $this->setPostWithLink($row);
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
        $order_by = $this->sanitiseOrderBy($order_by);

        $q = "SELECT l.*, p.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "LEFT JOIN #prefix#links l ";
        $q .= "ON p.post_id = l.post_id AND p.network = l.network ";
        $q .= "WHERE author_user_id = :author_id AND p.network=:network ";
        if (!$include_replies) {
            $q .= "AND (in_reply_to_post_id IS null OR in_reply_to_post_id = 0) ";
        }
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
        $q .= "LIMIT :start_on_record, :limit";
        $vars = array(
            ':author_id'=>$author_id,
            ':network'=>$network,
            ':limit'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );

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

    public function getPostsByUserInRange($author_id, $network, $from, $until, $order_by="pub_date", $direction="DESC",
            $iterator=false, $is_public = false) {
        $direction = $direction=="DESC" ? "DESC": "ASC";
        
        if (is_string($from)) {
            $from = strtotime($from);
        }
        if (is_string($until)) {
            $until = strtotime($until);
        }

        $order_by = $this->sanitiseOrderBy($order_by);

        $q = "SELECT l.*, p.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "LEFT JOIN #prefix#links l ";
        $q .= "ON p.post_id = l.post_id AND p.network = l.network ";
        $q .= "WHERE author_user_id = :author_id AND p.network=:network AND pub_date BETWEEN FROM_UNIXTIME(:from) ";
        $q .= "AND FROM_UNIXTIME(:until) ";
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
            ':author_id'=> $author_id,
            ':network'=> $network,
            ':from' => $from,
            ':until' => $until
        );
        
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
     * Get all posts by a given user with configurable order by field and direction
     * @param str $author_username
     * @param str $network Default "twitter"
     * @param int|bool $count False if no limit (ie, return all rows)
     * @param str $order_by field name Default "pub_date"
     * @return array Posts with link object set
     */
    private function getAllPostsByUsernameOrderedBy($author_username, $network="twitter", $count=0,
    $order_by="pub_date", $in_last_x_days = 0, $iterator = false, $is_public = false) {
        $order_by = $this->sanitiseOrderBy($order_by);
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network
        );
        $q = "SELECT l.*, p.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "LEFT JOIN #prefix#links l ";
        $q .= "ON p.post_id = l.post_id AND p.network = l.network ";
        $q .= "WHERE author_username = :author_username AND p.network = :network ";

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
        if ($is_public) {
            $q .= 'AND p.is_protected = 0 ';
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

    public function getAllPostsByUsernameIterator($username, $network, $count = 0) {
        return $this->getAllPostsByUsernameOrderedBy($username, $network="twitter", $count, null, null,
        $iterator = true);
    }

    public function getAllPostsByUsername($username, $network) {
        return $this->getAllPostsByUsernameOrderedBy($username, $network="twitter", null, null, null,
        $iterator = false);
    }

    public function getMostRepliedToPostsInLastWeek($username, $network, $count, $is_public = false) {
        return $this->getAllPostsByUsernameOrderedBy($username, $network, $count, 'reply_count_cache', 7,
                $iterator = false, $is_public);
    }

    public function getMostRetweetedPostsInLastWeek($username, $network, $count, $is_public = false) {
        return $this->getAllPostsByUsernameOrderedBy($username, $network, $count, 'retweet_count_cache', 7,
                $iterator = false, $is_public);
    }

    public function getTotalPostsByUser($user_id, $network) {
        $q = "SELECT  COUNT(*) as total ";
        $q .= "FROM #prefix#posts p ";
        $q .= "WHERE author_user_id = :user_id AND network=:network ";
        $q .= "ORDER BY pub_date ASC";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
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
            ':author_id'=>$author_id,
            ':network'=>$network
        );
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

        $order_by = $this->sanitiseOrderBy($order_by);

        $direction = ($direction == 'DESC') ? 'DESC' : 'ASC';

        $author_username = '@'.$author_username;
        $q = " SELECT l.*, p.*, u.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts AS p ";
        $q .= "INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id ";
        $q .= "LEFT JOIN #prefix#links AS l ON p.post_id = l.post_id AND l.network = p.network ";
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
        $ps = $this->execute($q, $vars);
        if($iterator) {
            return (new PostIterator($ps));
        }
        $all_rows = $this->getDataRowsAsArrays($ps);
        $all_posts = array();
        foreach ($all_rows as $row) {
            $all_posts[] = $this->setPostWithAuthorAndLink($row);
        }
        return $all_posts;
    }

    public function getAllReplies($user_id, $network, $count, $page = 1, $order_by = 'pub_date', $direction = 'DESC',
            $is_public = false) {
        $start_on_record = ($page - 1) * $count;

        $order_by = $this->sanitiseOrderBy($order_by);

        $direction = $direction == 'DESC' ? 'DESC' : 'ASC';

        $q = "SELECT l.*, p.*, u.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p LEFT JOIN #prefix#links l ON p.post_id = l.post_id AND l.network = p.network ";
        $q .= "INNER JOIN #prefix#users u ON p.author_user_id = u.user_id ";
        $q .= "WHERE in_reply_to_user_id = :user_id AND p.network=:network ";
        if ($is_public) {
            $q .= 'AND p.is_protected = 0 ';
        }
        $q .= "ORDER BY $order_by $direction LIMIT :start_on_record, :limit;";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network,
            ':start_on_record'=>(int)$start_on_record,
            ':limit'=>(int)$count
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $all_posts = array();
        foreach ($all_rows as $row) {
            $all_posts[] = $this->setPostWithAuthorAndLink($row);
        }
        return $all_posts;
    }

    public function getMostRepliedToPosts($user_id, $network, $count, $page=1, $is_public = false) {
        return $this->getAllPostsByUserID($user_id, $network, $count, "reply_count_cache", "DESC", true, $page,
                $iterator = false, $is_public);
    }

    public function getMostRetweetedPosts($user_id, $network, $count, $page=1, $is_public = false) {
        return $this->getAllPostsByUserID($user_id, $network, $count, "retweet_count_cache", "DESC", true, $page,
                $iterator = false, $is_public);
    }

    public function getOrphanReplies($username, $count, $network = "twitter") {
        $username = "@".$username;
        $q = " SELECT p.* , u.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts p ";
        $q .= " INNER JOIN #prefix#users u ON u.user_id = p.author_user_id WHERE ";
        //fulltext search only works for words longer than 4 chars
        if ( strlen($username) > PostMySQLDAO::FULLTEXT_CHAR_MINIMUM ) {
            $q .= " MATCH (`post_text`) AGAINST(:username IN BOOLEAN MODE) ";
        } else {
            $username = '%'.$username .'%';
            $q .= " post_text LIKE :username ";
        }
        $q .= " AND in_reply_to_post_id is null ";
        $q .= " AND in_retweet_of_post_id is null ";
        $q .= " AND p.network = :network ";
        $q .= " ORDER BY pub_date DESC LIMIT :limit;";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network,
            ':limit'=>(int)$count
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $all_posts = array();
        foreach ($all_rows as $row) {
            $all_posts[] = $this->setPostWithAuthor($row);
        }
        return $all_posts;
    }

    public function assignParent($parent_id, $orphan_id, $network, $former_parent_id = -1) {
        $post = $this->getPost($orphan_id, $network);

        // Check for former_parent_id. The current webfront doesn't send this to us
        // We may even want to remove $former_parent_id as a parameter and just look it up here always -FL
        if ($former_parent_id < 0 && isset($post->in_reply_to_post_id)
        && $this->isPostInDB($post->in_reply_to_post_id, $network)) {
            $former_parent_id = $post->in_reply_to_post_id;
        }

        $q = " UPDATE #prefix#posts SET in_reply_to_post_id = :parent_id ";
        $q .= "WHERE post_id = :orphan_id AND network=:network ";
        $vars = array(
            ':parent_id'=>$parent_id,
            ':orphan_id'=>$orphan_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        if ($parent_id > 0) {
            $this->incrementReplyCountCache($parent_id, $network);
        }
        if ($former_parent_id > 0) {
            $this->decrementReplyCountCache($former_parent_id, $network);
        }
        return $this->getUpdateCount($ps);
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
            ':post_id'=>$post_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function getStrayRepliedToPosts($author_id, $network) {
        $q = "SELECT in_reply_to_post_id FROM #prefix#posts p ";
        $q .= "WHERE p.author_user_id=:author_id AND p.network=:network ";
        $q .= "AND p.in_reply_to_post_id NOT IN (select post_id from #prefix#posts) ";
        $q .= "AND p.in_reply_to_post_id NOT IN (select post_id from #prefix#post_errors);";
        $vars = array(
            ':author_id'=>$author_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    /**
     * Get posts by public instances with custom sort order
     * @param int $page
     * @param int $count
     * @param string $order_by field name
     * @return array Posts with link set
     */
    private function getPostsByPublicInstancesOrderedBy($page, $count, $order_by, $in_last_x_days = 0) {
        $start_on_record = ($page - 1) * $count;
        //make sure order_by var is set to a valid column name, else default to pub_date
        $order_by = $this->sanitiseOrderBy($order_by);
        
        $vars = array(
            ':limit'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );

        $q = "SELECT l.*, p.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p INNER JOIN #prefix#instances i ";
        $q .= "ON p.author_user_id = i.network_user_id ";
        $q .= "LEFT JOIN #prefix#links l ON p.post_id = l.post_id AND l.network = p.network ";
        $q .= "WHERE i.is_public = 1 and (p.reply_count_cache > 0 or p.retweet_count_cache > 0) AND ";
        $q .= " (in_reply_to_post_id = 0 OR in_reply_to_post_id IS null) ";
        if ($in_last_x_days > 0) {
            $q .= "AND pub_date >= DATE_SUB(CURDATE(), INTERVAL :in_last_x_days DAY) ";
            $vars[':in_last_x_days'] = (int)$in_last_x_days;
        }
        $q .= "ORDER BY p.".$order_by." DESC ";
        $q .= "LIMIT :start_on_record, :limit";

        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $all_posts = array();
        foreach ($all_rows as $row) {
            $all_posts[] = $this->setPostWithLink($row);
        }
        return $all_posts;
    }

    public function getPostsByPublicInstances($page, $count) {
        return $this->getPostsByPublicInstancesOrderedBy($page, $count, "pub_date");
    }

    public function getTotalPhotoPagesAndPostsByPublicInstances($count) {
        $q = "SELECT count(*) as total_posts, ceil(count(*) / :count) as total_pages ";
        $q .= "FROM #prefix#posts p INNER JOIN #prefix#instances i ON p.author_user_id = i.network_user_id ";
        $q .= "LEFT JOIN #prefix#links l ON p.post_id = l.post_id AND p.network = l.network ";
        $q .= "WHERE i.is_public = 1 and l.is_image = 1 ";
        $vars = array(
            ':count'=>(int)$count
        );

        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsArray($ps);
    }

    public function getPostsToGeoencode($limit = 5000) {
        $q = "SELECT q.post_id, q.location, q.geo, q.place, q.in_reply_to_post_id, q.in_retweet_of_post_id, ";
        $q.= "q.is_reply_by_friend, q.is_retweet_by_friend FROM ";
        $q .= "(SELECT * FROM #prefix#posts AS p WHERE ";
        $q .= " (p.geo IS NOT null OR p.place IS NOT null OR p.location IS NOT null)";
        $q .= " AND (p.is_geo_encoded='0' OR p.is_geo_encoded='3') ";
        $q .= " ORDER BY id DESC LIMIT :limit) AS q ORDER BY q.id";
        $vars = array(
            ':limit'=>(int)$limit
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        return $all_rows;
    }

    public function setGeoencodedPost($post_id, $is_geo_encoded = 0, $location = null, $geodata = null, $distance = 0) {
        if ($location && $geodata && ($is_geo_encoded>=1 && $is_geo_encoded<=5)) {
            $q = "UPDATE #prefix#posts p SET p.location = :location, p.geo = :geo, ";
            $q .= "p.reply_retweet_distance = :distance, p.is_geo_encoded = :is_geo_encoded ";
            $q .= "WHERE p.post_id = :post_id";
            $vars = array(
                ':location'=>$location,
                ':geo'=>$geodata,
                ':distance'=>$distance,
                ':is_geo_encoded'=>$is_geo_encoded,
                ':post_id'=>$post_id
            );
        } else {
            $q = "UPDATE #prefix#posts p SET p.is_geo_encoded = :is_geo_encoded WHERE p.post_id = :post_id";
            $vars = array(
                ':is_geo_encoded'=>$is_geo_encoded,
                ':post_id'=>$post_id
            );
        }
        $ps = $this->execute($q, $vars);
        if ($this->getUpdateCount($ps) > 0) {
            $logstatus = "Geolocation for post $post_id IS_GEO_ENCODED: $is_geo_encoded";
            $this->logger->logInfo($logstatus, __METHOD__.','.__LINE__);
            return true;
        } else {
            $logstatus = "Geolocation for post_id=$post_id IS_GEO_ENCODED: $is_geo_encoded not saved";
            $this->logger->logInfo($logstatus, __METHOD__.','.__LINE__);
            return false;
        }
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
        $q  = "SELECT COUNT(*) AS num_posts, source";
        $q .= "  FROM #prefix#posts ";
        $q .= " WHERE author_user_id = :author_id AND network = :network";
        $q .= " GROUP BY source";
        $vars = array(
            ':author_id'=>$author_id,
            ':network'=>$network
        );
        $rows = $this->getDataRowsAsArrays($this->execute($q, $vars));
        $all_time_clients_usage = self::cleanClientsNames($rows);

        $q  = "SELECT COUNT(*) AS num_posts, source";
        $q .= "  FROM (";
        $q .= "       SELECT *";
        $q .= "         FROM #prefix#posts ";
        $q .= "        WHERE author_user_id = :author_id AND network = :network";
        $q .= "        ORDER BY pub_date DESC";
        $q .= "        LIMIT 25) p";
        $q .= " GROUP BY source";
        $vars = array(
            ':author_id'=>$author_id,
            ':network'=>$network
        );
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

}