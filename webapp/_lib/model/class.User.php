<?php
// require_once 'model/class.Utils.php';

/**
 *
 * ThinkUp/webapp/_lib/model/class.User.php
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
 * User class
 *
 * This class represents social network users like @ginatrapani on Twitter, or Joe Smith on Facebook.
 * It does not represent not ThinkUp users, see the Owner class for ThinkUp users.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class User {
    /**
     *
     * @var int
     */
    var $id;
    /**
     *
     * @var str
     */
    var $username;
    /**
     *
     * @var str
     */
    var $full_name;
    /**
     *
     * @var str
     */
    var $avatar;
    /**
     *
     * @var str
     */
    var $gender;
    /**
     *
     * @var str
     */
    var $birthday;
    /**
     *
     * @var location
     */
    var $location;
    /**
     *
     * @var description
     */
    var $description;
    /**
     *
     * @var url
     */
    var $url;
    /**
     *
     * @var bool
     */
    var $is_verified;
    /**
     *
     * @var bool
     */
    var $is_protected;
    /**
     *
     * @var int
     */
    var $follower_count;
    /**
     *
     * @var int
     */
    var $friend_count;
    /**
     *
     * @var int
     */
    var $favorites_count;
    /**
     *
     * @var int
     */
    var $post_count;
    /**
     *
     * @var date
     */
    var $last_updated;

    /**
     *
     * @var str
     */
    var $found_in;
    /**
     *
     * @var int
     */
    var $last_post;
    /**
     *
     * @var date
     */
    var $joined;
    /**
     *
     * @var int
     */
    var $last_post_id;
    /**
     *
     * @var str Default 'twitter'
     */
    var $network;
    /**
     *
     * @var int
     */
    var $user_id;
    /**
     *
     * @var array
     */
    var $other = array();

    /**
     * Constructor
     * @param array $val User key/value pairs
     * @param str $found_in Where user was found
     * @return User New user
     */
    public function __construct($val = false, $found_in = false) {
        if ($val){
            if (isset($val['id'])) {
                $this->id = $val['id'];
            }
            $this->username = $val['user_name'];
            $this->full_name = $val['full_name'];
            $this->user_id = $val['user_id'];
            $this->avatar = $val['avatar'];
            if (isset($val['gender'])) {
            	$this->gender = $val['gender'];
            }
            if (isset($val['birthday'])) {
            	$this->birthday = $val['birthday'];
            }
            $this->location = $val['location'];
            $this->description = $val['description'];
            $this->url = $val['url'];
            if (isset($val['is_verified'])) {
                $this->is_verified = $val['is_verified'];
            }
            if ($this->is_verified == '') {
                $this->is_verified = 0;
            } elseif ($this->is_verified == 'true') {
                $this->is_verified = 1;
            }
            $this->is_protected = $val['is_protected'];
            if ($this->is_protected == '') {
                $this->is_protected = 0;
            } elseif ($this->is_protected == 'true') {
                $this->is_protected = 1;
            }
            $this->follower_count = $val['follower_count'];
            $this->post_count = $val['post_count'];
            if (isset($val['last_post_id'])) {
                $this->last_post_id = $val['last_post_id'];
            }
            if (isset($val['last_updated'])) {
                $this->last_updated = $val['last_updated'];
            }
            if (isset($val['friend_count'])) {
                $this->friend_count = $val['friend_count'];
            }
            if (isset($val['favorites_count'])) {
                $this->favorites_count = $val['favorites_count'];
            }
            if (isset($val['last_post'])) {
                $this->last_post = $val['last_post'];
            }
            if (isset($val['joined'])) {
                $this->joined = $val['joined'];
            }
            $this->found_in = $found_in;

            if (isset($val['avg_tweets_per_day'])) {
                $this->avg_tweets_per_day = $val['avg_tweets_per_day'];
            }

            if (isset($val['network'])) {
                $this->network = $val['network'];
            }
        } else {
            if ($this->is_protected == '') {
                $this->is_protected = 0;
            } elseif ($this->is_protected == 'true') {
                $this->is_protected = 1;
            }
        }
    }

    /**
     * Overload the set method for mismatched member variable names
     * @param str $key
     * @param mixed $val
     */
    public function __set($key, $val){
        switch($key){
            case "user_name":
                $this->username = $val;
                break;
            default:
                $this->other[$key] = $val;
        }
    }
}
