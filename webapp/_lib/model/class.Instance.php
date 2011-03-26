<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Instance.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * Instance - a service and user account
 *
 * An instance is a service and user account, i.e., @thinkupapp on Twitter is an instance. The ThinkUp Facebook Page
 * is also an instance.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 */
class Instance {
    /**
     * @var int
     */
    var $id;
    /**
     * @var str
     */
    var $network_username;
    /**
     * @var int Service-specific ID, like the Twitter ID or Facebook user ID
     */
    var $network_user_id;
    /**
     * var @int Service-specific ID of user viewing the instance, needed when permissions are different depending on
     * who the viewer user is
     */
    var $network_viewer_id;
    /**
     * @var int
     */
    var $last_post_id;
    /**
     * @var int
     */
    var $last_page_fetched_replies;
    /**
     * @var int
     */
    var $last_page_fetched_tweets;
    /**
     * @var int
     */
    var $total_posts_in_system;
    /**
     * @var int
     */
    var $total_replies_in_system;
    /**
     * @var int
     */
    var $total_follows_in_system;
    /**
     * @var int
     */
    var $total_friends_in_system;
    /**
     * @var int
     */
    var $total_users_in_system;
    /**
     * @var bool
     */
    var $is_archive_loaded_replies;
    /**
     * @var bool
     */
    var $is_archive_loaded_follows;
    /**
     * @var bool
     */
    var $is_archive_loaded_friends;
    /**
     * @var date
     */
    var $crawler_last_run;
    /**
     * @var int
     */
    var $earliest_reply_in_system;
    /**
     * @var int
     */
    var $avg_replies_per_day;
    /**
     * @var bool
     */
    var $is_public = false;
    /**
     * @var bool
     */
    var $is_active = true;
    /**
     * @var str
     */
    var $network;
    /**
     * @var int
     */
    var $last_favorite_id;
    /**
     * @var int
     */
    var $last_unfav_page_checked;
    /**
     * @var int
     */
    var $last_page_fetched_favorites;
    /**
     * @var int
     */
    var $favorites_profile;
    /**
     * @var int
     */
    var $owner_favs_in_system;

    public function __construct($r = false) {
        if ($r){
            $this->id = $r['id'];
            $this->network_username = $r['network_username'];
            $this->network_user_id = $r['network_user_id'];
            $this->last_post_id = $r['last_post_id'];
            $this->last_page_fetched_replies = $r['last_page_fetched_replies'];
            $this->last_page_fetched_tweets = $r['last_page_fetched_tweets'];
            $this->total_posts_in_system = $r['total_posts_in_system'];
            $this->total_replies_in_system = $r['total_replies_in_system'];
            $this->total_follows_in_system = $r['total_follows_in_system'];
            $this->total_users_in_system = $r['total_users_in_system'];
            if ($r['is_archive_loaded_replies'] == 1){
                $this->is_archive_loaded_replies = true;
            } else {
                $this->is_archive_loaded_replies = false;
            }
            if ($r['is_archive_loaded_follows'] == 1){
                $this->is_archive_loaded_follows = true;
            } else {
                $this->is_archive_loaded_follows = false;
            }

            $this->crawler_last_run = $r['crawler_last_run'];
            $this->earliest_reply_in_system = $r['earliest_reply_in_system'];
            $this->avg_replies_per_day = $r['avg_replies_per_day'];
            $this->network = $r['network'];
            $this->last_favorite_id = $r['last_favorite_id'];
            $this->last_unfav_page_checked = $r['last_unfav_page_checked'];
            $this->last_page_fetched_favorites = $r['last_page_fetched_favorites'];
            $this->favorites_profile = $r['favorites_profile'];
            $this->owner_favs_in_system = $r['owner_favs_in_system'];

            if ($r['is_public'] == 1){
                $this->is_public = true;
            }
            if ($r['is_active'] == 0){
                $this->is_active = false;
            }
        } else {
            $this->is_archive_loaded_replies = ($this->is_archive_loaded_replies == 1 ? true : false );
            $this->is_archive_loaded_follows = ($this->is_archive_loaded_follows == 1 ? true : false );
            $this->is_public = ($this->is_public == 1 ? true : false );
        }
    }
}
