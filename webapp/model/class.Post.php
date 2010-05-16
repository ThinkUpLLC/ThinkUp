<?php
class Post {
    var $id;
    var $post_id;
    var $author_user_id;
    var $author_fullname;
    var $author_username;
    var $author_avatar;
    var $post_text;
    var $source;
    var $pub_date;
    var $adj_pub_date;
    var $in_reply_to_user_id;
    var $in_reply_to_post_id;
    var $mention_count_cache;
    var $in_retweet_of_post_id;
    var $retweet_count_cache;
    var $network;

    var $author; //optional user object
    var $link; //optional link object

    function Post($val) {
        $this->id = $val["id"];
        $this->post_id = $val["post_id"];
        $this->author_user_id = $val["author_user_id"];
        $this->author_username = $val["author_username"];
        $this->author_avatar = $val["author_avatar"];
        $this->post_text = $val["post_text"];
        $this->source = $val["source"];
        $this->pub_date = $val["pub_date"];
        $this->adj_pub_date = $val["adj_pub_date"];
        $this->in_reply_to_user_id = $val["in_reply_to_user_id"];
        $this->in_reply_to_post_id = $val["in_reply_to_post_id"];
        $this->mention_count_cache = $val["mention_count_cache"];
        $this->in_retweet_of_post_id = $val["in_retweet_of_post_id"];
        $this->retweet_count_cache = $val["retweet_count_cache"];
        $this->network = $val["network"];
    }

    public static function extractURLs($post_text) {
        preg_match_all('!https?://[\S]+!', $post_text, $matches);
        return $matches[0];
    }

}


class PostDAO extends MySQLDAO {

    function getPost($post_id) {
        $q = "
            SELECT 
                p.*, l.id, l.url, l.expanded_url, l.title, l.clicks, l.is_image, l.error, pub_date - interval #gmt_offset# hour as adj_pub_date
            FROM 
                #prefix#posts p
            LEFT JOIN
                #prefix#links l
            ON 
                l.post_id = p.post_id
            WHERE
                 p.post_id='".$post_id."';";
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        $post = $this->setPostWithLink($row);
        mysql_free_result($sql_result); # Free up memory
        return $post;
    }

    private function setPostWithAuthor($row) {
        $u = new User($row, '');
        $t = new Post($row);
        $t->author = $u;
        return $t;
    }

    private function setPostWithAuthorAndLink($row) {
        $u = new User($row, '');
        $l = new Link($row);
        $t = new Post($row);
        $t->author = $u;
        $t->link = $l;
        return $t;
    }

    private function setPostWithLink($row) {
        $p = new Post($row);
        $l = new Link($row);
        $p->link = $l;
        return $p;
    }


