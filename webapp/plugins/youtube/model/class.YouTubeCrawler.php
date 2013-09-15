<?php
/**
 *
 * webapp/plugins/youtube/model/class.YouTubeCrawler.php
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
 * YouTube Crawler
 *
 * Retrives data from YouTube
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot][com]>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 */

class YouTubeCrawler {

    /**
     *
     * @var Instance
     */
    var $instance;
    /**
     *
     * @var Logger
     */
    var $logger;
    /**
     * @var str
     */
    var $access_token;
    /**
     *
     * @var YouTubeAPIAccessor
     */
    var $youtube_api_accessor;
    /**
     *
     * @var YouTubeAPIV2Accessor
     */
    var $youtube_api_v2_accessor;
    /**
     *
     * @var YouTubeAnalyticsAPIAccessor
     */
    var $youtube_analytics_api_accessor;
    /**
     *
     * @var GooglePlusAPIAccessor
     */
    var $google_plus_api_accessor;
    /**
     * @var int Maximum amount of time the crawler should spend retriving videos in seconds
     */
    var $max_crawl_time;
    /**
     * @var str Optional YouTube Developer key to allow more requests per second to the YouTube V2 API
     */
    var $developer_key;
    /**
     * @var str Optional limit on how many comments we collect per video
     */
    var $maximum_comments;


