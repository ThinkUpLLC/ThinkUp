<?php
/**
 * Post Data Access Object
 * The data access object for retrieving and saving posts in the ThinkTank database
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once 'model/class.PDODAO.php';
require_once 'model/interface.PostDAO.php';

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
    var $REQUIRED_FIELDS =  array('post_id','author_username','author_fullname','author_avatar','author_user_id',
    'post_text','pub_date','source','network');

    /**
     * Optional fields in a post
     * @var array
     */
    var $OPTIONAL_FIELDS = array('in_reply_to_user_id', 'in_reply_to_post_id','in_retweet_of_post_id', 'location',
    'place', 'geo', 'retweet_count_cache', 'mention_count_cache');

    public function getPost($post_id) {
        $q = "SELECT  p.*, l.id, l.url, l.expanded_url, l.title, l.clicks, l.is_image, l.error, ";
        $q .= "pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p LEFT JOIN #prefix#links l ON l.post_id = p.post_id ";
        $q .= "WHERE p.post_id=:post_id;";
        $vars = array(
            ':post_id'=>$post_id
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
        return $post;
    }

    /**
     * Add link object to post
     * @param arrays $row
     */
    private function setPostWithLink($row) {
        $post = new Post($row);
        $link = new Link($row);
        $post->link = $link;
        return $post;
    }

    function getStandaloneReplies($username, $limit) {
        $username = '@'.$username;
        $q = " SELECT p.*, u.*, pub_date - INTERVAL #gmt_offset# hour AS adj_pub_date ";
        $q .= " FROM #prefix#posts AS p ";
        $q .= " INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id WHERE ";

        //fulltext search only works for words longer than 4 chars
        if ( strlen($username) > PostMySQLDAO::FULLTEXT_CHAR_MINIMUM ) {
            $q .= " MATCH (`post_text`) AGAINST(:username IN BOOLEAN MODE) ";
        } else {
            $username = '%'.$username .'%';
            $q .= " post_text LIKE :username ";
        }

        $q .= " AND in_reply_to_post_id is null ";
        $q .= " ORDER BY adj_pub_date DESC ";
        $q .= " LIMIT :limit";
        $vars = array(
            ':username'=>$username,
            ':limit'=>$limit
        );

        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $replies = array();
        foreach ($all_rows as $row) {
            $replies[] = $this->setPostWithAuthor($row);
        }
        return $replies;
    }

    function getRepliesToPost($post_id, $is_public = false, $count = 350) {
        $q = " SELECT p.*, l.url, l.expanded_url, l.is_image, l.error, u.*, ";
        $q .= " pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts p ";
        $q .= " LEFT JOIN #prefix#links AS l ON l.post_id = p.post_id ";
        $q .= " INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id ";
        $q .= " WHERE in_reply_to_post_id=:post_id ";
        if ($is_public) {
            $q .= "AND u.is_protected = 0 ";
        }
        $q .= " ORDER BY follower_count desc ";
        $q .= " LIMIT :limit;";

        $vars = array(
            ':post_id'=>$post_id,
            ':limit'=>$count
        );

        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $replies = array();
        foreach ($all_rows as $row) {
            $replies[] = $this->setPostWithAuthorAndLink($row);
        }
        return $replies;
    }

    function getRetweetsOfPost($post_id, $is_public = false) {
        $q = "SELECT p.*, u.*,  l.url, l.expanded_url, l.is_image, l.error, ";
        $q .= " pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts p ";
        $q .= " LEFT JOIN #prefix#links AS l ON l.post_id = p.post_id ";
        $q .= " INNER JOIN #prefix#users u on p.author_user_id = u.user_id ";
        $q .= " WHERE  in_retweet_of_post_id=:post_id ";
        if ($is_public) {
            $q .= "AND u.is_protected = 0 ";
        }
        $q .= "  ORDER BY follower_count DESC;";

        $vars = array(
            ':post_id'=>$post_id
        );

        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $retweets = array();
        foreach ($all_rows as $row) {
            $retweets[] = $this->setPostWithAuthorAndLink($row);
        }
        return $retweets;
    }

    function getPostReachViaRetweets($post_id) {
        $q = "SELECT  SUM(u.follower_count) AS total ";
        $q .= "FROM  #prefix#posts p INNER JOIN #prefix#users u ";
        $q .= "ON p.author_user_id = u.user_id WHERE in_retweet_of_post_id=:post_id ";
        $q .= "ORDER BY follower_count desc;";
        $vars = array(
            ':post_id'=>$post_id
        );
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        return $row['total'];
    }

    /**
     * @TODO: Figure out a better way to do this, only returns 1-1 exchanges, not back-and-forth threads
     */
    function getPostsAuthorHasRepliedTo($author_id, $count) {
        $q = "SELECT p1.author_username as questioner_username, p1.author_avatar as questioner_avatar, ";
        $q .= " p2.follower_count as answerer_follower_count, p1.post_id as question_post_id, ";
        $q .= " p1.post_text as question, p1.pub_date - interval #gmt_offset# hour as question_adj_pub_date, ";
        $q .= " p.post_id as answer_post_id, p.author_username as answerer_username, ";
        $q .= " p.author_avatar as answerer_avatar, p3.follower_count as questioner_follower_count, ";
        $q .= " p.post_text as answer, p.pub_date - interval #gmt_offset# hour as answer_adj_pub_date ";
        $q .= " FROM #prefix#posts p INNER JOIN #prefix#posts p1 on p1.post_id = p.in_reply_to_post_id ";
        $q .= " JOIN #prefix#users p2 on p2.user_id = :author_id ";
        $q .= " JOIN #prefix#users p3 on p3.user_id = p.in_reply_to_user_id ";
        $q .= " WHERE p.author_user_id = :author_id AND p.in_reply_to_post_id IS NOT NULL ";
        $q .= " ORDER BY p.pub_date desc LIMIT :limit;";
        $vars = array(
            ':author_id'=>$author_id,
            ':limit'=>$count
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $posts_replied_to = array();
        foreach ($all_rows as $row) {
            $posts_replied_to[] = $row;
        }
        return $posts_replied_to;
    }

    public function getExchangesBetweenUsers($author_id, $other_user_id) {
        $q = "SELECT   p1.author_username as questioner_username, p1.author_avatar as questioner_avatar, ";
        $q .= " p2.follower_count as questioner_follower_count, p1.post_id as question_post_id, ";
        $q .= " p1.post_text as question, p1.pub_date - interval #gmt_offset# hour as question_adj_pub_date, ";
        $q .= " p.post_id as answer_post_id,  p.author_username as answerer_username, ";
        $q .= " p.author_avatar as answerer_avatar, p3.follower_count as answerer_follower_count, ";
        $q .= " p.post_text as answer, p.pub_date - interval #gmt_offset# hour as answer_adj_pub_date ";
        $q .= " FROM  #prefix#posts p INNER JOIN #prefix#posts p1 on p1.post_id = p.in_reply_to_post_id ";
        $q .= " JOIN #prefix#users p2 on p2.user_id = :author_id ";
        $q .= " JOIN #prefix#users p3 on p3.user_id = :other_user_id ";
        $q .= " WHERE p.in_reply_to_post_id is not null AND ";
        $q .= " (p.author_user_id = :author_id AND p1.author_user_id = :other_user_id) ";
        $q .= " OR (p1.author_user_id = :author_id AND p.author_user_id = :other_user_id) ";
        $q .= " ORDER BY p.pub_date DESC ";
        $vars = array(
            ':author_id'=>$author_id,
            ':other_user_id'=>$other_user_id
        );
        $ps = $this->execute($q, $vars);

        $all_rows = $this->getDataRowsAsArrays($ps);
        $posts_replied_to = array();
        foreach ($all_rows as $row) {
            $posts_replied_to[] = $row;
        }
        return $posts_replied_to;
    }

    public function getPublicRepliesToPost($post_id) {
        return $this->getRepliesToPost($post_id, true);
    }

    public function isPostInDB($post_id) {
        $q = "SELECT post_id FROM  #prefix#posts ";
        $q .= " WHERE post_id = :post_id;";
        $vars = array(
            ':post_id'=>$post_id
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }

    public function isReplyInDB($post_id) {
        return $this->isPostInDB($post_id);
    }

    /**
     * Increment reply cache count
     * @param int $post_id
     * @return int number of updated rows (1 if successful, 0 if not)
     */
    private function incrementReplyCountCache($post_id) {
        return $this->incrementCacheCount($post_id, "mention");
    }

    /**
     * Increment retweet cache count
     * @param int $post_id
     * @return int number of updated rows (1 if successful, 0 if not)
     */
    private function incrementRepostCountCache($post_id) {
        return $this->incrementCacheCount($post_id, "retweet");
    }

    /**
     * Increment either mention_cache_count or retweet_cache_count
     * @param int $post_id
     * @param string $fieldname either "mention" or "retweet"
     * @return int number of updated rows
     */
    private function incrementCacheCount($post_id, $fieldname) {
        $fieldname = $fieldname=="mention"?"mention":"retweet";
        $q = " UPDATE  #prefix#posts SET ".$fieldname."_count_cache = ".$fieldname."_count_cache + 1 ";
        $q .= "WHERE post_id = :post_id";
        $vars = array(
            ':post_id'=>$post_id
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

    public function addPost($vals) {
        if (!$this->isPostInDB($vals['post_id'])) {
            if ($this->hasAllRequiredFields($vals)) {
                //SQL variables to bind
                $vars = array();
                //SQL query
                $q = "INSERT INTO #prefix#posts SET ";
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

                if (isset($vals['in_reply_to_post_id']) && $vals['in_reply_to_post_id'] != ''
                && $this->isPostInDB($vals['in_reply_to_post_id'])) {
                    $this->incrementReplyCountCache($vals['in_reply_to_post_id']);
                    $status_message = "Reply found for ".$vals['in_reply_to_post_id'].", ID: ".$vals["post_id"].
                    "; updating reply cache count";
                    $this->logger->logStatus($status_message, get_class($this));
                }

                if (isset($vals['in_retweet_of_post_id']) && $vals['in_retweet_of_post_id'] != ''
                && $this->isPostInDB($vals['in_retweet_of_post_id'])) {
                    $this->incrementRepostCountCache($vals['in_retweet_of_post_id']);
                    $status_message = "Repost of ".$vals['in_retweet_of_post_id']." by ".$vals["author_username"].
                    " ID: ".$vals["post_id"]."; updating retweet cache count";
                    $this->logger->logStatus($status_message, get_class($this));
                }

                return $this->getUpdateCount($ps);
            } else {
                //doesn't have all req'd values
                return 0;
            }
        } else {
            //already in DB
            return 0;
        }
    }

    public function getAllPosts($author_id, $count, $include_replies=true) {
        return $this->getAllPostsByUserID($author_id, $count, "pub_date", "DESC", $include_replies);
    }

    /**
     * Get all posts by a given user with configurable order by field and direction
     * @TODO Bind order_by and direction params as strings without single quotes
     * @param int $author_id
     * @param int $count
     * @param string $order_by field name
     * @param string $direction either "DESC" or "ASC
     * @param bool $include_replies If true, return posts with in_reply_to_post_id set, if not don't
     * @return array Posts with link object set
     */
    private function getAllPostsByUserID($author_id, $count, $order_by="pub_date", $direction="DESC",
    $include_replies=true) {
        $direction = $direction=="DESC" ? "DESC": "ASC";
        if ( !in_array($order_by, $this->REQUIRED_FIELDS) && !in_array($order_by, $this->OPTIONAL_FIELDS  )) {
            $order_by="pub_date";
        }
        $q = "SELECT l.*, p.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts p";
        $q .= " LEFT JOIN #prefix#links l ";
        $q .= " ON p.post_id = l.post_id ";
        $q .= " WHERE author_user_id = :author_id ";
        if (!$include_replies) {
            $q .= " AND (in_reply_to_post_id IS NULL OR in_reply_to_post_id = 0) ";
        }
        $q .= " ORDER BY ".$order_by." ".$direction." ";
        $q .= " LIMIT :limit";

        $vars = array(
            ':author_id'=>$author_id,
            ':limit'=>$count 
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        foreach ($all_rows as $row) {
            $posts[] = $this->setPostWithLink($row);
        }
        return $posts;
    }

    public function getAllPostsByUsername($username) {
        $q = "SELECT p.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p ";
        $q .= "WHERE author_username = :username ";
        $q .= "ORDER BY pub_date ASC";
        $vars = array(
            ':username'=>$username
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $posts = array();
        foreach ($all_rows as $row) {
            $posts[] = new Post($row);
        }
        return $posts;
    }

    public function getTotalPostsByUser($user_id) {
        $q = "SELECT  COUNT(*) as total ";
        $q .= "FROM #prefix#posts p ";
        $q .= "WHERE author_user_id = :user_id ";
        $q .= "ORDER BY pub_date ASC";
        $vars = array(
            ':user_id'=>$user_id
        );
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);
        return $result["total"];
    }

    public function getStatusSources($author_id) {
        $q = "SELECT source, count(source) as total ";
        $q .= "FROM #prefix#posts WHERE ";
        $q .= "author_user_id = :author_id ";
        $q .= "GROUP BY source  ORDER BY total DESC;";
        $vars = array(
            ':author_id'=>$author_id
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    public function getAllMentions($author_username, $count, $network = "twitter") {
        $author_username = '@'.$author_username;
        $q = " SELECT l.*, p.*, u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts AS p ";
        $q .= " INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id ";
        $q .= " LEFT JOIN #prefix#links AS l ON p.post_id = l.post_id ";
        $q .= " WHERE p.network = :network AND";
        //fulltext search only works for words longer than 4 chars
        if ( strlen($author_username) > PostMySQLDAO::FULLTEXT_CHAR_MINIMUM ) {
            $q .= " MATCH (`post_text`) AGAINST(:author_username IN BOOLEAN MODE) ";
        } else {
            $author_username = '%'.$author_username .'%';
            $q .= " post_text LIKE :author_username ";
        }
        $q .= " ORDER BY pub_date DESC ";
        $q .= " LIMIT :limit;";
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$network,
            ':limit'=>$count
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $all_posts = array();
        foreach ($all_rows as $row) {
            $all_posts[] = $this->setPostWithAuthorAndLink($row);
        }
        return $all_posts;
    }

    public function getAllReplies($user_id, $count) {
        $q = "SELECT l.*, p.*, u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p LEFT JOIN #prefix#links l ON p.post_id = l.post_id ";
        $q .= "INNER JOIN #prefix#users u ON p.author_user_id = u.user_id ";
        $q .= "WHERE in_reply_to_user_id = :user_id ORDER BY pub_date DESC LIMIT :limit;";
        $vars = array(
            ':user_id'=>$user_id,
            ':limit'=>$count
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $all_posts = array();
        foreach ($all_rows as $row) {
            $all_posts[] = $this->setPostWithAuthorAndLink($row);
        }
        return $all_posts;
    }

    public function getMostRepliedToPosts($user_id, $count) {
        return $this->getAllPostsByUserID($user_id, $count, "mention_count_cache", "DESC");
    }

    public function getMostRetweetedPosts($user_id, $count) {
        return $this->getAllPostsByUserID($user_id, $count, "retweet_count_cache", "DESC");
    }

    public function getOrphanReplies($username, $count, $network = "twitter") {
        $username = "@".$username;
        $q = " SELECT p.* , u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
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
            ':limit'=>$count
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $all_posts = array();
        foreach ($all_rows as $row) {
            $all_posts[] = $this->setPostWithAuthor($row);
        }
        return $all_posts;
    }

    public function getLikelyOrphansForParent($parent_pub_date, $author_user_id, $author_username, $count) {
        $username = "@".$author_username;
        $q = " SELECT p.* , u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts p ";
        $q .= " INNER JOIN #prefix#users AS u ON p.author_user_id = u.user_id WHERE ";
        //fulltext search only works for words longer than 4 chars
        if ( strlen($username) > PostMySQLDAO::FULLTEXT_CHAR_MINIMUM ) {
            $q .= " MATCH (`post_text`) AGAINST(:username IN BOOLEAN MODE) ";
        } else {
            $username = '%'.$username .'%';
            $q .= " post_text LIKE :username ";
        }
        $q .= " AND pub_date > :parent_pub_date ";
        $q .= " AND in_reply_to_post_id IS NULL ";
        $q .= " AND in_retweet_of_post_id IS NULL ";
        $q .= " AND p.author_user_id != :author_user_id ";
        $q .= " ORDER BY pub_date ASC ";
        $q .= " LIMIT :limit";
        $vars = array(
            ':username'=>$username,
            ':parent_pub_date'=>$parent_pub_date,
            ':author_user_id'=>$author_user_id,
            ':limit'=>$count
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $all_posts = array();
        foreach ($all_rows as $row) {
            $all_posts[] = $this->setPostWithAuthor($row);
        }
        return $all_posts;
    }

    public function assignParent($parent_id, $orphan_id, $former_parent_id = -1) {
        $post = $this->getPost($orphan_id);

        // Check for former_parent_id. The current webfront doesn't send this to us
        // We may even want to remove $former_parent_id as a parameter and just look it up here always -FL
        if ($former_parent_id < 0 && isset($post->in_reply_to_post_id)
        && $this->isPostInDB($post->in_reply_to_post_id)) {
            $former_parent_id = $post->in_reply_to_post_id;
        }

        $q = " UPDATE #prefix#posts SET in_reply_to_post_id = :parent_id ";
        $q .= "WHERE post_id = :orphan_id ";
        $vars = array(
            ':parent_id'=>$parent_id,
            ':orphan_id'=>$orphan_id
        );
        $ps = $this->execute($q, $vars);

        if ($parent_id > 0) {
            $this->incrementReplyCountCache($parent_id);
        }
        if ($former_parent_id > 0) {
            $this->decrementReplyCountCache($former_parent_id);
        }
        return $this->getUpdateCount($ps);
    }
    /**
     * Decrement a post's mention_count_cache
     * @param int $post_id
     * @return in count of affected rows
     */
    private function decrementReplyCountCache($post_id) {
        $q = "UPDATE #prefix#posts SET mention_count_cache = mention_count_cache - 1 ";
        $q .= "WHERE post_id = :post_id";
        $vars = array(
            ':post_id'=>$post_id
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function getStrayRepliedToPosts($author_id) {
        $q = "SELECT in_reply_to_post_id FROM #prefix#posts p ";
        $q .= "WHERE p.author_user_id=:author_id ";
        $q .= "AND p.in_reply_to_post_id NOT IN (select post_id from #prefix#posts) ";
        $q .= "AND p.in_reply_to_post_id NOT IN (select post_id from #prefix#post_errors);";
        $vars = array(
            ':author_id'=>$author_id
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    /**
     * Get posts by public instances with custom sort order
     * @TODO bind $order_by without single quotes
     * @param int $page
     * @param int $count
     * @param string $order_by field name
     * @return array Posts with link set
     */
    private function getPostsByPublicInstancesOrderedBy($page, $count, $order_by, $in_last_x_days = 0) {
        $start_on_record = ($page - 1) * $count;
        //make sure order_by var is set to a valid column name, else default to pub_date
        if ( !in_array($order_by, $this->REQUIRED_FIELDS) && !in_array($order_by, $this->OPTIONAL_FIELDS  )) {
            $order_by="pub_date";
        }
        $vars = array(
            ':limit'=>$count,
            ':start_on_record'=>(int)$start_on_record
        );

        $q = "SELECT l.*, p.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p INNER JOIN #prefix#instances i ";
        $q .= "ON p.author_user_id = i.network_user_id ";
        $q .= "LEFT JOIN #prefix#links l ON p.post_id = l.post_id ";
        $q .= "WHERE i.is_public = 1 and (p.mention_count_cache > 0 or p.retweet_count_cache > 0) AND ";
        $q .= " (in_reply_to_post_id = 0 OR in_reply_to_post_id IS NULL) ";
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

    public function getTotalPagesAndPostsByPublicInstances($count, $in_last_x_days=0) {
        $vars = array(
            ':count'=>(int)$count
        );

        $q = "SELECT count(*) as total_posts, ceil(count(*) / :count) as total_pages ";
        $q .= "FROM #prefix#posts p INNER JOIN #prefix#instances i ";
        $q .= "ON p.author_user_id = i.network_user_id LEFT JOIN #prefix#links l ";
        $q .= "ON p.post_id = l.post_id ";
        $q .= "WHERE i.is_public = 1 and (p.mention_count_cache > 0 or p.retweet_count_cache > 0) AND ";
        $q .= " (in_reply_to_post_id = 0 OR in_reply_to_post_id IS NULL) ";
        if ($in_last_x_days > 0) {
            $q .= "AND pub_date >= DATE_SUB(CURDATE(), INTERVAL :in_last_x_days DAY) ";
            $vars[':in_last_x_days'] = (int)$in_last_x_days;
        }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsArray($ps);
    }

    public function getPostsByPublicInstances($page, $count) {
        return $this->getPostsByPublicInstancesOrderedBy($page, $count, "pub_date");
    }

    public function getPhotoPostsByPublicInstances($page, $count) {
        $start_on_record = ($page - 1) * $count;
        $q = "SELECT l.*, p.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#posts p INNER JOIN #prefix#instances i ON p.author_user_id = i.network_user_id ";
        $q .= "LEFT JOIN #prefix#links l ON p.post_id = l.post_id WHERE i.is_public = 1 and l.is_image = 1 ";
        $q .= "ORDER BY p.pub_date DESC ";
        $q .= "LIMIT :start_on_record, :limit";
        $vars = array(
            ':limit'=>$count,
            ':start_on_record'=>(int)$start_on_record
        );

        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $all_posts = array();
        foreach ($all_rows as $row) {
            $all_posts[] = $this->setPostWithLink($row);
        }
        return $all_posts;
    }

    public function getTotalPhotoPagesAndPostsByPublicInstances($count) {
        $q = "SELECT count(*) as total_posts, ceil(count(*) / :count) as total_pages ";
        $q .= "FROM #prefix#posts p INNER JOIN #prefix#instances i ON p.author_user_id = i.network_user_id ";
        $q .= "LEFT JOIN #prefix#links l ON p.post_id = l.post_id WHERE i.is_public = 1 and l.is_image = 1 ";
        $vars = array(
            ':count'=>(int)$count
        );

        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsArray($ps);
    }

    public function getLinkPostsByPublicInstances($page, $count) {
        $start_on_record = ($page - 1) * $count;
        $q = "SELECT l.*, p.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts p INNER JOIN #prefix#instances i ";
        $q .= "ON p.author_user_id = i.network_user_id LEFT JOIN #prefix#links l ON p.post_id = l.post_id ";
        $q .= "WHERE i.is_public = 1 and l.expanded_url != '' and l.is_image = 0 ORDER BY p.pub_date DESC ";
        $q .= "LIMIT :start_on_record, :limit ";
        $vars = array(
            ':limit'=>$count,
            ':start_on_record'=>(int)$start_on_record
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $all_posts = array();
        foreach ($all_rows as $row) {
            $all_posts[] = $this->setPostWithLink($row);
        }
        return $all_posts;
    }

    public function getTotalLinkPagesAndPostsByPublicInstances($count) {
        $q = "SELECT count(*) as total_posts, ceil(count(*) / :count) as total_pages ";
        $q .= "FROM #prefix#posts p INNER JOIN #prefix#instances i ON p.author_user_id = i.network_user_id ";
        $q .= "LEFT JOIN #prefix#links l ON p.post_id = l.post_id ";
        $q .= " WHERE i.is_public = 1 and l.expanded_url != '' and l.is_image = 0 ";
        $vars = array(
            ':count'=>(int)$count
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsArray($ps);
    }

    public function getMostRepliedToPostsByPublicInstances($page, $count) {
        return $this->getPostsByPublicInstancesOrderedBy($page, $count, "mention_count_cache");
    }

    public function getMostRetweetedPostsByPublicInstances($page, $count) {
        return $this->getPostsByPublicInstancesOrderedBy($page, $count, "retweet_count_cache");
    }

    public function getMostRepliedToPostsByPublicInstancesInLastWeek($page, $count) {
        return $this->getPostsByPublicInstancesOrderedBy($page, $count, "mention_count_cache", 7);
    }

    public function getMostRetweetedPostsByPublicInstancesInLastWeek($page, $count) {
        return $this->getPostsByPublicInstancesOrderedBy($page, $count, "retweet_count_cache", 7);
    }

    public function isPostByPublicInstance($post_id) {
        $q = "SELECT *, pub_date - interval #gmt_offset# hour as adj_pub_date FROM #prefix#posts p ";
        $q .= "INNER JOIN #prefix#instances i ON p.author_user_id = i.network_user_id ";
        $q .= "WHERE i.is_public = 1 and p.post_id = :post_id;";
        $vars = array(
            ':post_id'=>$post_id
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }
}