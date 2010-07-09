<?php
/**
 * Post Data Access Object interface
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface PostDAO {
    /**
     * Get post by ID
     * @param int $post_id
     * @return Post Post with link member variable set, null if post doesn't exist
     */
    public function getPost($post_id);

    /**
     * Get replies to a username that aren't linked to a specific post by that user
     * @TODO Add network as one of the selection criteria, this is a Twitter-specific list
     * @param string $username
     * @param int $limit
     * @return array Array of Post objects with author member variable set
     */
    public function getStandaloneReplies($username, $limit);

    /**
     * Get replies to a post
     * @param int $post_id
     * @param bool $public
     * @param int $count
     * @return array Posts with author and link objects set
     */
    public function getRepliesToPost($post_id, $is_public = false, $count = 350);

    /**
     * Get retweets of post
     * @param int $post_id
     * @param bool $is_public
     * @return array Retweets of post
     */
    public function getRetweetsOfPost($post_id, $is_public = false);

    /**
     * Get total number of followers by retweeters
     * @param int $post_id
     * @return int total followers
     */
    public function getPostReachViaRetweets($post_id);

    /**
     * Get posts that author has replied to (for question/answer exchanges)
     * @param int $author_id
     * @param int $count
     * @return array Question and answer values
     */
    public function getPostsAuthorHasRepliedTo($author_id, $count);

    /**
     * Get all the back-and-forth posts between two users.
     * @param int $author_id
     * @param int $other_user_id
     */
    public function getExchangesBetweenUsers($author_id, $other_user_id);

    /**
     * Get public replies to post
     * @param int $post_id
     * @return array Public posts with author and link objects set
     */
    public function getPublicRepliesToPost($post_id);

    /**
     * Check to see if Post is in database
     * @param int $post_id
     * @return bool true if post is in the database
     */
    public function isPostInDB($post_id);
    /**
     * Check to see if reply is in database
     * This is an alias for isPostInDB
     * @param int $post_id
     * @return bool true if reply is in the database
     */
    public function isReplyInDB($post_id);

    /**
     * Insert post given an array of values
     *
     * Values expected:
     * <code>
     *  $vals['post_id']
     *  $vals['user_name']
     *  $vals['full_name']
     *  $vals['avatar']
     *  $vals['user_id']
     *  $vals['post_text']
     *  $vals['pub_date']
     *  $vals['source']
     *  $vals['network']
     * </code>
     *
     * @param array $vals see above
     * @return int number of posts inserted
     */
    public function addPost($vals);

    /**
     * Get all posts by an author given an author ID
     * @param int $author_id
     * @param string  $network
     * @param int $count
     * @param bool $include_replies If true, return posts with in_reply_to_post_id set
     * @return array Posts by author with link set
     */
    public function getAllPosts($author_id, $count, $include_replies=true);

    /**
     * Get all posts by author given the author's username
     * @param string $username
     * @return array Posts by author (no link set)
     */
    public function getAllPostsByUsername($username);

    /**
     * Get count of posts by author user ID
     * @param int $user_id
     * @return int total posts
     */
    public function getTotalPostsByUser($user_id);

    /**
     * Get all the sources of an author's posts and their count
     * @param int $author_id
     * @return array "source"=>"web", "total"=>15
     */
    public function getStatusSources($author_id);

    /**
     * Get a certain number of mentions of a username on a given network
     * @param string  $author_username
     * @param int $count
     * @param string $network defaults to "twitter"
     * @return array of Post objects with author and link set
     */
    public function getAllMentions($author_username, $count, $network = "twitter");

    /**
     * Get all replies to a given user ID
     * @param int $user_id
     * @param int $count
     * @return array Posts with author and link set
     */
    public function getAllReplies($user_id, $count);

    /**
     * Get posts by a user ordered by reply count desc
     * @param int $user_id
     * @param int $count
     * @return array Posts with link object set
     */
    public function getMostRepliedToPosts($user_id, $count);

    /**
     * Get posts by a usre ordered by retweet count desc
     * @param int $user_id
     * @param int $count
     * @return array Posts with link object set
     */
    public function getMostRetweetedPosts($user_id, $count);

    /**
     * Get a page of posts by public instances ordered by pub_date desc
     * @param int $page
     * @param int $count
     * @return array Posts with link set
     */
    public function getPostsByPublicInstances($page, $count);

    /**
     * Get a page of posts by public instances ordered by reply_count_cache desc
     * @param int $page
     * @param int $count
     * @return array Posts with link set
     */
    public function getMostRepliedToPostsByPublicInstances($page, $count);

    /**
     * Get a page of posts by public instances ordered by retweet_count_cache desc
     * @param int $page
     * @param int $count
     * @return array Posts with link set
     */
    public function getMostRetweetedPostsByPublicInstances($page, $count);

    /**
     * Get total posts and pages by public instances for a specified number of past days
     * @param int $count Number of posts per page
     * @param int $last_x_days 0 for all time (default)
     * @return array $row['total_posts'], $row['total_pages']
     */
    public function getTotalPagesAndPostsByPublicInstances($count, $last_x_days=0);

    /**
     * Get photo posts by public instances
     * @param int $page
     * @param int $count
     * @return array posts with link set
     */
    public function getPhotoPostsByPublicInstances($page, $count);

    /**
     * Get total photo posts and pages by public instances
     * @param int $count number of photo posts per page
     * @return array Posts with link set
     */
    public function getTotalPhotoPagesAndPostsByPublicInstances($count);

    /**
     * Get link posts by public instances
     * @param int $page
     * @param int $count number of posts per page
     * @return array Posts with link set
     */
    public function getLinkPostsByPublicInstances($page, $count);

    /**
     * Get total link posts and pages by public instances
     * @param int $count number of posts per page
     * @return array posts with link set
     */
    public function getTotalLinkPagesAndPostsByPublicInstances($count);

    /**
     * Assign parent replied-to post ID to a given post, and increment/decrement reply count cache totals as needed
     * @param int $parent_id
     * @param int $orphan_id
     * @param int $former_parent_id
     * @return int total affected rows
     */
    public function assignParent($parent_id, $orphan_id, $former_parent_id = -1);

    /**
     * Get orphan replies--mentions that are not associated with a particular post (not a reply or retweet).
     * @param string $username
     * @param int $count
     * @param string $network Default "twitter"
     * @return array Post objects with author set
     */
    public function getOrphanReplies($username, $count, $network = "twitter");

    /**
     * Get orphan replies (no in_reply_to_post_id or in_retweet_of_post_id) posted after a potential parent post
     * @param string $parent_pub_date
     * @param int $author_user_id
     * @param string $author_username
     * @param int $count
     * @return array Post objects with author set
     */
    public function getLikelyOrphansForParent($parent_pub_date, $author_user_id, $author_username, $count);

    /**
     * Get stray replied-to posts--posts that are listed in the in_repy_to_post_id field, but aren't in the posts table
     * @param $author_id
     * @return array $row['in_reply_to_post_id']
     */
    public function getStrayRepliedToPosts($author_id);

    /**
     * Check if post is by a public instance
     * @param int $post_id
     * @return bool True if post is by a public instance
     */
    public function isPostByPublicInstance($post_id);

    /**
     * Get a page of posts in the last week by public instances ordered by reply_count_cache desc
     * @param int $page
     * @param int $count
     * @return array Posts with link set
     */
    public function getMostRetweetedPostsByPublicInstancesInLastWeek($page, $count);

    /**
     * Get a page of posts in the last week by public instances ordered by retweet_count_cache desc
     * @param int $page
     * @param int $count
     * @return array Posts with link set
     */
    public function getMostRepliedToPostsByPublicInstancesInLastWeek($page, $count);

    /**
     * Get specified number of most-replied-to posts by a username on a network
     * @param str $username
     * @param str $network
     * @param int $count
     * @return array Posts
     */
    public function getMostRepliedToPostsInLastWeek($username, $network, $count);

    /**
     * Get specified number of most-retweeted posts by a username on a network
     * @param str $username
     * @param str $network
     * @param int $count
     * @return array Posts
     */
    public function getMostRetweetedPostsInLastWeek($username, $network, $count);
}