    function getStandaloneReplies($username, $limit) {
        $q = " SELECT t.*, u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts AS t ";
        $q .= " INNER JOIN #prefix#users AS u ON t.author_user_id = u.user_id WHERE ";
        $q .= ' MATCH (`post_text`) AGAINST(\'"'.$username.'"\' IN BOOLEAN MODE)';
        $q .= " AND in_reply_to_post_id=0 ";
        $q .= " ORDER BY adj_pub_date DESC ";
        $q .= " LIMIT ".$limit;
        $sql_result = $this->executeSQL($q);
        $strays = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $strays[] = $this->setPostWithAuthor($row);
        }
        mysql_free_result($sql_result);
        return $strays;
    }

    function getRepliesToPost($post_id, $public = false, $count = 350) {
        $condition = "";
        if ($public)
        $condition = "AND u.is_protected = 0";
        $q = " SELECT t.*, l.url, l.expanded_url, l.is_image, l.error, u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts t ";
        $q .= " LEFT JOIN #prefix#links AS l ON l.post_id = t.post_id ";
        $q .= " INNER JOIN #prefix#users AS u ON t.author_user_id = u.user_id ";
        $q .= " WHERE in_reply_to_post_id=".$post_id." ".$condition;
        $q .= " ORDER BY follower_count desc ";
        $q .= " LIMIT $count; ";

        $sql_result = $this->executeSQL($q);
        $posts_stored = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $posts_stored[] = $this->setPostWithAuthorAndLink($row);
        }
        mysql_free_result($sql_result); # Free up memory
        return $posts_stored;
    }

    function getRetweetsOfPost($post_id, $public = false) {
        $condition = "";
        if ($public)
        $condition = "AND u.is_protected = 0";

        $q = "
            select 
                t.*, u.*,  l.url, l.expanded_url, l.is_image, l.error, pub_date - interval #gmt_offset# hour as adj_pub_date 
            from 
                #prefix#posts t
            LEFT JOIN #prefix#links AS l ON l.post_id = t.post_id    
            inner join 
                #prefix#users u 
            on 
                t.author_user_id = u.user_id 
            where 
                in_retweet_of_post_id=".$post_id." 
                ".$condition."    
            order by 
                follower_count desc;";
        $sql_result = $this->executeSQL($q);
        $posts_stored = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $posts_stored[] = $this->setPostWithAuthorAndLink($row);
        }
        mysql_free_result($sql_result); # Free up memory
        return $posts_stored;
    }

    function getPostReachViaRetweets($post_id) {
        $q = "
            select 
                SUM(u.follower_count) as total
            from 
                #prefix#posts t
            inner join 
                #prefix#users u 
            on 
                t.author_user_id = u.user_id 
            where 
                in_retweet_of_post_id=".$post_id." 
            order by 
                follower_count desc;";
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        mysql_free_result($sql_result); # Free up memory
        return $row['total'];
    }

    function getPostsAuthorHasRepliedTo($author_id, $count) {
        //TODO: Figure out a better way to do this, only returns 1-1 exchanges, not back-and-forth threads

        $q = "
            SELECT
                t1.author_username as questioner_username, t1.author_avatar as questioner_avatar, t2.follower_count as answerer_follower_count, t1.post_id as question_post_id, t1.post_text as question, t1.pub_date - interval #gmt_offset# hour as question_adj_pub_date, t.post_id as answer_post_id, t.author_username as answerer_username, t.author_avatar as answerer_avatar, t3.follower_count as questioner_follower_count, t.post_text as answer, t.pub_date - interval #gmt_offset# hour as answer_adj_pub_date
            FROM 
                #prefix#posts t 
            INNER JOIN 
                #prefix#posts t1 on t1.post_id = t.in_reply_to_post_id 
            JOIN 
                #prefix#users t2 on t2.user_id = ".$author_id."
            JOIN 
                #prefix#users t3 on t3.user_id = t.in_reply_to_user_id 
            WHERE 
                t.author_user_id = ".$author_id." AND t.in_reply_to_post_id is not null 
            ORDER BY
                t.pub_date desc 
            LIMIT ".$count.";";
         
        $sql_result = $this->executeSQL($q);
        $posts_replied_to = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $posts_replied_to[] = $row;
        }
        mysql_free_result($sql_result); # Free up memory
        return $posts_replied_to;

    }

    function getExchangesBetweenUsers($author_id, $other_user_id) {

        $q = "
        
            SELECT
                t1.author_username as questioner_username, t1.author_avatar as questioner_avatar, t2.follower_count as questioner_follower_count, t1.post_id as question_post_id, t1.post_text as question, t1.pub_date - interval #gmt_offset# hour as question_adj_pub_date, t.post_id as answer_post_id,  t.author_username as answerer_username, t.author_avatar as answerer_avatar, t3.follower_count as answerer_follower_count, t.post_text as answer, t.pub_date - interval #gmt_offset# hour as answer_adj_pub_date
            FROM 
                #prefix#posts t 
            INNER JOIN 
                #prefix#posts t1 on t1.post_id = t.in_reply_to_post_id 
            JOIN 
                #prefix#users t2 on t2.user_id = ".$author_id."
            JOIN 
                #prefix#users t3 on t3.user_id = ".$other_user_id."
            WHERE 
                t.in_reply_to_post_id is not null AND
                (t.author_user_id = ".$author_id." AND t1.author_user_id = ".$other_user_id.")
                OR
                (t1.author_user_id = ".$author_id." AND t.author_user_id = ".$other_user_id.")
            ORDER BY
                t.pub_date desc";

        $sql_result = $this->executeSQL($q);
        $posts_replied_to = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $posts_replied_to[] = $row;
        }
        mysql_free_result($sql_result); # Free up memory
        return $posts_replied_to;

    }


    function getPublicRepliesToPost($post_id) {
        return $this->getRepliesToPost($post_id, true);
    }

    function addPost($vals) {
        if (!$this->isPostInDB($vals['post_id'])) {

            foreach ($vals as $key=>$value) {
                $vals[$key] = mysql_real_escape_string($value);
            }
            $post_sql = $vals['post_text'];
            if ($vals['in_reply_to_user_id'] == '') {
                $post_in_reply_to_user_id = 'NULL';
            } else {
                $post_in_reply_to_user_id = $vals['in_reply_to_user_id'];
            }

            if ($vals['in_reply_to_post_id'] == '') {
                $post_in_reply_to_post_id = 'NULL';
            } else {
                $post_in_reply_to_post_id = $vals['in_reply_to_post_id'];
            }
            if (isset($vals['in_retweet_of_post_id'])) {
                if ($vals['in_retweet_of_post_id'] == '') {
                    $post_in_retweet_of_post_id = 'NULL';
                } else {
                    $post_in_retweet_of_post_id = $vals['in_retweet_of_post_id'];
                }
            } else
            $post_in_retweet_of_post_id = 'NULL';

            if (!isset($vals["network"])) {
                $vals["network"] = 'twitter';
            }


            $q = "
                INSERT INTO #prefix#posts
                    (post_id,
                    author_username,author_fullname,author_avatar,author_user_id,
                    post_text,pub_date,in_reply_to_user_id,in_reply_to_post_id,in_retweet_of_post_id,source,network)
                VALUES (
                {$vals['post_id']}, '{$vals['user_name']}',
                    '{$vals['full_name']}', '{$vals['avatar']}', '{$vals['user_id']}',
                    '$post_sql',
                    '{$vals['pub_date']}', $post_in_reply_to_user_id, $post_in_reply_to_post_id,$post_in_retweet_of_post_id,'{$vals['source']}','{$vals['network']}')
            ";
                $foo = $this->executeSQL($q);

                if ($vals['in_reply_to_post_id'] != '' && $this->isPostInDB($vals['in_reply_to_post_id'])) {
                    $this->incrementReplyCountCache($vals['in_reply_to_post_id']);
                    $status_message = "Reply found for ".$vals['in_reply_to_post_id'].", ID: ".$vals["post_id"]."; updating reply cache count";
                    $this->logger->logStatus($status_message, get_class($this));
                    $status_message = "";
                }

                if (isset($vals['in_retweet_of_post_id']) && $vals['in_retweet_of_post_id'] != '' && $this->isPostInDB($vals['in_retweet_of_post_id'])) {
                    $this->incrementRepostCountCache($vals['in_retweet_of_post_id']);
                    $status_message = "Repost of ".$vals['in_retweet_of_post_id'].", ID: ".$vals["post_id"]."; updating retweet cache count";
                    $this->logger->logStatus($status_message, get_class($this));
                    $status_message = "";
                }


                return mysql_affected_rows();
        } else {
            return 0;
        }

    }


    function isPostInDB($post_id) {
        $q = "
            SELECT 
                post_id 
            FROM 
                #prefix#posts 
            WHERE post_id = ".$post_id;
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) > 0)
        return true;
        else
        return false;
    }

    function isReplyInDB($post_id) {
        $q = "
            SELECT 
                post_id 
            FROM 
                #prefix#posts 
            WHERE 
                post_id = ".$post_id;
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) > 0)
        return true;
        else
        return false;
    }

    function incrementReplyCountCache($post_id) {
        return $this->incrementCacheCount($post_id, "mention");
    }

    function incrementRepostCountCache($post_id) {
        return $this->incrementCacheCount($post_id, "retweet");
    }

    private function incrementCacheCount($post_id, $fieldname) {
        $q = "
            UPDATE 
                #prefix#posts
            SET 
                ".$fieldname."_count_cache = ".$fieldname."_count_cache + 1
            WHERE 
                post_id = ".$post_id."
        ";
        $foo = $this->executeSQL($q);
        return mysql_affected_rows();
    }


    function decrementReplyCountCache($post_id) {
        $q = "
            UPDATE 
                #prefix#posts
            SET 
                mention_count_cache = mention_count_cache - 1
            WHERE 
                post_id = ".$post_id."
        ";
        $foo = $this->executeSQL($q);
        return mysql_affected_rows();
    }

    function getAllPosts($author_id, $count) {
        $q = "
            SELECT 
                l.*, t.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
            FROM 
                #prefix#posts t
            LEFT JOIN
                #prefix#links l
            ON t.post_id = l.post_id
            WHERE 
                author_user_id = ".$author_id."
            ORDER BY 
                pub_date DESC 
            LIMIT ".$count.";";
         
        $sql_result = $this->executeSQL($q);
        $all_posts = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $all_posts[] = $this->setPostWithLink($row);
        }
        mysql_free_result($sql_result);
        return $all_posts;
    }

    function getAllPostsByUsername($username) {

        $q = "
            SELECT 
                t.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
            FROM 
                #prefix#posts t
            WHERE 
                author_username = '".$username."'
            ORDER BY 
                pub_date ASC";
        $sql_result = $this->executeSQL($q);
        $all_posts = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $all_posts[] = new Post($row);
        }
        mysql_free_result($sql_result);
        return $all_posts;
    }

    function getTotalPostsByUser($userid) {
        $q = "
            SELECT 
                COUNT(*) as total 
            FROM 
                #prefix#posts t
            WHERE 
                author_user_id = '".$userid."'
            ORDER BY 
                pub_date ASC";
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        return $row["total"];
    }

    function getStatusSources($author_id) {
        $q = "
            SELECT 
                source, count(source) as total 
            FROM 
                #prefix#posts
            WHERE 
                author_user_id = ".$author_id."            
            GROUP BY source
            ORDER BY total DESC;";
        $sql_result = $this->executeSQL($q);
        $all_sources = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $all_sources[] = $row;
        }
        mysql_free_result($sql_result);
        return $all_sources;
    }


    function getAllMentions($author_username, $count, $network = "twitter") {

        $q = " SELECT l.*, t.*, u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts AS t ";
        $q .= " INNER JOIN #prefix#users AS u ON t.author_user_id = u.user_id ";
        $q .= " LEFT JOIN #prefix#links AS l ON t.post_id = l.post_id ";
        $q .= ' WHERE t.network = \''.$network.'\'';
        $q .= ' AND MATCH (`post_text`) AGAINST(\'"'.$author_username.'"\' IN BOOLEAN MODE)';
        $q .= " ORDER BY pub_date DESC ";
        $q .= " LIMIT ".$count.";";
        $sql_result = $this->executeSQL($q);
        $all_posts = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $all_posts[] = $this->setPostWithAuthorAndLink($row);
        }
        mysql_free_result($sql_result);
        return $all_posts;
    }

    function getAllReplies($user_id, $count) {

        $q = "
            SELECT 
                l.*, t.*, u.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
            FROM 
                #prefix#posts t
            LEFT JOIN
                #prefix#links l
            ON t.post_id = l.post_id                
            INNER JOIN
                #prefix#users u
            ON
                t.author_user_id = u.user_id
            WHERE 
                 in_reply_to_user_id = ".$user_id."
            ORDER BY 
                pub_date DESC 
            LIMIT ".$count.";";
        $sql_result = $this->executeSQL($q);
        $all_posts = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $all_posts[] = $this->setPostWithAuthorAndLink($row);
        }
        mysql_free_result($sql_result);
        return $all_posts;
    }


    function getMostRepliedToPosts($user_id, $count) {
        $q = "
            SELECT 
                l.*, t.* , pub_date - interval #gmt_offset# hour as adj_pub_date 
            FROM 
                #prefix#posts t
            LEFT JOIN
                #prefix#links l
            ON t.post_id = l.post_id                
            WHERE
                author_user_id = ".$user_id."
            ORDER BY
                mention_count_cache DESC 
            LIMIT ".$count.";";
        $sql_result = $this->executeSQL($q);
        $most_replied_to_posts = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $most_replied_to_posts[] = $this->setPostWithLink($row);
        }
        mysql_free_result($sql_result);
        return $most_replied_to_posts;

    }

    function getMostRetweetedPosts($user_id, $count, $public = false) {

        $q = "
            SELECT 
                l.*, t.* , pub_date - interval #gmt_offset# hour as adj_pub_date 
            FROM 
                #prefix#posts t
            LEFT JOIN
                #prefix#links l
            ON t.post_id = l.post_id                
            WHERE
                author_user_id = ".$user_id."
            ORDER BY
                retweet_count_cache DESC 
            LIMIT ".$count.";";
        $sql_result = $this->executeSQL($q);
        $most_retweeted_posts = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $most_retweeted_posts[] = $this->setPostWithLink($row);
        }
        mysql_free_result($sql_result);
        return $most_retweeted_posts;

    }

    function getOrphanReplies($username, $count, $network = "twitter") {

        $q = " SELECT t.* , u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts AS t ";
        $q .= " INNER JOIN #prefix#users AS u ON u.user_id = t.author_user_id ";
        $q .= " WHERE ";
        $q .= ' MATCH (`post_text`) AGAINST(\'"'.$username.'"\' IN BOOLEAN MODE)';
        $q .= " AND in_reply_to_post_id is null ";
        $q .= " AND in_retweet_of_post_id is null ";
        $q .= " AND t.network = '".$network."' ";
        $q .= " ORDER BY pub_date DESC ";
        $q .= " LIMIT ".$count.";";
        $sql_result = $this->executeSQL($q);
        $orphan_replies = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $orphan_replies[] = $this->setPostWithAuthor($row);
        }
        mysql_free_result($sql_result);
        return $orphan_replies;
    }

    function getLikelyOrphansForParent($parent_pub_date, $author_user_id, $author_username, $count) {

        $q = " SELECT t.* , u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#posts AS t ";
        $q .= " INNER JOIN #prefix#users AS u ON t.author_user_id = u.user_id ";
        $q .= " WHERE ";
        $q .= ' MATCH (`post_text`) AGAINST(\'"'.$author_username.'"\' IN BOOLEAN MODE)';
        $q .= " AND pub_date > '".$parent_pub_date."' ";
        $q .= " AND in_reply_to_post_id IS NULL ";
        $q .= " AND in_retweet_of_post_id IS NULL ";
        $q .= " AND t.author_user_id != ".$author_user_id;
        $q .= " ORDER BY pub_date ASC ";
        $q .= " LIMIT ".$count;
        $sql_result = $this->executeSQL($q);
        $likely_orphans = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $likely_orphans[] = $this->setPostWithAuthor($row);
        }
        mysql_free_result($sql_result);
        return $likely_orphans;

    }

    function assignParent($parent_id, $orphan_id, $former_parent_id = -1) {

        $post = $this->getPost($orphan_id);

        // Check for former_parent_id. The current webfront doesn't send this to us
        // We may even want to remove $former_parent_id as a parameter and just look it up here always -FL
        if ($former_parent_id < 0 && isset($post->in_reply_to_post_id) && $this->isPostInDB($post->in_reply_to_post_id)) {
            $former_parent_id = $post->in_reply_to_post_id;
        }

        $q = "
            UPDATE 
                #prefix#posts
            SET 
                in_reply_to_post_id = ".$parent_id."
            WHERE
                post_id = ".$orphan_id;
        $this->executeSQL($q);


        if ($parent_id > 0) {
            $this->incrementReplyCountCache($parent_id);
        }
        if ($former_parent_id > 0) {
            $this->decrementReplyCountCache($former_parent_id);
        }
        return mysql_affected_rows();
    }

    function getStrayRepliedToPosts($author_id) {
        $q = "
            SELECT
                in_reply_to_post_id
            FROM 
                #prefix#posts t 
            WHERE 
                t.author_user_id=".$author_id."
                AND t.in_reply_to_post_id NOT IN (select post_id from #prefix#posts) 
                 AND t.in_reply_to_post_id NOT IN (select post_id from #prefix#post_errors);";
        $sql_result = $this->executeSQL($q);
        $strays = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $strays[] = $row;
        }
        mysql_free_result($sql_result);
        return $strays;
    }

    private function getPostsByPublicInstancesOrderedBy($page, $count, $orderby) {
        $start_on_record = ($page - 1) * $count;
        $q = "
            SELECT 
                l.*, t.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
            FROM 
                #prefix#posts t
            INNER JOIN
                #prefix#instances i
            ON
                t.author_user_id = i.network_user_id
            LEFT JOIN
                #prefix#links l
            ON t.post_id = l.post_id

            WHERE
                i.is_public = 1 and (t.mention_count_cache > 0 or t.retweet_count_cache > 0) and in_reply_to_post_id is NULL
            ORDER BY
                t.".$orderby." DESC
            LIMIT ".$start_on_record.", ".$count;
        $sql_result = $this->executeSQL($q);
        $posts = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $posts[] = $this->setPostWithLink($row);
        }
        mysql_free_result($sql_result);
        return $posts;
    }

    function getTotalPagesAndPostsByPublicInstances($count) {
        $q = "
            SELECT 
                count(*) as total_posts, ceil(count(*) / $count) as total_pages
            FROM 
                #prefix#posts t
            INNER JOIN
                #prefix#instances i
            ON
                t.author_user_id = i.network_user_id
            LEFT JOIN
                #prefix#links l
            ON t.post_id = l.post_id

            WHERE
                i.is_public = 1 and (t.mention_count_cache > 0 or t.retweet_count_cache > 0) and in_reply_to_post_id is NULL ";
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        return $row;
    }

    function getPostsByPublicInstances($page, $count) {
        return $this->getPostsByPublicInstancesOrderedBy($page, $count, "pub_date");
    }

    function getPhotoPostsByPublicInstances($page, $count) {
        $start_on_record = ($page - 1) * $count;
        $q = "
            SELECT 
                l.*, t.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
            FROM 
                #prefix#posts t
            INNER JOIN
                #prefix#instances i
            ON
                t.author_user_id = i.network_user_id
            LEFT JOIN
                #prefix#links l
            ON t.post_id = l.post_id

            WHERE
                i.is_public = 1 and l.is_image = 1 
            ORDER BY
                t.pub_date DESC
            LIMIT ".$start_on_record.", ".$count;
        $sql_result = $this->executeSQL($q);
        $posts = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $posts[] = $this->setPostWithLink($row);
        }
        mysql_free_result($sql_result);
        return $posts;
    }

    function getTotalPhotoPagesAndPostsByPublicInstances($count) {
        $q = "
            SELECT 
                                count(*) as total_posts, ceil(count(*) / $count) as total_pages
            FROM 
                #prefix#posts t
            INNER JOIN
                #prefix#instances i
            ON
                t.author_user_id = i.network_user_id
            LEFT JOIN
                #prefix#links l
            ON t.post_id = l.post_id

            WHERE
                i.is_public = 1 and l.is_image = 1 
                        ";
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        return $row;
    }

    function getLinkPostsByPublicInstances($page, $count) {
        $start_on_record = ($page - 1) * $count;
        $q = "
            SELECT 
                l.*, t.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
            FROM 
                #prefix#posts t
            INNER JOIN
                #prefix#instances i
            ON
                t.author_user_id = i.network_user_id
            LEFT JOIN
                #prefix#links l
            ON t.post_id = l.post_id

            WHERE
                i.is_public = 1 and l.expanded_url != '' and l.is_image = 0 
            ORDER BY
                t.pub_date DESC
            LIMIT ".$start_on_record.", ".$count;
        $sql_result = $this->executeSQL($q);
        $posts = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $posts[] = $this->setPostWithLink($row);
        }
        mysql_free_result($sql_result);
        return $posts;
    }

    function getTotalLinkPagesAndPostsByPublicInstances($count) {
        $q = "
            SELECT 
                                count(*) as total_posts, ceil(count(*) / $count) as total_pages
            FROM 
                #prefix#posts t
            INNER JOIN
                #prefix#instances i
            ON
                t.author_user_id = i.network_user_id
            LEFT JOIN
                #prefix#links l
            ON t.post_id = l.post_id

            WHERE
                i.is_public = 1 and l.expanded_url != '' and l.is_image = 0 
                        ";
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        return $row;
    }

    function getMostRepliedToPostsByPublicInstances($page, $count) {
        return $this->getPostsByPublicInstancesOrderedBy($page, $count, "mention_count_cache");
    }

    function getMostRetweetedPostsByPublicInstances($page, $count) {
        return $this->getPostsByPublicInstancesOrderedBy($page, $count, "retweet_count_cache");
    }


    function isPostByPublicInstance($id) {
        $q = "
            SELECT 
                *, pub_date - interval #gmt_offset# hour as adj_pub_date 
            FROM 
                #prefix#posts t
            INNER JOIN
                #prefix#instances i
            ON
                t.author_user_id = i.network_user_id
            WHERE
                i.is_public = 1 and t.post_id = ".$id.";";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) > 0)
        $r = true;
        else
        $r = false;

        mysql_free_result($sql_result);
        return $r;
    }

}

class PostErrorDAO extends MySQLDAO {
    //Construct is located in parent

    function insertError($id, $error_code, $error_text, $issued_to) {
        $q = "
            INSERT INTO
                 #prefix#post_errors (post_id, error_code, error_text, error_issued_to_user_id)
            VALUES 
                (".$id.", ".$error_code.", '".$error_text."', ".$issued_to.") ";
        $sql_result = $this->executeSQL($q);
        if (mysql_affected_rows() > 0)
        return true;
        else
        return false;
    }
}

class RetweetDetector {

    public function __construct() {
    }

    public static function isRetweet($post, $ownerName) {
        if (strpos(strtolower($post), strtolower("RT @".$ownerName)) === false)
        return false;
        else
        return true;
    }


    public static function detectOriginalTweet($retweet_text, $recentPosts) {
        $originalPostId = false;
        foreach ($recentPosts as $t) {
            $snip = substr($t->post_text, 0, 24);
            if (strpos($retweet_text, $snip) != false)
            $originalPostId = $t->post_id;
        }

        return $originalPostId;
    }
}
?>
