<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.PostDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * Post Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface PostDAO {
    /**
     * Get post by ID
     * @param int $post_id
     * @param str $network
     * @return Post Post with optional link member object set, null if post doesn't exist
     */
    public function getPost($post_id, $network);

    /**
     * Get replies to a username that aren't linked to a specific post by that user
     * @param str $username
     * @param str $network
     * @param int $limit
     * @return array Array of Post objects with author member variable set
     */
    public function getStandaloneReplies($username, $network, $limit);

    /**
     * Get replies to a post
     * @param int $post_id
     * @param str $network
     * @param str $order_by Order of sorting posts
     * @param int $unit Defaults to km
     * @param bool $public Defaults to false
     * @param int $count Defaults to 350
     * @return array Posts with author object set, and optional link object set
     */
    public function getRepliesToPost($post_id, $network, $order_by = 'default', $unit = 'km', $is_public = false,
    $count = 350);

    /**
     * Get replies Iterator to a post
     * @param int $post_id
     * @param str $network
     * @param str $order_by Order of sorting posts
     * @param int $unit Defaults to km
     * @param bool $public Defaults to false
     * @param int $count Defaults to 350
     * @return Iterator Posts with author object set, and optional link object set
     */
    public function getRepliesToPostIterator($post_id, $network, $order_by = 'default', $unit = 'km', $is_public = false,
    $count = 350);


    /**
     * Get retweets of post
     * @param int $post_id
     * @param str $network Defaults to 'twitter'
     * @param str $order_by Order of sorting posts
     * @param int $unit Defaults to km
     * @param bool $public Defaults to false
     * @return array Retweets of post with optional link object set
     */
    public function getRetweetsOfPost($post_id, $network = 'twitter', $order_by = 'default', $unit = 'km',
    $is_public = false);

    /**
     * Get all related posts (retweets and replies)
     * @param int $post_id
     * @param str $network Defaults to 'twitter'
     * @param bool $is_public Defaults to false
     * @return str json with data of all related posts along with data about the main post
     */
    public function getRelatedPosts($post_id, $network = 'twitter', $is_public = false, $count = 350);

    /**
     * Get posts that author has replied to (for question/answer exchanges)
     * @param int $author_id
     * @param int $count
     * @param str $network Defaults to 'twitter'
     * @param int $page Page number, defaults to 1
     * @return array Question and answer values
     */
    public function getPostsAuthorHasRepliedTo($author_id, $count, $network = 'twitter', $page=1);

    /**
     * Get all the back-and-forth posts between two users.
     * @param int $author_id
     * @param int $other_user_id
     * @param str $network Defaults to 'twitter'
     * @return array Back and forth posts
     */
    public function getExchangesBetweenUsers($author_id, $other_user_id, $network = 'twitter');

    /**
     * Check to see if Post is in database
     * @param int $post_id
     * @param str $network
     * @return bool true if post is in the database
     */
    public function isPostInDB($post_id, $network);
    /**
     * Check to see if reply is in database
     * This is an alias for isPostInDB
     * @param int $post_id
     * @param str $network
     * @return bool true if reply is in the database
     */
    public function isReplyInDB($post_id, $network);

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
     * @param str  $network
     * @param int $count
     * @param int $page
     * @param bool $include_replies If true, return posts with in_reply_to_post_id set
     * @return array Posts by author with link set
     */
    public function getAllPosts($author_id, $network, $count, $page=1, $include_replies=true);

    /**
     * Get all posts by an author given an author ID that contain a question mark
     * @param int $author_id
     * @param str  $network
     * @param int $count
     * @param int $page
     * @return array Posts by author with a question mark in them with link set
     */
    public function getAllQuestionPosts($author_id, $network, $count, $page=1);

    /**
     * Get all posts by an author given an author ID
     * @param int $author_id
     * @param str  $network
     * @param int $count
     * @param bool $include_replies If true, return posts with in_reply_to_post_id set
     * @return Iterator Posts Iterator
     */
    public function getAllPostsIterator($author_id, $network, $count, $include_replies=true);

    /**
     * Get all posts by author given the author's username
     * @param str $username
     * @param str $network
     * @return array Posts by author (no link set)
     */
    public function getAllPostsByUsername($username, $network);

    /**
     * Get post iterator by author given the author's username
     * @param str $username
     * @param str $network
     * @return Iterator PostIterator by author (no link set)
     */
    public function getAllPostsByUsernameIterator($username, $network);

    /**
     * Get count of posts by author user ID
     * @param int $user_id
     * @param str $network
     * @return int total posts
     */
    public function getTotalPostsByUser($user_id, $network);

    /**
     * Get all the sources of an author's posts and their count
     * @param int $author_id
     * @param str $network
     * @return array "source"=>"web", "total"=>15
     */
    public function getStatusSources($author_id, $network);

    /**
     * Get a certain number of mentions of a username on a given network
     * @param str  $author_username
     * @param int $count
     * @param str $network defaults to "twitter"
     * @return Iterator PostIterator object
     */
    public function getAllMentionsIterator($author_username, $count, $network = "twitter");

    /**
     * Get a certain number of mentions of a username on a given network
     * @param str  $author_username
     * @param int $count
     * @param str $network defaults to "twitter"
     * @param int $page Page number, defaults to 1
     * @param bool $public Public mentions only, defaults to false
     * @return array of Post objects with author and link set
     */
    public function getAllMentions($author_username, $count, $network = "twitter", $page=1, $public=false);

    /**
     * Get all replies to a given user ID
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @return array Posts with author and link set
     */
    public function getAllReplies($user_id, $network, $count);

    /**
     * Get posts by a user ordered by reply count desc
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page Page number, defaults to 1
     * @return array Posts with link object set
     */
    public function getMostRepliedToPosts($user_id, $network, $count, $page=1);

    /**
     * Get posts by a usre ordered by retweet count desc
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page Page number, defaults to 1
     * @return array Posts with link object set
     */
    public function getMostRetweetedPosts($user_id, $network, $count, $page=1);

    /**
     * Get a page of posts by public instances ordered by pub_date desc
     * @param int $page
     * @param int $count
     * @return array Posts with link set
     */
    public function getPostsByPublicInstances($page, $count);

    /**
     * Get total photo posts and pages by public instances
     * @param int $count number of photo posts per page
     * @return array Posts with link set
     */
    public function getTotalPhotoPagesAndPostsByPublicInstances($count);

    /**
     * Assign parent replied-to post ID to a given post, and increment/decrement reply count cache totals as needed
     * @param int $parent_id
     * @param int $orphan_id
     * @param str $network
     * @param int $former_parent_id
     * @return int total affected rows
     */
    public function assignParent($parent_id, $orphan_id, $network, $former_parent_id = -1);

    /**
     * Get orphan replies--mentions that are not associated with a particular post (not a reply or retweet).
     * @param str $username
     * @param int $count
     * @param str $network Default 'twitter'
     * @return array Post objects with author set
     */
    public function getOrphanReplies($username, $count, $network = 'twitter');

    /**
     * Get stray replied-to posts--posts that are listed in the in_repy_to_post_id field, but aren't in the posts table
     * @param int $author_id
     * @param str $network
     * @return array $row['in_reply_to_post_id']
     */
    public function getStrayRepliedToPosts($author_id, $network);

    /**
     * Get posts that have not been geocoded--posts that have their is_geo_encoded field set to 0
     * @param int $limit
     * @return array $row['id'],$row['location'],$row['geo'],$row['post']
     * @return array $row['in_reply_to_post_id'],$row['in_retweet_of_post_id']
     */
    public function getPostsToGeoencode($limit = 500);

    /**
     * Set geo-location data for post
     * @param int $post_id
     * @param int $is_geo_encoded 0 if Not Geoencoded, 1 if Successful,
     * 2 if ZERO_RESULTS, 3 if OVER_QUERY_LIMIT, 4 if REQUEST_DENIED, 5 if INVALID_REQUEST
     * @param string $location
     * @param string $geodata
     * @param int $distance
     * @return bool True if geo-location data for post added successfully
     */
    public function setGeoencodedPost($post_id, $is_geo_encoded = 0, $location = NULL, $geodata = NULL, $distance = 0);

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

    /**
     * Calculate how much each client is used by a user on a specific network
     * @param int $author_id
     * @param string $network
     * @return array First element of the returned array is an array of all the clients the user used, ever.
     *               The second element is an array of the clients used for the last 25 posts.
     *               Both arrays are sorted by number of use, descending.
     */
    public function getClientsUsedByUserOnNetwork($author_id, $network);
}