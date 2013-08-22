<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.VideoDAO.php
 *
 * Copyright (c) 2011-2013 Aaron Kalair
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
 * Video Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @author Aaron Kalair
 */
interface VideoDAO {
    /**
     * Inserts a video in storage. Values split roughly 50/50 between posts and videos.
     * @param arr $vals The values to store in the database, at least need the required parameters
     * @return str Internal post ID of the inserted video
     */
    public function addVideo($vals);
    /**
     * Updates all of the counts for a given video.
     * @param  arr $vals New counts for all the attributes and the internal post key
     * @return str Internal post ID of the inserted video
     */
    public function updateVideoCounts($vals);
    /**
     * Returns video details from the database.
     * @param  str $video_id The ID of the video on the social network
     * @param  str $network Network the video is hosted on
     * @return Video Object Containing all the videos details
     */
    public function getVideoByID($video_id, $network);
    /**
     * Returns the highest percentage of dislikes a users video has, for all time if $duration is null or for $duration
     * days if it is set.
     * @param  str $username Username of the user to retrieve the count for
     * @param  str $network  Network the videos were posted on
     * @param  str $duration Defaults to null and includes every video in the count, if set only includes videos from
     *                       the last $duration days.
     * @return int The highest percentage of dislikes a users video has
     */
    public function getHighestDislikePercentage($username, $network, $duration=null);
    /**
     * Returns the highest percentage of likes a users video has, for all time if $duration is null or for $duration
     * days if it is set.
     * @param  str $username Username of the user to retrieve the count for
     * @param  str $network  Network the videos were posted on
     * @param  str $duration Defaults to null and includes every video in the count, if set only includes videos from
     *                       the last $duration days.
     * @return int The highest percentage of likes a users video has
     */
    public function getHighestLikePercentage($username, $network, $duration=null);
    /**
     * Returns the average percentage of dislikes a users video has, for all time if $duration is null or for $duration
     * days if it is set.
     * @param  str $username Username of the user to retrieve the count for
     * @param  str $network  Network the videos were posted on
     * @param  str $duration Defaults to null and includes every video in the count, if set only includes videos from
     *                       the last $duration days.
     * @return int The average percentage of dislikes a users video has
     */
    public function getAverageDislikePercentage($username, $network, $duration=null);
    /**
     * Returns the average percentage of likes a users video has, for all time if $duration is null or for $duration
     * days if it is set.
     * @param  str $username Username of the user to retrieve the count for
     * @param  str $network  Network the videos were posted on
     * @param  str $duration Defaults to null and includes every video in the count, if set only includes videos from
     *                       the last $duration days.
     * @return int The average percentage of likes a users video has
     */
    public function getAverageLikePercentage($username, $network, $duration=null);
}
