<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.PostDAO.php
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
 * Post Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface PostDAO {
    /**
     * Get post by ID
     * @param str $post_id
     * @param str $network
     * @return Post Post with optional link member object set, null if post doesn't exist
     */
    public function getPost($post_id, $network);

    /**
     * Get replies to a post
     * @param str $post_id
     * @param str $network
     * @param str $order_by Order of sorting posts
     * @param int $unit Defaults to km
     * @param bool $public Defaults to false
     * @param int $count Defaults to 350
     * @param int $page The page of results to return. Defaults to 1. Pages start
     * at 1, not 0.
     * @return array Posts with author object set, and optional link object set
     */
    public function getRepliesToPost($post_id, $network, $order_by = 'default', $unit = 'km', $is_public = false,
    $count = 350, $page = 1);

    /**
     * Get replies to a post in a given time frame.
     * @param str $post_id
     * @param str $network
     * @param str $order_by Order of sorting posts
     * @param int $unit Defaults to km
     * @param bool $public Defaults to false
     * @param int $count Defaults to 350
     * @param int $page The page of results to return. Defaults to 1. Pages start at 1, not 0.
     * @return array Posts with author object set, and optional link object set
     */
    public function getRepliesToPostInRange($post_id, $network, $from, $until, $order_by = 'default', $unit = 'km',
    $is_public = false, $count = 350, $page = 1);

    /**
     * Get replies Iterator to a post
     * @param str $post_id
     * @param str $network
     * @param str $order_by Order of sorting posts
     * @param int $unit Defaults to km
     * @param bool $public Defaults to false
     * @param int $count Defaults to 350
     * @param int $page The page of results to return. Defaults to 1. Pages start
     * at 1, not 0.
     * @return Iterator Posts with author object set, and optional link object set
     */
    public function getRepliesToPostIterator($post_id, $network, $order_by = 'default', $unit = 'km',
    $is_public = false, $count = 350, $page = 1);

    /**
     * Get retweets of post
     * @param str $post_id
     * @param str $network Defaults to 'twitter'
     * @param str $order_by Order of sorting posts
     * @param int $unit Defaults to km
     * @param bool $public Defaults to false
     * @param int $count The number of results to return. Defaults to null which
     * means return all retweets of this post.
     * @param int $page The page of results to return. Defaults to 1. The pages
     * start at 1, not 0.
     * @return array Retweets of post with optional link object set
     */
    public function getRetweetsOfPost($post_id, $network = 'twitter', $order_by = 'default', $unit = 'km',
    $is_public = false, $count = null, $page = 1);

    /**
     * Get all related posts (retweets and replies)
     * @param str $post_id
     * @param str $network Defaults to 'twitter'
     * @param bool $is_public Defaults to false
     * @param int $page The page of results to return. Defaults to 1.
     * @param bool $geo_encoded_only Defaults to true.
     * @param bool $include_original_post Whether or not to include the post you're querying. Defaults to true.
     * @return array Array of replies, retweets, and original post
     */
    public function getRelatedPostsArray($post_id, $network = 'twitter', $is_public = false, $count = 350, $page =1,
    $geo_encoded_only = true, $include_original_post = true);

    /**
     * Get all related posts (retweets and replies)
     * @param str $post_id
     * @param str $network Defaults to 'twitter'
     * @param bool $is_public Defaults to false
     * @param int $page The page of results to return. Defaults to 1.
     * @param bool $geo_encoded_only Defaults to true.
     * @param bool $include_original_post Whether or not to include the post you're querying. Defaults to true.
     * @return array Array of post objects
     */
    public function getRelatedPosts($post_id, $network = 'twitter', $is_public = false, $count = 350, $page = 1,
    $geo_encoded_only = true, $include_original_post = true);

    /**
     * Get posts that author has replied to (for question/answer exchanges)
     * @param str $author_id
     * @param int $count
     * @param str $network Defaults to 'twitter'
     * @param int $page Page number, defaults to 1
     * @param bool $public_only Defaults to true
     * @return array Question and answer values
     */
    public function getPostsAuthorHasRepliedTo($author_id, $count, $network = 'twitter', $page=1, $public_only=true);

    /**
     * Get all the back-and-forth posts between two users.
     * @param str $author_id
     * @param int $other_user_id
     * @param str $network Defaults to 'twitter'
     * @return array Back and forth posts
     */
    public function getExchangesBetweenUsers($author_id, $other_user_id, $network = 'twitter');

    /**
     * Check to see if Post is in database
     * @param str $post_id
     * @param str $network
     * @return bool true if post is in the database
     */
    public function isPostInDB($post_id, $network);
    /**
     * Check to see if reply is in database
     * This is an alias for isPostInDB
     * @param str $post_id
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
     *  $vals['is_protected']
     *  $vals['is_reply_by_friend']
     * </code>
     * Note: All fields which represent boolean values--fields whose names start with is_--should be an
     * int equal to either 1 or 0.
     *
     * @param array $vals see above
     * @return int|bool New insert id or false if not inserted
     */
    public function addPost($vals);

    /**
     * Insert post given an array of values, and related post entities
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
     * @param array $entities Indices may be set for 'urls', 'mentions', 'hashtags', and 'place'.
     * @param array $user_array user to be updated/created-- if set, array will be passed to User constructor.
     * @return int number of posts inserted
     */
    public function addPostAndAssociatedInfo(array $vals, $entities = null, $user_array = null);

    /**
     * Get all posts by an author given an author ID
     * @param str $author_id
     * @param str  $network
     * @param int $count
     * @param int $page
     * @param bool $include_replies If true, return posts with in_reply_to_post_id set
     * @param str $order_by The column to order the results by. Defaults to "pub_date".
     * @param str $direction The direction with which to order the results. Defaults
     * to "DESC".
     * @param bool $is_public Whether or not these results are going to be shown publicly.
     * @return array Posts by author with link set
     */
    public function getAllPosts($author_id, $network, $count, $page=1, $include_replies=true,
    $order_by = 'pub_date', $direction = 'DESC', $is_public = false);

    /**
     * Get posts from the friends of the given user_id; that is, their 'timeline' or 'newsfeed' data.
     * @param int $user_id
     * @param str  $network
     * @param int $count
     * @param int $page
     * @param bool $is_public
     * @param bool $iterator
     * @return array Posts or PostsIterator
     */
    public function getPostsByFriends($user_id, $network, $count = 15, $page = 1, $is_public = false,
    $iterator = false);

    /**
     * Iterator wrapper for getPostsByFriends
     * @param int $user_id
     * @param str  $network
     * @param int $count
     * @param bool $is_public
     * @return PostsIterator
     */
    public function getPostsByFriendsIterator($user_id, $network, $count, $is_public=false);

    /**
     * Get all posts by an author given an author ID that contain a question mark
     * @param str $author_id
     * @param str  $network
     * @param int $count
     * @param int $page
     * @param str $order_by The column to order the results by. Defaults to "pub_date".
     * @param str $direction The direction with which to order the results. Defaults
     * to "DESC".
     * @param bool $is_public Whether or not these results are going to be shown publicly. Defaults to false.
     * @return array Posts by author with a question mark in them with link set
     */
    public function getAllQuestionPosts($author_id, $network, $count, $page=1, $order_by = 'pub_date',
    $direction = 'DESC', $is_public = false);

    /**
     * Get all posts by a given user based on a given time frame.
     *
     * @param str $author_id The ID of the author to search for.
     * @param str $network The network of the user to search for.
     * @param mixed $from The date to search from. Can be a unix timestamp or a valid date string.
     * @param mixed $ntil The date to search until (not inclusive). Can be a unix timestamp or a valid date string.
     * @param str $order_by field name to order by. Defaults to pub_date.
     * @param str $direction either "DESC" or "ASC". Defaults to DESC.
     * @param bool $iterator Specify whether or not you want a post iterator returned. Default to
     * false.
     * @param int $page The page of results to return. Defaults to 1. Pages start
     * at 1, not 0.
     * @param bool $is_public Whether or not these results are going to be displayed publicly. Defaults to false.
     * @return array Posts with link object set
     */
    public function getPostsByUserInRange($author_id, $network, $from, $until, $order_by="pub_date", $direction="DESC",
    $iterator=false, $is_public = false);

    /**
     * Get all posts by an author given an author ID
     * @param str $author_id
     * @param str  $network
     * @param int $count
     * @param bool $include_replies If true, return posts with in_reply_to_post_id set
     * @param str $order_by The database column to order the results by.
     * @param str $direction The direction with which to order the results. Defaults
     * to "DESC".
     * @param bool $is_public Whether or not these results are going to be shown publicly. Defaults to false.
     * @return Iterator Posts Iterator
     */
    public function getAllPostsIterator($author_id, $network, $count, $include_replies=true,
    $order_by = 'pub_date', $direction = 'DESC', $is_public = false);

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
     * Get count of posts by author username
     * @param str $username
     * @param str $network
     * @return int total posts
     */
    public function getTotalPostsByUser($author_username, $network);

    /**
     * Get all the sources of an author's posts and their count
     * @param str $author_id
     * @param str $network
     * @return array "source"=>"web", "total"=>15
     */
    public function getStatusSources($author_id, $network);

    /**
     * Get a certain number of mentions of a username on a given network
     * @param str  $author_username
     * @param int $count
     * @param str $network defaults to "twitter"
     * @param int $page Page number, defaults to 1
     * @param bool $public Public mentions only, defaults to false
     * @param bool $include_rts Whether or not to include retweets. Defaults to true.
     * @param str $order_by The database column to order the results by.
     * @param str $direction The direction with which to order the results. Defaults
     * to "DESC".
     * @return Iterator PostIterator object
     */
    public function getAllMentionsIterator($author_username, $count, $network = "twitter", $page=1, $public=false,
    $include_rts = true, $order_by = 'pub_date', $direction = 'DESC');

    /**
     * Get a certain number of mentions of a username on a given network
     * @param str  $author_username
     * @param int $count
     * @param str $network defaults to "twitter"
     * @param int $page Page number, defaults to 1
     * @param bool $public Public mentions only, defaults to false
     * @param bool $include_rts Whether or not to include retweets. Defaults to true.
     * @param str $order_by The database column to order the results by.
     * @param str $direction The direction with which to order the results. Defaults
     * to "DESC".
     * @return array of Post objects with author and link set
     */
    public function getAllMentions($author_username, $count, $network = "twitter", $page=1, $public=false,
    $include_rts = true, $order_by = 'pub_date', $direction = 'DESC');

    /**
     * Get a certain number of mentions of a username on a given network and on a given time frame.
     * @param str  $author_username
     * @param int $count
     * @param str $network defaults to "twitter"
     * @param int $page Page number, defaults to 1
     * @param bool $public Public mentions only, defaults to false
     * @param bool $include_rts Whether or not to include retweets. Defaults to true.
     * @param str $order_by The database column to order the results by.
     * @param str $direction The direction with which to order the results. Defaults
     * to "DESC".
     * @return array of Post objects with author and link set
     */
    public function getAllMentionsInRange($author_username, $count, $network = "twitter", $from, $until, $page=1,
    $public=false, $include_rts = true, $order_by = 'pub_date', $direction = 'DESC');

    /**
     * Get all replies to a given user ID
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page Page number, defaults to 1
     * @param str $order_by The database column to order the results by.
     * @param str $direction The direction with which to order the results. Defaults
     * to "DESC".
     * @param bool $is_public Whether or not the result of the method call will be displayed publicly. Defaults to
     * false.
     * @return array Posts with author and link set
     */
    public function getAllReplies($user_id, $network, $count, $page = 1, $order_by = 'pub_date', $direction = 'DESC',
    $is_public = false);

    /**
     * Get all replies to a given user ID on a given time frame.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page Page number, defaults to 1
     * @param str $order_by The database column to order the results by.
     * @param str $direction The direction with which to order the results. Defaults
     * to "DESC".
     * @param bool $is_public Whether or not the result of the method call will be displayed publicly. Defaults to
     * false.
     * @return array Posts with author and link set
     */
    public function getAllRepliesInRange($user_id, $network, $count, $from, $until, $page = 1, $order_by = 'pub_date',
    $direction = 'DESC', $is_public = false);

    /**
     * Get posts by a user ordered by reply count desc
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page Page number, defaults to 1
     * @param bool $is_public Whether or not the results of this method call are going to be publicly displayed.
     * Defaults to false.
     * @return array Posts with link object set
     */
    public function getMostRepliedToPosts($user_id, $network, $count, $page=1, $is_public = false);

    /**
     * Get posts by a user ordered by favorite/like count.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page Page number, defaults to 1
     * @param bool $is_public Whether or not the results of this method call are going to be publicly displayed.
     * Defaults to false.
     * @return array Posts with link object set
     */
    public function getMostFavedPosts($user_id, $network, $count, $page=1, $is_public = false);

    /**
     * Get posts by a usre ordered by retweet count desc
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page Page number, defaults to 1
     * @param bool $is_public Whether or not the results of this method call are going to be publicly displayed.
     * Defaults to false.
     * @return array Posts with link object set
     */
    public function getMostRetweetedPosts($user_id, $network, $count, $page=1, $is_public = false);

    /**
     * Get orphan replies--mentions that are not associated with a particular post (not a reply or retweet).
     * @param str $username
     * @param int $count
     * @param str $network Default 'twitter'
     * @return array Post objects with author set
     */
    public function getOrphanReplies($username, $count, $network = 'twitter', $page = 1);

    /**
     * Get stray replied-to posts--posts that are listed in the in_repy_to_post_id field, but aren't in the posts table
     * @param str $author_id
     * @param str $network
     * @param int $page The page of results to return. Defaults to 1. Pages start
     * at 1, not 0.
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
     * @param str $post_id
     * @param str $network
     * @param int $is_geo_encoded 0 if Not Geoencoded, 1 if Successful,
     * 2 if ZERO_RESULTS, 3 if OVER_QUERY_LIMIT, 4 if REQUEST_DENIED, 5 if INVALID_REQUEST
     * @param str $location
     * @param str $geodata
     * @param int $distance
     * @return bool True if geo-location data for post added successfully
     */
    public function setGeoencodedPost($post_id, $network, $is_geo_encoded = 0, $location = NULL, $geodata = NULL,
    $distance = 0);

    /**
     * Update the favorite/like count cache for a post.
     * @param str $post_id
     * @param str $network
     * @param int $fav_like_count
     * @return int Number of updated rows
     */
    public function updateFavLikeCount($post_id, $network, $fav_like_count);

    /**
     * Update the reply count cache for a post.
     * @param str $post_id
     * @param str $network
     * @param int $reply_count
     * @return int Number of updated rows
     */
    public function updateReplyCount($post_id, $network, $reply_count);

    /**
     * Update the retweet count cache for a post.
     * @param str $post_id
     * @param str $network
     * @param int $retweet_count
     * @return int Number of updated rows
     */
    public function updateRetweetCount($post_id, $network, $retweet_count);

    /**
     * Update the text of a post.
     * @param str $post_id
     * @param str $network
     * @param str $post_text
     * @return int Number of updated rows
     */
    public function updatePostText($post_id, $network, $post_text);

    /**
     * Get most-replied-to posts by a username on a network in the last 7 days.
     * @param str $username The username of the user to fetch posts for.
     * @param str $network The network of the user to fetch posts for.
     * @param int $count The number of posts to fetch.
     * @param bool $is_public Whether or not the results from this method call are going to be publicly displayed.
     * Defaults to false.
     * @return array Posts
     */
    public function getMostRepliedToPostsInLastWeek($username, $network, $count, $is_public = false);

    /**
     * Get most-favorited posts published by a username on a network in the last 7 days.
     * @param str $username The username of the user to fetch posts for.
     * @param str $network The network of the user to fetch posts for.
     * @param int $count The number of posts to fetch.
     * @param bool $is_public Whether or not the results from this method call are going to be publicly displayed.
     * Defaults to false.
     * @return array Posts
     */
    public function getMostFavedPostsInLastWeek($username, $network, $count, $is_public = false);

    /**
     * Get specified number of most-retweeted posts by a username on a network
     * @param str $username The username of the user to fetch posts for.
     * @param str $network The network of the user to fetch posts for.
     * @param int $count The number of posts to fetch.
     * @param bool $is_public Whether or not the results from this method call are going to be publicly displayed.
     * Defaults to false.
     * @return array Posts
     */
    public function getMostRetweetedPostsInLastWeek($username, $network, $count, $is_public = false);

    /**
     * Calculate how much each client is used by a user on a specific network
     * @param str $author_id
     * @param string $network
     * @return array First element of the returned array is an array of all the clients the user used, ever.
     *               The second element is an array of the clients used for the last 25 posts.
     *               Both arrays are sorted by number of use, descending.
     */
    public function getClientsUsedByUserOnNetwork($author_id, $network);

    /**
     * Update author username by author ID for all posts in the post table for a defined network
     * @param str $author_user_id
     * @param str $network
     * @param str $author_username
     * @return int Count of posts updated
     */
    public function updateAuthorUsername($author_user_id, $network, $author_username);

    /**
     * Delete post by ID
     * @param int $id
     * @return boolean True if post deleted, False if not (ie: post did not exist)
     */
    public function deletePost($id);

    /**
     * Get most recent posts which have replies, likes, and/or retweets, and are not direct replies to another post.
     * @param str $author_user_id
     * @param str $network
     * @param int $count
     */
    public function getHotPosts($author_user_id, $network, $count);

    /**
     * Get the posts to the given user_id that are not replies, e.g. Facebook wall posts
     * @param $user_id
     * @param $network
     * @param $count
     * @param $page
     * @param $is_public
     * @param $iterator
     * @return array Posts
     */
    public function getPostsToUser($user_id, $network, $count = 15, $page = 1, $is_public = false,
    $iterator = false);

    /**
     * Get Iterator of Posts to user.
     * @param $user_id
     * @param $network
     * @param $count
     * @param $is_public
     * @return Iterator Posts with author object set, and optional link object set
     */
    public function getPostsToUserIterator($user_id, $network, $count, $is_public=false);

    /**
     * Get the average retweet count over the last X days
     * @param $username
     * @param $network
     * @param $last_x_days
     * @param $since Date to calculate from defaults to today
     * @return int Average retweet count over the last X days
     */
    public function getAverageRetweetCount($username, $network, $last_x_days, $since=null);

    /**
     * Get the average fave count over the last X days
     * @param $username
     * @param $network
     * @param $last_x_days
     * @param $since Date to calculate from defaults to today
     * @return int Average retweet count over the last X days
     */
    public function getAverageFaveCount($username, $network, $last_x_days, $since=null);

    /**
     * Get the average reply count over the last X days
     * @param $username
     * @param $network
     * @param $last_x_days
     * @param $since Date to calculate from defaults to today
     * @return int Average reply count over the last X days
     */
    public function getAverageReplyCount($username, $network, $last_x_days, $since=null);

    /**
     * Get posts from this day in every year except this one that aren't replies or reshares/retweets.
     * @param str $author_id
     * @param str $network
     * @param str $from_date If not specified, defaults to current date
     * @return array Post objects
     */
    public function getOnThisDayFlashbackPosts($author_id, $network, $from_date=null);

    /**
     * Get all checkins with place information and any links attached to the checkin.
     * @param $author_id
     * @param $network
     * @param $count
     * @param $page
     * @return array Post objects
     */
    public function getAllCheckins($author_id, $network, $count=15, $page=1);

    /**
     * Count the number of checkins to each place type.
     * @param str $author_id
     * @param str $network
     * @return array Place type, number of checkins to this place type pairs
     */
    public function countCheckinsToPlaceTypes($author_id, $network);

    /**
     * Generate a map image URL for the checkins in the last week
     * @param str $author_id
     * @param str $network
     * @return string that is a URL to a image of the checkins on a google map
     */
    public function getAllCheckinsInLastWeekAsGoogleMap($author_id, $network);

    /**
     * Get Javascript data table of post rate per hour all time versus last week.
     * @param $author_id
     * @param $network
     * @return str JavaScript data for Google chart
     */
    public function getPostsPerHourDataVis($author_id, $network);

    /**
     * Get the top 25 posts with the most replies, reshares, and likes of a given year.
     * @param str $author_user_id
     * @param str $network
     * @param str $year
     * @param int $count Defaults to 25
     * @return array of Post objects
     */
    public function getMostPopularPostsOfTheYear($author_user_id, $network, $year, $count=25);

    /** Check if user has any posts with retweets on or before since_date minus last_x_days
     * @param str $author_username
     * @param str $network
     * @param int $last_x_days
     * @param str $since Date in Y-m-d format
     * @return bool
     */
    public function doesUserHavePostsWithRetweetsSinceDate($author_username, $network, $last_x_days, $since=null);

    /** Check if user has any posts with faves on or before since_date minus last_x_days
     * @param str $author_username
     * @param str $network
     * @param int $last_x_days
     * @param str $since Date in Y-m-d format
     * @return bool
     */
    public function doesUserHavePostsWithFavesSinceDate($author_username, $network, $last_x_days, $since=null);

    /** Check if user has any posts with replies on or before since_date minus last_x_days
     * @param str $author_username
     * @param str $network
     * @param int $last_x_days
     * @param str $since Date in Y-m-d format
     * @return bool
     */
    public function doesUserHavePostsWithRepliesSinceDate($author_username, $network, $last_x_days, $since=null);

    /**
     * Get users who have have retweeted a specified post and have a higher follower count than a given threshold.
     * @param unknown_type $post_id
     * @param unknown_type $network
     * @param unknown_type $follower_count_threshold
     * @return array User
     */
    public function getRetweetsByAuthorsOverFollowerCount($post_id, $network, $follower_count_threshold);

    /**
     * Get the number of days since a user last replied to a specified recipient.
     * @param int $user_id
     * @param int $recipient_id
     * @param str $network
     * @return int
     */
    public function getDaysAgoSinceUserRepliedToRecipient($user_id, $recipient_id, $network);

    /**
     * Get the total number of posts by a user.
     * @param int $author_id
     * @param str $network
     * @param int $days_ago
     * @return int posts count
     */
    public function countAllPostsByUserSinceDaysAgo($author_id, $network, $days_ago=7);

    /**
     * Search a service users's posts.
     * @param arr $keywords
     * @param str $network
     * @param str $author_username
     * @param int $page_number defaults to 1
     * @param int $page_count defaults to 20
     * @return arr of Post objects
     */
    public function searchPostsByUser(array $keywords, $network, $author_username, $page_number=1, $page_count=20);

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
    $page=1, $is_public = false);

    /**
     * Delete Posts given a hashtag_id
     * @param str $hashtag_id
     * @return  int Total number of affected rows
     */
    public function deletePostsByHashtagId($hashtag_id);

    /**
     * Search a service users's tweet search.
     * @param arr $keywords
     * @param Hashtag $hashtag
     * @param str $network
     * @param int $page_number defaults to 1
     * @param int $page_count defaults to 20
     * @return arr of Post objects
     */
    public function searchPostsByHashtag($keywords, Hashtag $hashtag, $network, $page_number=1, $page_count=20);
    
    /**
     * Get today's post with the biggest sum of likes and comments.
     * @param int $author_id
     * @param str $network
     * @return Post object
     */
    public function getMostFavCommentPostsByUserId($author_id, $network);
}
