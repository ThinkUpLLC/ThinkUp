<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Instance.php
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
 * Instance - Authorized network user for which ThinkUp archives data.
 *
 * An instance is a service and user account, i.e., @thinkupapp on Twitter is an instance. The ThinkUp Facebook Page
 * is also an instance.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
class Instance {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var int User ID on a given network, like a user's Twitter ID or Facebook user ID.
     */
    var $network_user_id;
    /**
     * @var int Network user ID of the viewing user (which can affect permissions).
     */
    var $network_viewer_id;
    /**
     * @var str Username on a given network, like a user's Twitter username or Facebook user name.
     */
    var $network_username;
    /**
     * @var int Last network post ID fetched for this instance.
     */
    var $last_post_id;
    /**
     * @var str The last time the crawler completed a run for this instance.
     */
    var $crawler_last_run;
    /**
     * @var int Total posts by this instance as reported by service API.
     */
    var $total_posts_by_owner;
    /**
     * @var int Total posts in datastore authored by this instance.
     */
    var $total_posts_in_system;
    /**
     * @var int Total replies in datastore authored by this instance.
     */
    var $total_replies_in_system;
    /**
     * @var int Total active follows where instance is the followed user.
     */
    var $total_follows_in_system;
    /**
     * @var float Average posts per day by instance.
     */
    var $posts_per_day;
    /**
     * @var float Average posts per week by instance.
     */
    var $posts_per_week;
    /**
     * @var float Percent of an instance's posts which are replies.
     */
    var $percentage_replies;
    /**
     * @var float Percent of an instance's posts which contain links.
     */
    var $percentage_links;
    /**
     * @var str Date and time of the earliest post authored by the instance in the datastore.
     */
    var $earliest_post_in_system;
    /**
     * @var str Date and time of the earliest reply authored by the instance in the datastore.
     */
    var $earliest_reply_in_system;
    /**
     * @var bool Whether or not all the instance's posts have been backfilled.
     */
    var $is_archive_loaded_posts = false;
    /**
     * @var bool Whether or not all the instance's replies have been backfilled.
     */
    var $is_archive_loaded_replies = false;
    /**
     * @var bool Whether or not all the instance's follows have been backfilled.
     */
    var $is_archive_loaded_follows = false;
    /**
     * @var bool Whether or not instance is public in ThinkUp, that is, viewable when no ThinkUp user is logged in.
     */
    var $is_public = false;
    /**
     * @var bool Whether or not the instance user is being actively crawled (0 if it is paused).
     */
    var $is_active = false;
    /**
     * @var str The lowercase name of the source network, i.e., twitter or facebook.
     */
    var $network;
    /**
     * @var int Total instance favorites as reported by the service API.
     */
    var $favorites_profile;
    /**
     * @var int Total instance favorites saved in the datastore.
     */
    var $owner_favs_in_system;
    public function __construct($row = false) {
        if ($row) {
            $this->id = $row['id'];
            $this->network_user_id = $row['network_user_id'];
            $this->network_viewer_id = $row['network_viewer_id'];
            $this->network_username = $row['network_username'];
            $this->last_post_id = $row['last_post_id'];
            $this->crawler_last_run = $row['crawler_last_run'];
            $this->total_posts_by_owner = $row['total_posts_by_owner'];
            $this->total_posts_in_system = $row['total_posts_in_system'];
            $this->total_replies_in_system = $row['total_replies_in_system'];
            $this->total_follows_in_system = $row['total_follows_in_system'];
            $this->posts_per_day = $row['posts_per_day'];
            $this->posts_per_week = $row['posts_per_week'];
            $this->percentage_replies = $row['percentage_replies'];
            $this->percentage_links = $row['percentage_links'];
            $this->earliest_post_in_system = $row['earliest_post_in_system'];
            $this->earliest_reply_in_system = $row['earliest_reply_in_system'];
            $this->is_archive_loaded_posts = PDODAO::convertDBToBool($row['is_archive_loaded_posts']);
            $this->is_archive_loaded_replies = PDODAO::convertDBToBool($row['is_archive_loaded_replies']);
            $this->is_archive_loaded_follows = PDODAO::convertDBToBool($row['is_archive_loaded_follows']);
            $this->is_public = PDODAO::convertDBToBool($row['is_public']);
            $this->is_active = PDODAO::convertDBToBool($row['is_active']);
            $this->network = $row['network'];
            $this->favorites_profile = $row['favorites_profile'];
            $this->owner_favs_in_system = $row['owner_favs_in_system'];
        }
    }
}