    public function __construct($instance, $access_token, $max_time, $developer_key, $max_comments) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        $this->access_token = $access_token;
        $this->youtube_api_accessor = new YouTubeAPIAccessor();
        $this->youtube_api_v2_accessor = new YouTubeAPIV2Accessor();
        $this->youtube_analytics_api_accessor = new YouTubeAnalyticsAPIAccessor();
        $this->google_plus_api_accessor = new GooglePlusAPIAccessor();
        $this->max_crawl_time = $max_time;
        $this->developer_key = $developer_key;
        $this->maximum_comments = $max_comments;
    }

   /**
     * Retrieve OAuth and refresh tokens from the Google API as per:
     * https://developers.google.com/youtube/v3/guides/authentication
     * @param str $client_id
     * @param str $client_secret
     * @param str $code_refresh_token Either the refresh token or Google-provided code
     * @param str $grant_type Either 'refresh_token' or 'authorization_code'
     * @param str $redirect_uri
     * @return Object with access_token and refresh_token member vars
     */
    public function getOAuthTokens($client_id, $client_secret, $code_refresh_token, $grant_type, $redirect_uri=null) {
        //prep access token request URL
        $access_token_request_url = "https://accounts.google.com/o/oauth2/token";
        $fields = array(
            'client_id'=>urlencode($client_id),
            'client_secret'=>urlencode($client_secret),
            'grant_type'=>urlencode($grant_type)
        );
        if ($grant_type=='refresh_token') {
            $fields['refresh_token'] = $code_refresh_token;
        } elseif ($grant_type=='authorization_code') {
            $fields['code'] = $code_refresh_token;
        }
        if (isset($redirect_uri)) {
            $fields['redirect_uri'] = $redirect_uri;
        }
        //get tokens
        $tokens =  $this->youtube_api_accessor->rawPostApiRequest($access_token_request_url, $fields, true);
        return $tokens;
    }

    public function initializeInstanceUser($client_id, $client_secret, $access_token, $refresh_token, $owner_id) {
        $network = 'youtube';
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_object = null;
        // Get owner user details and save them to DB
        $fields = array('fields'=>'displayName,id,image,tagline,verified');
        $user_details =  $this->google_plus_api_accessor->apiRequest('people/me', $this->access_token, $fields);

        if (isset($user_details->error->code) && $user_details->error->code == '401') {
            //Token has expired, fetch and save a new one
            $tokens = self::getOAuthTokens($client_id, $client_secret, $refresh_token, 'refresh_token');
            if (isset($tokens->error) || !isset($tokens->access_token)) {
                $error_msg = "Oops! Something went wrong while obtaining OAuth tokens.<br>Google says \"";
                if (isset($tokens->error)) {
                    $error_msg .= $tokens->error;
                } else {
                    $error_msg .= Utils::varDumpToString($tokens);
                }
                $error_msg .=".\" Please double-check your settings and try again.";
                $this->logger->logError($error_msg, __METHOD__.','.__LINE__);
            } else {
                $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                $owner_instance_dao->updateTokens($owner_id, $this->instance->id, $access_token, $refresh_token);
                $this->access_token  = $tokens->access_token;
                //try again
                $user_details =  $this->google_plus_api_accessor->apiRequest('people/me', $this->access_token, $fields);
            }
        }

        if (isset($user_details)) {
            $user_details->network = $network;
            $user = $this->parseUserDetails($user_details);
        }
        if (isset($user)) {
            $user_object = new User($user, 'Owner initialization');
            $user_dao->updateUser($user_object);
        }
        if (isset($user_object)) {
            $this->logger->logSuccess("Successfully fetched ".$user_object->username. " ".$user_object->network.
            "'s details from Google+", __METHOD__.','.__LINE__);
        } else {
            $this->logger->logInfo("Error fetching user details from the Google+ API, response was ".
            Utils::varDumpToString($user_details), __METHOD__.','.__LINE__);
        }
        return $user_object;
    }

    /**
     * If user doesn't exist in the datastore, fetch details from Google+ API and insert into the datastore.
     * If $reload_from_googleplus is true, update existing user details in store with data from Google+ API.
     * @param int $user_id Google+ user ID
     * @param str $found_in Where the user was found
     * @param bool $reload_from_googleplus Defaults to false; if true will query Google+ API and update existing user
     * @return User
     */
    public function fetchUser($user_id, $found_in, $force_reload_from_googleplus=false) {
        $network = 'youtube';
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_object = null;
        if ($force_reload_from_googleplus || !$user_dao->isUserInDB($user_id, $network)) {
            // Get owner user details and save them to DB
            $fields = array('fields'=>'displayName,id,image,tagline,verified');
            $user_details =  $this->google_plus_api_accessor->apiRequest('people/'.$user_id, $this->access_token,
            $fields);
            $user_details->network = $network;

            $user = $this->parseUserDetails($user_details);

            if (isset($user)) {
                $user_object = new User($user, $found_in);
                $user_dao->updateUser($user_object);
            }
            if (isset($user_object)) {
                $this->logger->logSuccess("Successfully fetched ".$user_id. " ".$network."'s details from Google+",
                __METHOD__.','.__LINE__);
            } else {
                $this->logger->logInfo("Error fetching ".$user_id." ". $network."'s details from the Google+ API, ".
                "response was ".Utils::varDumpToString($user_details), __METHOD__.','.__LINE__);
            }
        }
        return $user_object;
    }

    /**
     * For fetching details about a user who does not have a Gooogle Plus ID
     * If user doesn't exist in the datastore, fetch details from YouTube API and insert into the datastore.
     * If $reload_from_youtube is true, update existing user details in store with data from YouTube API.
     * @param int $user_id YouTube user ID
     * @param str $found_in Where the user was found
     * @param bool $reload_from_youtube Defaults to false; if true will query YouTube API and update existing user
     * @return User
     */
    public function fetchUserFromYouTube($user_id, $found_in, $force_reload_from_youtube=false) {
        $network = 'youtube';
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_object = null;
        if ($force_reload_from_youtube || !$user_dao->isUserInDB($user_id, $network)) {
            // Get owner user details and save them to DB
            $fields = array('alt'=>'json');
            if(isset($this->developer_key)) {$fields['key'] = $this->developer_key;}

            $user_details =  $this->youtube_api_v2_accessor->apiRequest('users/'.
            $user_id_string[sizeof($user_id_string)-1], $fields);

            $user = $this->parseYouTubeUserDetails($user_details->entry);

            if (isset($user)) {
                $user_object = new User($user, $found_in);
                $user_dao->updateUser($user_object);
            }
            if (isset($user_object)) {
                $this->logger->logSuccess("Successfully fetched ".$user_id. " ".$network."'s details from YouTube",
                __METHOD__.','.__LINE__);
            } else {
                $this->logger->logInfo("Error fetching ".$user_id." ". $network."'s details from the YouTube API, ".
                "response was ".Utils::varDumpToString($user_details), __METHOD__.','.__LINE__);
            }
        }
        return $user_object;
    }

    /**
     *  Collects and stores information about the users videos from the YouTube APIs
     *  Currently collects and stores:
     *   - Basic video information such as title, author, description and location the video was shot in (if available)
     *  - Replies to the video
     *      -- This uses the YouTube V2 API due to the V3 API currently not supporting replies
     *   - All time counts for likes, dislikes, views, average view duration, average view percentage, favorites added,
     *   favorites removed, shares, subscribers gained and subscribers lost
     *     -- The totals for these are stored in the videos table, a history of these totals is stored in the
     *     count_history table under a type of [metric]_all_time and date of todays date
     *    -- A record of these metrics for indivdual days is also saved in the count_history table under a type of
     *    [metric] and date of the day the metric represents usually two days ago due to a delay in the availability
     *      of data from the Analytics API
     * @return null
     */
    public function fetchInstanceUserVideos() {
        $video_dao = DAOFactory::getDAO('VideoDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');
        $post_dao = DAOFactory::getDAO('PostDAO');
        $count_history_dao = DAOFactory::getDAO('CountHistoryDAO');
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        // Get the users upload playlist ID
        $fields_for_ids = array('part' => 'contentDetails,statistics', 'mine'=>'true');
        $various_ids = $this->youtube_api_accessor->apiRequest('channels', $this->access_token, $fields_for_ids);
        $upload_id = $various_ids->items[0]->contentDetails->relatedPlaylists->uploads;
        // Also get their channel ID as we'll need it later on
        $channel_id = $various_ids->items[0]->id;
        // There are some required attributes about the author that YouTube doesn't return for the videos so we need
        // to query the database for them
        $author_details = $user_dao->getDetails($this->instance->network_user_id, 'youtube');
        $user_id = $this->instance->network_user_id;
        // Update the users subscriber count
        $subscriber_count = $various_ids->items[0]->statistics->subscriberCount;
        $author_details->follower_count = $subscriber_count;
        $user_dao->updateUser($author_details);
        $count_history_dao->insert($user_id, 'youtube', $subscriber_count, null, 'subscriber_count');
        // Calculate the time at which we should stop fetching videos
        $end_time = time() + $this->max_crawl_time;
        // Keep track of if we finished the crawl early due to timing out
        $had_to_finish_early=false;
        // Check if we already loaded all the old posts for this user
        $archive_loaded = $instance->is_archive_loaded_posts;
        // If the archive isn't loaded yet keep track of how many times we've tried to load it
        if(!$archive_loaded) {
            $attempts = $count_history_dao->getLatestCountByNetworkUserIDAndType($user_id, 'youtube',
            'youtube_archive_attempts');
            if($attempts == null) {
                // If this is the first crawler run
                $attempts['count'] = 0;
            }
            $attempts['count']++;
            $count_history_dao->insert($user_id, 'youtube', $attempts['count'], null, 'youtube_archive_attempts', null);
        }

        // Now page through their videos collecting the data
        $videos_fields = array('part' => 'snippet', 'maxResults' => '25', 'playlistId' => $upload_id,
        'pageToken' => null);

        // We may get multiple pages
        do {
            // This is a page of IDs of videos the user has uploaded
            $user_videos = $this->youtube_api_accessor->apiRequest('playlistItems',$this->access_token, $videos_fields);
            // For each video store the relevant details about it
            foreach($user_videos->items as $video) {
                // If we've hit the max crawl time stop
                if(time() >= $end_time) {
                    $this->logger->logUserInfo("Stopping this service users crawl because it has exceeded max time of ".
                    ($this->max_crawl_time/60)." minute(s). ",__METHOD__.','.__LINE__);
                    $had_to_finish_early = true;
                    break 2;
                }

                $video_id = $video->snippet->resourceId->videoId;

                // Get the title, description, likes, dislikes, views, and details about where
                // the video was taken from the data API
                $video_fields = array('id' => $video_id, 'part' => 'statistics,id,snippet,recordingDetails,status');
                $video_details = $this->youtube_api_accessor->apiRequest('videos', $this->access_token, $video_fields);
                $item = $video_details->items[0];
                // Check we haven't used up our quota
                if(isset($video_details->error)){
                    $this->logger->logError('Error querying YouTube Data API V3 ', __METHOD__.','.__LINE__);
                    break;
                }

                $video_attributes['post_text'] = $item->snippet->title;
                $video_attributes['description'] = $item->snippet->description;
                $video_attributes['likes'] = $item->statistics->likeCount;
                $video_attributes['dislikes'] = $item->statistics->dislikeCount;
                $video_attributes['views'] = $item->statistics->viewCount;

                // Keep track of these all time counts
                $count_history_dao->insert($user_id, 'youtube', $video_attributes['likes'], $video_id,
                'likes_all_time');
                $count_history_dao->insert($user_id, 'youtube', $video_attributes['dislikes'], $video_id,
                'dislikes_all_time');
                $count_history_dao->insert($user_id, 'youtube', $video_attributes['views'], $video_id,
                'views_all_time');

                $video_attributes['pub_date'] = $item->snippet->publishedAt;
                $video_attributes['post_id'] = $item->id;
                $video_attributes['location'] = $item->recordingDetails->locationDescription;
                $video_attributes['place'] = $item->recordingDetails->locationDescription;
                if(isset($item->recordingDetails->latitude)) {
                    $video_attributes['geo'] = $item->recordingDetails->latitude . "," .
                    $item->recordingDetails->longitude;
                }
                $video_attributes['is_protected'] = self::determinePrivacyStatus($item->status->privacyStatus);

                $today = date('Y-m-d');
                $upload_date = substr($item->snippet->publishedAt, 0,10);

                // Get the favourites added, favourites removed, shares, subscribers gained, subscribers lost
                // estimated minuites watched, average view duration, average view percentage
                $analytics_fields = array('ids' => 'channel=='.$channel_id, 'start-date' => $upload_date,
                'end-date' => $today,
                'metrics'=>'favoritesAdded,favoritesRemoved,shares,subscribersGained,subscribersLost,'.
                'estimatedMinutesWatched,averageViewDuration,averageViewPercentage,views,likes,dislikes',
                'filters' => 'video=='.$video_id);

                $video_analytics_details = $this->youtube_analytics_api_accessor->apiRequest('reports',
                $this->access_token, $analytics_fields);
                // Check we haven't used up our quota
                if(isset($video_analytics_details->error)){
                    $this->logger->logError('Error querying YouTube Analytics API', __METHOD__.','.__LINE__);
                    break;
                }
                $analytics_item = $video_analytics_details->rows[0];
                // If the video is new we may not get any of these values back, but they can't be null
                if(isset($analytics_item)) {
                    $video_attributes['favorites_added'] = $analytics_item[0];
                    $video_attributes['favorites_removed'] = $analytics_item[1];
                    $video_attributes['shares'] = $analytics_item[2];
                    $video_attributes['subscribers_gained'] = $analytics_item[3];
                    $video_attributes['subscribers_lost'] = $analytics_item[4];
                    $video_attributes['minutes_watched'] = $analytics_item[5];
                    $video_attributes['average_view_duration'] = $analytics_item[6];
                    $video_attributes['average_view_percentage'] = $analytics_item[7];

                    // Keep track of these all time counts
                    $count_history_dao->insert($user_id, 'youtube', $analytics_item[0], $video_id,
                    'favorites_added_all_time');
                    $count_history_dao->insert($user_id, 'youtube', $analytics_item[1], $video_id,
                    'favorites_removed_all_time');
                    $count_history_dao->insert($user_id, 'youtube', $analytics_item[2], $video_id,
                    'shares_all_time');
                    $count_history_dao->insert($user_id, 'youtube', $analytics_item[3], $video_id,
                    'subscribers_gained_all_time');
                    $count_history_dao->insert($user_id, 'youtube', $analytics_item[4], $video_id,
                    'subscribers_lost_all_time');
                    $count_history_dao->insert($user_id, 'youtube', $analytics_item[5], $video_id,
                    'minutes_watched_all_time');
                    $count_history_dao->insert($user_id, 'youtube', $analytics_item[6], $video_id,
                    'average_view_duration_all_time');
                    $count_history_dao->insert($user_id, 'youtube', $analytics_item[7], $video_id,
                    'average_view_percentage_all_time');
                } else { // So set them the 0
                    $video_attributes['favorites_added'] = 0;
                    $video_attributes['favorites_removed'] = 0;
                    $video_attributes['shares'] = 0;
                    $video_attributes['subscribers_gained'] = 0;
                    $video_attributes['subscribers_lost'] = 0;
                    $video_attributes['minutes_watched'] = 0;
                    $video_attributes['average_view_duration'] = 0;
                    $video_attributes['average_view_percentage'] = 0;
                }

                $video_attributes['author_user_id'] = $this->instance->network_user_id;
                $video_attributes['author_username'] = $this->instance->network_username;
                $video_attributes['author_fullname'] = $author_details->full_name;
                $video_attributes['author_avatar'] = $author_details->avatar;
                $video_attributes['source'] = '';
                $video_attributes['network'] = 'youtube';

                $video_dao->addVideo($video_attributes);

                // Now collect per day count data for 2 days ago (testing has shown analytics data is delayed by 2 days)
                $two_days_ago = date('Y-m-d', strtotime("-2 day", strtotime($today)));
                $analytics_fields['start-date'] = $two_days_ago;
                $analytics_fields['end-date'] = $two_days_ago;
                $analytics_today_details = $this->youtube_analytics_api_accessor->apiRequest('reports',
                $this->access_token, $analytics_fields);
                // Check we haven't used up our quota
                if(isset($analytics_today_details->error)){
                    $this->logger->logError('Error querying YouTube Analytics API', __METHOD__.','.__LINE__);
                    break;
                }
                $todays_analytics = $analytics_today_details->rows[0];
                // Check we got data and if not skip this part
                if(isset($todays_analytics)) {
                    $count_history_dao->insert($user_id, 'youtube', $todays_analytics[0], $video_id,
                    'favorites_added', $two_days_ago);
                    $count_history_dao->insert($user_id, 'youtube', $todays_analytics[1], $video_id,
                    'favorites_removed', $two_days_ago );
                    $count_history_dao->insert($user_id, 'youtube', $todays_analytics[2], $video_id,
                    'shares', $two_days_ago);
                    $count_history_dao->insert($user_id, 'youtube', $todays_analytics[3], $video_id,
                    'subscribers_gained', $two_days_ago);
                    $count_history_dao->insert($user_id, 'youtube', $todays_analytics[4], $video_id,
                    'subscribers_lost', $two_days_ago);
                    $count_history_dao->insert($user_id, 'youtube', $todays_analytics[5], $video_id,
                    'minutes_watched', $two_days_ago);
                    $count_history_dao->insert($user_id, 'youtube', $todays_analytics[6], $video_id,
                    'average_view_duration', $two_days_ago);
                    $count_history_dao->insert($user_id, 'youtube', $todays_analytics[7], $video_id,
                    'average_view_percentage', $two_days_ago);
                    $count_history_dao->insert($user_id, 'youtube', $todays_analytics[8], $video_id,
                    'views', $two_days_ago);
                    $count_history_dao->insert($user_id, 'youtube', $todays_analytics[9], $video_id,
                    'likes', $two_days_ago);
                    $count_history_dao->insert($user_id, 'youtube', $todays_analytics[10], $video_id,
                    'dislikes', $two_days_ago);
                }
                // Check to see how many comments we already have for this video and if there are no new ones skip
                // comment collection as it takes a long time.
                $video_in_db = $video_dao->getVideoByID($video_id, 'youtube');
                $comments_in_db = $video_in_db->reply_count_cache;
                if(!isset($comments_in_db)) {$comments_in_db = 0;}
                $api_comments = $item->statistics->commentCount;
                $comments_collected = 0;

                // if this video has any new comments capture those
                if($api_comments > 0 && $api_comments > $comments_in_db) {

                    // Request the first page of comments for this video
                    $comments_fields = array('alt' => 'json');
                    if(isset($this->developer_key)) {$comments_fields['key'] = $this->developer_key;}

                    $comments = $this->youtube_api_v2_accessor->apiRequest('videos/'.$video_id.'/comments',
                    $comments_fields);

                    // Check we haven't used up our quota
                    if(isset($comments->errors)) {
                        $this->logger->logError('Error querying YouTube Data API V2 ', __METHOD__.','.__LINE__);
                        break;
                    }

                    do{

                        // Iterate through each comment and store the details
                        foreach( $comments->feed->entry as $comment) {
                            // We may have only needed to collect a few new comments so abort if we have everything
                            if($api_comments == $comments_in_db) {
                                break 2;
                            }
                            // If the user has specified a limit on the number of comments per video to collect each
                            // crawl check we haven't exceeded it
                            if(isset($this->maximum_comments) && $comments_collected >= $this->maximum_comments) {
                                $this->logger->logUserInfo("Stopping collection of comments for video due to reaching ".
                                "limit of ".$this->maximum_comments." comments.",__METHOD__.','.__LINE__);
                                break 2;
                            }
                            // We may spend a long time collecting comments so also check here if we've exceed the max
                            // time specified by the user
                            if(time() >= $end_time) {
                                $this->logger->logUserInfo("Stopping this service users crawl because it has exceeded ".
                                "max time of ".($this->max_crawl_time/60)." minute(s). ",__METHOD__.','.__LINE__);
                                $had_to_finish_early = true;
                                break 4;
                            }

                            // The id is returned in the XML as part of a long URL, we only want the last part of that
                            // URL
                            $id_string = explode('/', $comment->id->{'$t'});
                            // This will be the last element of id_string
                            $comment_store['post_id'] = $id_string[sizeof($id_string)-1];

                            // The post text is the comment they made
                            // Remove byte order markers from the comment text from:
                            // http://stackoverflow.com/questions/3255993/how-do-i-remove-i-from-the-beginning
                            // -of-a-file#comment9330944_3256183
                            $comment_store['post_text'] = preg_replace('/\x{EF}\x{BB}\x{BF}/','',
                            $comment->content->{'$t'});

                            // The author username is the users G+ displayname which we need to query for
                            // To get the G+ ID of this commentor we need to vist their youtube profile page, the ID
                            // needed to get to this users page is the last element of the author URI
                            $user_id_string = explode('/', $comment->author[0]->uri->{'$t'});
                            $name =  $this->youtube_api_v2_accessor->apiRequest('users/'.
                            $user_id_string[sizeof($user_id_string)-1], $comments_fields);
                            $gplus_id = $name->entry->{'yt$googlePlusUserId'}->{'$t'};

                            // // Now we have their G+ ID we can get their details from the G+ API
                            $gplus_fields = array('fields'=>'displayName,id,image,tagline,verified');
                            $user_details = $this->google_plus_api_accessor->apiRequest('people/'.$gplus_id,
                            $this->access_token, $gplus_fields);

                            // Sometimes G+ says the ID is invalid or the user doesn't have a G+ ID
                            if($user_details->error->code == '404' || $gplus_id == '') {

                                // Use V2 of the YouTube api to get their details
                                $comment_store['author_username'] = $name->entry->{'yt$username'}->{'$t'};
                                $comment_store['author_fullname'] = $name->entry->author[0]->name->{'$t'};
                                $comment_store["author_avatar"] = $name->entry->{'media$thumbnail'}->url;
                                // In this case the user id is their YouTube user ID
                                $comment_store['author_user_id'] = $user_id_string[sizeof($user_id_string)-1];

                                self::fetchUserFromYouTube($user_id_string[sizeof($user_id_string)-1],
                                'youtube_crawler');

                                // If we still didn't get these details we can't store this comment
                                if($comment_store['author_username'] == null ||
                                   $comment_store['author_fullname'] == null ||
                                   $comment_store["author_avatar"] == null ) {
                                    continue;
                                }

                            } elseif (isset($user_details->error)) {
                                //Check we haven't exceed the G+ API quota
                                $this->logger->logError('Error querying Google Plus API ', __METHOD__.','.__LINE__);
                                break;
                            } else {
                                $comment_store['author_username'] = $user_details->displayName;
                                $comment_store['author_fullname'] = $user_details->displayName;
                                $comment_store["author_avatar"] = $user_details->image->url;
                                // The author user id is their G+ ID
                                $comment_store['author_user_id'] = $gplus_id;

                                // Make sure we have this commentor in the database
                                self::fetchUser($gplus_id, 'youtube crawler');
                            }

                            // The date they posted the comment
                            $comment_store['pub_date'] = substr($comment->published->{'$t'}, 0,10) . " " .
                            substr($comment->published->{'$t'}, 11,8);
                            // Source of the comment
                            $comment_store['source'] = "";
                            // Comments can not be private
                            $comment_store['is_protected'] = false;
                            // Set the network to youtube
                            $comment_store['network'] = 'youtube';
                            // The ID of the author of the video
                            $comment_store['in_reply_to_user_id'] = $this->instance->network_user_id;
                            // The ID of the video this comment is a reply to
                            $comment_store['in_reply_to_post_id'] = $video_id;

                            $insert_id = $post_dao->addPost($comment_store);
                            // If the insert id is null and were not going back to collect the whole archive
                            // we've already captured comments from this point so move on
                            if($insert_id == null && $archive_loaded) {
                                break 2;
                            }
                            $comments_in_db++;
                            $comments_collected++;
                        }

                        $test = self::determineIfMoreCommentsExist($comments, $video_id);

                        // If there is another page of comments make a request for them
                        if($test['next']){
                            $comments = $this->youtube_api_v2_accessor->basicApiRequest($test['url']);
                            // Check we haven't used up our quota
                            if(isset($comments->errors)){
                                $this->logger->logError('Error querying YouTube Data API V2 ', __METHOD__.','.__LINE__);
                                break;
                            } elseif($comments == null) {
                                // If the comments come back as null its because we've been making too many requests too
                                // quickly The YouTube api doesn't return valid JSON telling us this though so
                                // json_decode returns null so back off for 30 seconds and then try again
                                $error_message = "Querying the YouTube API too often waiting for 30 seconds, to ";
                                $error_message .= "prevent this delay add a developer key.";
                                $this->logger->logError($error_message, __METHOD__.','.__LINE__);
                                sleep(30);
                                $comments = $this->youtube_api_v2_accessor->basicApiRequest($test['url']);
                            }
                        }
                    } while($test['next']);

                }

                // If we have another page of videos then get the token for the page
                if(isset($user_videos->nextPageToken)) {
                    $videos_fields['pageToken'] = $user_videos->nextPageToken;
                }
            }
            // If we have another page of videos and haven't loaded all this users video yet keep going
            // if we have loaded all this users videos then stop after 1 page (50 videos)
        } while(isset($user_videos->nextPageToken) && !$archive_loaded);

        // If we didn't have to finish the crawl early due to timing out we have collected all this users videos or
        // we have tried more than 20 times stop trying to go back and load the post archive
        if(!$had_to_finish_early || $attempts >= 20) {
            $instance_dao->setPostArchiveLoaded($user_id, 'youtube');
        }
    }

    /**
     * Determines if another page of YouTube comments exists
     * @param  Array $response Decoded JSON from the YouTube comments API
     * @return Array, 'next' is true if another page of comments exists and false otherwise, 'url' contains the URL to
     * vist to fetch the next page of comments if one exists.
     */
    private function determineIfMoreCommentsExist($response) {
        // Based on documentation here: https://developers.google.com/youtube/2.0/reference#Comments_Feeds
        // If there are more pages of results then there will be a link tag with a rel attribute of next
        // Itterate through all the link tags and see if any have a rel attribute of next
        $answer['next'] = false;
        // For each link
        foreach($response->feed->link as $link) {
            if($link->rel == 'next') {
                if(isset($this->developer_key)) {
                    $answer['url'] = $link->href.'&key='.$this->developer_key;
                } else {
                    $answer['url'] = $link->href;
                }
                $answer['next'] = true;
            }
        }
        return $answer;
    }

    /**
     * Determines the privacy status of a video, returns 1 if it is private or unlisted and 0 otherwise
     * @param  str $value A string representing the privacy status of a video
     * @return int 1 if the video is private/unlisted or 0 otherwise
     */
    private function determinePrivacyStatus($value) {
        if($value == 'private' || $value == 'unlisted'){
            return 1;
        }
        else{
            return 0;
        }
    }

    /**
     * Convert decoded JSON data from Google+ for a user into a ThinkUp user object.
     * @param array $details
     * @retun array $user_vals
     */
    private function parseUserDetails($details) {
        if (isset($details->displayName) && isset($details->id)) {
            $user_vals = array();

            $user_vals["user_name"] = $details->displayName;
            $user_vals["full_name"] = $details->displayName;
            $user_vals["user_id"] = $details->id;
            $user_vals["avatar"] = $details->image->url;
            $user_vals['url'] = 'https://plus.google.com/'.$details->id;
            $user_vals["follower_count"] = 0;
            $user_vals["location"] = '';
            if (isset($details->placesLived) && count($details->placesLived) > 0) {
                foreach ($details->placesLived as $placeLived){
                    if (isset($placeLived->primary))
                    $user_vals["location"] = $placeLived->value;
                }
            }
            $user_vals["description"] = isset($details->tagline)?$details->tagline:'';
            $user_vals["is_verifed"] = isset($details->verified)?$details->verified:'';
            $user_vals["is_protected"] = 0; //All Google+ users are public
            $user_vals["post_count"] = 0;
            $user_vals["joined"] = null;
            $user_vals["network"] = $details->network;
            //this will help us in getting correct range of posts
            $user_vals["updated_time"] = isset($details->updated_time)?$details->updated_time:0;
            return $user_vals;
        }
    }

    /**
     * Convert decoded JSON data from YouTube for a user into a ThinkUp user object.
     * @param array $details
     * @retun array $user_vals
     */
    private function parseYouTubeUserDetails($details) {
        if (isset($details->displayName) && isset($details->id)) {
            $user_vals = array();

            $user_id_string = explode('/', $details->author[0]->uri->{'$t'});
            $user_id = $user_id_string[sizeof($user_id_string)-1];

            $user_vals["user_name"] = $details->{'yt$username'}->{'$t'};
            $user_vals["full_name"] = self::determineFullName($details);
            $user_vals["user_id"] = $user_id;
            $user_vals["avatar"] = $details->{'media$thumbnail'}->url;
            $user_vals['url'] = $details->link[0]->href;
            $user_vals["follower_count"] = 0;
            $user_vals["location"] = '';
            $user_vals["description"] = '';
            $user_vals["is_verifed"] = 0;
            $user_vals["is_protected"] = 0;
            $user_vals["post_count"] = 0;
            $user_vals["joined"] = null;
            $user_vals["network"] = 'youtube';
            //this will help us in getting correct range of posts
            $user_vals["updated_time"] = null;
            return $user_vals;
        }
    }

    /**
     * Returns the users full name based on how much data they have added to their profile.
     * @param  array $details An array of decoded json containing details about the users name
     * @return str A string representing their full name
     */
    private function determineFullName($details) {
        $name = '';
        if( isset($details->{'yt$firstName'}) ) {
            $name .= $details->{'yt$firstName'};
        }
        if( isset($details->{'yt$lastName'}) ) {
            $name .= ' '.$details->{'yt$lstName'};
        }
        return $name;
    }



}
