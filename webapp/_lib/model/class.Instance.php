<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Instance.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
*/
class Instance {
    var $id;
    var $network_username;
    var $network_user_id;
    var $network_viewer_id;
    var $last_status_id;
    var $last_page_fetched_replies;
    var $last_page_fetched_tweets;
    var $total_posts_in_system;
    var $total_replies_in_system;
    var $total_follows_in_system;
    var $total_friends_in_system;
    var $total_users_in_system;
    var $is_archive_loaded_replies;
    var $is_archive_loaded_follows;
    var $is_archive_loaded_friends;
    var $crawler_last_run;
    var $earliest_reply_in_system;
    var $api_calls_to_leave_unmade_per_minute;
    var $avg_replies_per_day;
    var $is_public = false;
    var $is_active = true;
    var $network;

    public function __construct($r = false) {
        if ($r){
            $this->id = $r['id'];
            $this->network_username = $r['network_username'];
            $this->network_user_id = $r['network_user_id'];
            $this->last_status_id = $r['last_status_id'];
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
            $this->api_calls_to_leave_unmade_per_minute = $r['api_calls_to_leave_unmade_per_minute'];
            $this->avg_replies_per_day = $r['avg_replies_per_day'];
            $this->network = $r['network'];

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
