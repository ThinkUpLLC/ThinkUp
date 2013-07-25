<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Video.php
 *
 * Copyright (c) 2009-2013 Aaron Kalair
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
 * Video class
 *
 * This class represents a video posted on a network such as YouTube.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Aaron Kalair
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 *
 */
class Video extends Post {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var int ID of the row in the posts table for more information on this video.
     */
    var $post_key;
    /**
     * @var string The description of this video.
     */
    var $description;
    /**
     * @var int Total number of likes this video has received.
     */
    var $likes;
    /**
     * @var int Total number of dislikes this video has received.
     */
    var $dislikes;
    /**
     * @var int Total number of views on this video.
     */
    var $views;
    /**
     * @var int Total number of minutes people have spent watching this video.
     */
    var $minutes_watched;
    /**
     * @var int Average number of minuites people spent watching this video.
     */
    var $average_view_duration;
    /**
     * @var int Average percentage of this video people watched.
     */
    var $average_view_percentage;
    /**
     * @var int Number of people who favorited this video.
     */
    var $favorites_added;
    /**
     * @var int Number of people who removed this video from their favorites.
     */
    var $favorites_removed;
    /**
     * @var int Number of times people shared this video through the share button.
     */
    var $shares;
    /**
     * @var int Number of people who subscribed to this users channel on this videos page.
     */
    var $subscribers_gained;
    /**
     * @var int Number of people who unsubscribed to this users channel on this videos page.
     */
    var $subscribers_lost;
    public function __construct($row = false) {
        if ($row) {
            parent::__construct($row);
            $this->id = $row['id'];
            $this->post_key = $row['post_key'];
            $this->description = $row['description'];
            $this->likes = $row['likes'];
            $this->dislikes = $row['dislikes'];
            $this->views = $row['views'];
            $this->minutes_watched = $row['minutes_watched'];
            $this->average_view_duration = $row['average_view_duration'];
            $this->average_view_percentage = $row['average_view_percentage'];
            $this->favorites_added = $row['favorites_added'];
            $this->favorites_removed = $row['favorites_removed'];
            $this->shares = $row['shares'];
            $this->subscribers_gained = $row['subscribers_gained'];
            $this->subscribers_lost = $row['subscribers_lost'];
        }
    }
}