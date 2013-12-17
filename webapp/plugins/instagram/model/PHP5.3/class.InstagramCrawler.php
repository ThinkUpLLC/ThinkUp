<?php

class InstagramCrawler {
    /**
     * @var Instance
     */
    var $instance;
    /**
     * @var Logger
     */
    var $logger;
    /**
     * @var str
     */
    var $access_token;
    /**
     * @var int Maximum amount of time the crawler should spend fetching a profile or page in seconds
     */
    var $max_crawl_time;
    /**
     * @param Instance $instance
     * @return InstagramCrawler
     */
    public function __construct($instance, $access_token, $max_crawl_time) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        $this->access_token = $access_token;
        $this->max_crawl_time = $max_crawl_time;
    }
    /**
     * If user doesn't exist in the datastore, fetch details from instagram API and insert into the datastore.
     * If $reload_from_instagram is true, update existing user details in store with data from instagram API.
     * @param int $user_id instagram user ID
     * @param str $found_in Where the user was found
     * @param bool $reload_from_instagram Defaults to false; if true will query instagram API and update existing user
     * @return User
     */
    public function fetchUser($user_id, $found_in, $force_reload_from_instagram=false) {
        //assume all users except the instance user is a instagram profile, not a page
        $network = ($user_id == $this->instance->network_user_id)?$this->instance->network:'instagram';
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_object = null;
        if ($force_reload_from_instagram || !$user_dao->isUserInDB($user_id, $network)) {
            // Get owner user details and save them to DB
            try {

                $user_details = InstagramAPIaccessor::apiRequest('user', $user_id, $this->access_token);
                if (isset($user_details)) {
                    $user_details->network = $network;
                }

                $user = $this->parseUserDetails($user_details);
                if (isset($user)) {
                    $user_object = new User($user, $found_in);
                    $user_dao->updateUser($user_object);
                }

                if (isset($user_object)) {
                    $this->logger->logSuccess("Successfully fetched ".$user_id. " ".$network."'s details from instagram",
                    __METHOD__.','.__LINE__);
                } else {
                    $this->logger->logInfo("Error fetching ".$user_id." ". $network."'s details from instagram API, ".
                    "response was ".Utils::varDumpToString($user_details), __METHOD__.','.__LINE__);
                }
            } catch(Exception $e) {
                // Dont have permission to get this users details
            }
        }
        return $user_object;
    }
    /**
     * Convert an Instagram user object into a ThinkUp user object.
     * @param array $details
     * @return array $user_vals
     */
    private function parseUserDetails(Instagram\User $details) {
        if ($details->getUserName() != null && $details->getId() != null) {
            $user_vals = array();

            $user_vals["post_count"] = $details->getMediaCount();
            $user_vals["follower_count"] = $details->getFollowersCount();

            $user_vals["user_name"] = $details->getUserName();
            $user_vals["full_name"] = $details->getFullName();
            $user_vals["user_id"] = $details->getId();
            $user_vals["avatar"] = $details->getProfilePicture();
            $user_vals['url'] = $details->getWebsite()!=null?$details->getWebsite():'';
            $user_vals["location"] = '';
            $user_vals["description"] = $details->getBio()!=null?$details->getBio():'';
            $user_vals["is_protected"] = 0;
            $user_vals["joined"] = null;
            $user_vals["network"] = $details->network;
            //this will help us in getting correct range of posts
            $user_vals["updated_time"] = isset($details->updated_time)?$details->updated_time:0;
            return $user_vals;
        }
    }
    /**
     * Fetch and save the posts and replies for the crawler's instance. This function will loop back through the
     * user's or pages archive of posts.
     */
    public function fetchPostsAndReplies() {
        $id = $this->instance->network_user_id;
        $network = $this->instance->network;
        // fetch user's friends
        $this->storeFriends();

        $fetch_next_page = true;
        $current_page_number = 1;

        $posts = InstagramAPIAccessor::apiRequest('media', $id, $this->access_token, array('count' => 20));

        //Cap crawl time for very busy pages with thousands of likes/comments
        $fetch_stop_time = time() + $this->max_crawl_time;

        //Determine 'since', datetime of oldest post in datastore
        $post_dao = DAOFactory::getDAO('PostDAO');
        $since_post = $post_dao->getAllPosts($id, $network, 1, 1, true, 'pub_date', 'ASC');
        $since = isset($since_post[0])?$since_post[0]->pub_date:0;
        $since = strtotime($since) - (60 * 60 * 24); // last post minus one day, just to be safe
        ($since < 0)?$since=0:$since=$since;

        while ($fetch_next_page) {
            if ($posts->count() > 0) {
                $this->logger->logInfo(sizeof($stream->data)." instagram posts found on page ".$current_page_number,
                __METHOD__.','.__LINE__);

                $this->processPosts($posts, $network, $current_page_number);

                if ($posts->getNext() != null) {
                    $posts = InstagramAPIaccessor::apiRequest('media', $id, $this->access_token, array('count' => 20,
                    'max_id' => $posts->getNext()));
                    $current_page_number++;
                } else {
                    $fetch_next_page = false;
                }
            } else {
                $this->logger->logInfo("No instagram posts found for ID $id", __METHOD__.','.__LINE__);
                $fetch_next_page = false;
            }
            if (time() > $fetch_stop_time) {
                $fetch_next_page = false;
                $this->logger->logUserInfo("Stopping this service user's crawl because it has exceeded max time of ".
                ($this->max_crawl_time/60)." minute(s). ",__METHOD__.','.__LINE__);
            }
        }
    }
    /**
     * Convert a collection of profile posts into ThinkUp posts and users
     * @param Object $posts
     * @param str $source The network for the post, always 'instagram'
     * @param int Page number being processed
     */
    private function processPosts(Instagram\Collection\MediaCollection $posts, $network, $page_number) {
        $thinkup_posts = array();
        $total_added_posts = 0;

        $thinkup_users = array();
        $total_added_users = 0;

        $thinkup_likes = array();
        $total_added_likes = 0;

        //efficiency control vars
        $must_process_likes = true;
        $must_process_comments = true;
        $post_comments_added = 0;
        $post_likes_added = 0;
        $comments_difference = false;
        $likes_difference = false;

        $post_dao = DAOFactory::getDAO('PostDAO');

        foreach ($posts as $index=>$p) {
            $post_id = $p->getId();
            $this->logger->logInfo("Beginning to process ".$post_id.", post ".($index+1)." of ".count($posts->count()).
            " on page ".$page_number, __METHOD__.','.__LINE__);

            // stream can contain posts from multiple users.  get profile for this post
            $profile = $p->getUser();
            $is_protected = 0;
            //Get likes count
            $likes_count = $p->getLikesCount();
            $comments = $p->getComments();

            $post_in_storage = $post_dao->getPost($post_id, $network);

            //Figure out if we have to process likes and comments
            if (isset($post_in_storage)) {
                $this->logger->logInfo("Post ".$post_id. " already in storage", __METHOD__.','.__LINE__);
                if ($post_in_storage->favlike_count_cache >= $likes_count ) {
                    $must_process_likes = false;
                    $this->logger->logInfo("Already have ".$likes_count." like(s) for post ".$post_id.
                    "in storage; skipping like processing", __METHOD__.','.__LINE__);
                } else  {
                    $likes_difference = $likes_count - $post_in_storage->favlike_count_cache;
                    $this->logger->logInfo($likes_difference." new like(s) to process for post ".$post_id,
                    __METHOD__.','.__LINE__);
                }

                $commentsCount = $comments->count();
                if ($commentsCount > 0) {
                    if ($post_in_storage->reply_count_cache >= $commentsCount) {
                        $must_process_comments = false;
                        $this->logger->logInfo("Already have ".$commentsCount." comment(s) for post ".$post_id.
                        "; skipping comment processing", __METHOD__.','.__LINE__);
                    } else {
                        $comments_difference = $commentsCount - $post_in_storage->reply_count_cache;
                        $this->logger->logInfo($comments_difference." new comment(s) of ".$commentsCount.
                        " total to process for post ".$post_id, __METHOD__.','.__LINE__);
                    }
                }
            } else {
                $this->logger->logInfo("Post ".$post_id. " not in storage", __METHOD__.','.__LINE__);
            }
            // If we dont already have this photo
            if (!isset($post_in_storage)) {
                // Photos may be posted without a caption
                // Note that if you post a photo without a caption and then reply to it with the first comment instagram
                // treats this as your caption.
                if(strlen($p->getCaption()) >0 ) {
                    $text = $p->getCaption();
                } else {
                    $text = "";
                }

                $photo_to_process = array(
                  // Post details
                  "post_id"=>$post_id,
                  "author_username"=>$profile->getUserName(),
                  "author_fullname"=>$profile->getFullName(),
                  "author_avatar"=>$profile->getProfilePicture(),
                  "author_user_id"=>$profile->getId(),
                  "post_text"=> $text,
                  "pub_date"=>DateTime::createFromFormat('U', $p->getCreatedTime())->format('Y-m-d H:i'),
                  "favlike_count_cache"=>$likes_count,
                  "in_reply_to_user_id"=>'', // assume only one recipient
                  "in_reply_to_post_id"=>'',
                  "source"=>'',
                  'network'=>$network,
                  'is_protected'=>$is_protected,
                  'location'=>'',
                  // Photo details
                  'permalink'=>$p->getLink(),
                  'standard_resolution_url'=>$p->getStandardRes()->url,
                  'low_resolution_url'=>$p->getLowRes()->url,
                  'thumbnail_url'=>$p->getThumbnail()->url,
                  'filter'=>$p->getFilter(),
                );

                $new_photo_key = $this->storePhotoAndAuthor($photo_to_process, "Owner stream");

                if ($new_photo_key !== false ) {
                    $total_added_posts++;
                }
            } else { // post already exists in storage
                if ($must_process_likes) { //update its like count only
                    $post_dao->updateFavLikeCount($post_id, $network, $likes_count);
                    $this->logger->logInfo("Updated Like count for post ".$post_id . " to ". $likes_count,
                    __METHOD__.','.__LINE__);
                }
            }

            if ($must_process_comments) {
                if ($comments->count() > 0) {
                    $comments_captured = 0;
                    $post_comments = $comments;
                    if ($post_comments->count() > 0) {
                        foreach ($post_comments as $c) {
                            $comment_id = $c->getId();
                            //only add to queue if not already in storage
                            $comment_in_storage = $post_dao->getPost($comment_id, $network);
                            if (!isset($comment_in_storage)) {
                                $comment_author = $c->getUser();
                                $comment_to_process = array(
                                    "post_id"=>$comment_id,
                                    "author_username"=>$comment_author->getUserName(),
                                    "author_fullname"=>$comment_author->getFullName(),
                                    "author_avatar"=>$comment_author->getProfilePicture(),
                                    "author_user_id"=>$comment_author->getId(),
                                    "post_text"=>$c->getText(),
                                    "pub_date"=>DateTime::createFromFormat('U',
                                     $p->getCreatedTime())->format('Y-m-d H:i'),
                                    "in_reply_to_user_id"=>$profile->getId(),
                                    "in_reply_to_post_id"=>$post_id,
                                    "source"=>'', 'network'=>$network,
                                    'is_protected'=>$is_protected,
                                    'location'=>''
                                );
                                array_push($thinkup_posts, $comment_to_process);
                                $comments_captured = $comments_captured + 1;
                            }
                        }
                    }
                    $post_comments_added = $post_comments_added +
                    $this->storePostsAndAuthors($thinkup_posts, "Post stream comments");

                    //free up memory
                    $thinkup_posts = array();

                    if (is_int($comments_difference) && $post_comments_added >= $comments_difference) {
                        $must_process_comments = false;
                        $this->logger->logInfo("Caught up on post ".$post_id."'s balance of ".
                        $comments_difference." comments; stopping comment processing", __METHOD__.','.__LINE__);
                    }
                }
                if ($post_comments_added > 0) { //let user know
                    $this->logger->logUserSuccess("Added ".$post_comments_added." comment(s) for post ". $post_id,
                    __METHOD__.','.__LINE__);
                } else {
                    $this->logger->logInfo("Added ".$post_comments_added." comment(s) for post ". $post_id,
                    __METHOD__.','.__LINE__);
                }
                $total_added_posts = $total_added_posts + $post_comments_added;
            }

            //process "likes"
            if ($must_process_likes) {
                if ($likes_count > 0) {
                    $likes_captured = 0;
                    $post_likes = $p->getLikes();
                    $post_likes_count = $likes_count;
                    if ($post_likes_count > 0) {
                        foreach ($post_likes as $l) {
                            //Get users
                            $user_to_add = array(
                                "user_name"=>$l->getUserName(),
                                "full_name"=>$l->getFullName(),
                                "user_id"=>$l->getId(),
                                "avatar"=>$l->getProfilePicture(),
                                "description"=>'',
                                "url"=>'',
                                "is_protected"=>0,
                                "follower_count"=>0,
                                "post_count"=>0,
                                "joined"=>'',
                                "found_in"=>"Likes",
                                "network"=>'instagram'
                            ); //Users are always set to network=instagram
                            array_push($thinkup_users, $user_to_add);

                            $fav_to_add = array(
                                "favoriter_id"=>$l->getId(),
                                "network"=>$network,
                                "author_user_id"=>$profile->getId(),
                                "post_id"=>$post_id
                            );
                            array_push($thinkup_likes, $fav_to_add);
                            $likes_captured = $likes_captured + 1;
                        }
                    }

                    $total_added_users = $total_added_users + $this->calculateNumberOfUsersStored($thinkup_users,
                    "Likes");
                    $post_likes_added = $post_likes_added + $this->storeLikes($thinkup_likes);

                    //free up memory
                    $thinkup_users = array();
                    $thinkup_likes = array();

                    if (is_int($likes_difference) && $post_likes_added >= $likes_difference) {
                        $must_process_likes = false;
                        $this->logger->logInfo("Caught up on post ".$post_id."'s balance of ".
                        $likes_difference." likes; stopping like processing", __METHOD__.','.__LINE__);
                    }
                }
                $this->logger->logInfo("Added ".$post_likes_added." like(s) for post ".$post_id,
                __METHOD__.','.__LINE__);
                $total_added_likes = $total_added_likes + $post_likes_added;
            }
            //free up memory
            $thinkup_users = array();
            $thinkup_likes = array();
            //reset control vars for next post
            $must_process_likes = true;
            $must_process_comments = true;
            $post_comments_added = 0;
            $post_likes_added = 0;
            $comments_difference = false;
            $likes_difference = false;
        }

        $this->logger->logUserSuccess("On page ".$page_number.", captured ".$total_added_posts." post(s), ".
        $total_added_users." user(s) and ".$total_added_likes." like(s)", __METHOD__.','.__LINE__);
    }

    /**
     * Store an array of posts made on instagram in the database
     * @param  arr $posts           An array of posts to store
     * @param  str $posts_source Where the posts came from e.g. instagram
     * @return int number of posts added
     */
    private function storePostsAndAuthors($posts, $posts_source){
        $total_added_posts = 0;
        $added_post = 0;
        foreach ($posts as $post) {
            $added_post_key = $this->storePostAndAuthor($post, $posts_source);
            if ($added_post !== false) {
                $this->logger->logInfo("Added post ID ".$post["post_id"]." on ".$post["network"].
                " for ".$post["author_username"].":".substr($post["post_text"],0, 20)."...", __METHOD__.','.__LINE__);
                $total_added_posts = $total_added_posts ++;
            } else  {
                $this->logger->logInfo("Didn't add post ".$post["post_id"]." on ".$post["network"].
                " for ".$post["author_username"].":".substr($post["post_text"],0, 20)."...", __METHOD__.','.__LINE__);
            }
            $added_post = 0;
        }
        return $total_added_posts;
    }
    /**
     * Store a post in the posts table and add the author to the users table
     * @param  arr $post  An array of posts and associated users
     * @param  str $post_source Where the posts were made e.g. instagram
     * @return int id of the row the post
     */
    private function storePostAndAuthor($post, $post_source){
        $post_dao = DAOFactory::getDAO('PostDAO');
        try {
            if (isset($post['author_user_id'])) {
                $user_object = $this->fetchUser($post['author_user_id'], $post_source);
                if (isset($user_object)) {
                    $post["author_username"] = $user_object->username;
                    $post["author_fullname"] = $user_object->full_name;
                    $post["author_avatar"] = $user_object->avatar;
                    $post["location"] = $user_object->location;
                }
            }
        } catch(Exception $e) {
            // User is private and we can't store their details.
        }
        $added_post_key = $post_dao->addPost($post);
        return $added_post_key;
    }
    /**
     * Store a photo into the photos table and add the author to the users table
     * @param  arr $photo        An array of photos to add to the database
     * @param  str $photo_source Where the post came from e.g. instagram
     * @return int id of the row of the photo
     */
    private function storePhotoAndAuthor($photo, $photo_source){
        $photo_dao = DAOFactory::getDAO('PhotoDAO');
        if (isset($photo['author_user_id'])) {
            $user_object = $this->fetchUser($photo['author_user_id'], $photo_source);
            if (isset($user_object)) {
                $photo["author_username"] = $user_object->username;
                $photo["author_fullname"] = $user_object->full_name;
                $photo["author_avatar"] = $user_object->avatar;
                $photo["location"] = $user_object->location;
            }
        }
        $added_photo_key = $photo_dao->addPhoto($photo);
        return $added_photo_key;
    }
    /**
     * Counts how many users were added to the database
     * @param  arr $users An array of users for which to check if they were stored
     * @param  str $users_source Where the users were found
     * @return int Number of users from the input array that are in the users table
     */
    private function calculateNumberOfUsersStored($users, $users_source) {
        $added_users = 0;
        if (count($users) > 0) {
            foreach ($users as $user) {
                $user_object = $this->fetchUser($user['user_id'], $users_source);
                if (isset($user_object)) {
                    $added_users = $added_users + 1;
                }
            }
        }
        return $added_users;
    }
    /**
     * Stores information about users who have liked posts in the favorites table
     * @param  arr $likes An array of posts which have been liked and details about the liker
     * @return int number of likes which were added
     */
    private function storeLikes($likes) {
        $added_likes = 0;
        if (count($likes) > 0) {
            $fav_dao = DAOFactory::getDAO('FavoritePostDAO');
            foreach ($likes as $like) {
                $added_likes = $added_likes + $fav_dao->addFavorite($like['favoriter_id'], $like);
            }
        }
        return $added_likes;
    }
    /**
     * Retrives all of a users friends from the Instagram API and stores them in the database
     * @return null
     */
    private function storeFriends() {
        if ($this->instance->network != 'instagram') {
            return;
        }
        //Retrieve friends via the instagram API
        $user_id = $this->instance->network_user_id;
        $access_token = $this->access_token;
        $network = ($user_id == $this->instance->network_user_id)?$this->instance->network:'instagram';
        $friends = InstagramAPIAccessor::apiRequest('friends', $user_id, $access_token);

        if (isset($friends)) {
            //store relationships in follows table
            $follows_dao = DAOFactory::getDAO('FollowDAO');
            $count_dao = DAOFactory::getDAO('CountHistoryDAO');
            $user_dao = DAOFactory::getDAO('UserDAO');

            foreach ($friends as $friend) {
                try {
                    $follower_id = $friend->getId();
                    if ($follows_dao->followExists($user_id, $follower_id, $network)) {
                        // follow relationship already exists
                        $follows_dao->update($user_id, $follower_id, $network);
                    } else {
                        // follow relationship does not exist yet
                        $follows_dao->insert($user_id, $follower_id, $network);
                    }

                    $follower_details = $friend;
                    if (isset($follower_details)) {
                        $follower_details->network = $network;
                    }

                    $follower = $this->parseUserDetails($follower_details);
                    $follower_object = new User($follower);
                    if (isset($follower_object)) {
                        $user_dao->updateUser($follower_object);
                    }
                } catch(Exception $e) {
                    // Don't have permission to get this users details
                }
            }
            //totals in follower_count table
            $count_dao->insert($user_id, $network, $friends->count(), null, 'followers');
        } else {
            throw new Instagram\Core\ApiAuthException('Error retrieving friends');
        }
    }
}
