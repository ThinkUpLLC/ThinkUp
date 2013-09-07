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
     * Returns the $column count of the most recent $limit videos.
     * @param  str $username Username of the user to retrieve the count for
     * @param  str $network  Network the videos were posted on
     * @param  int $limit The maximum number of videos to return
     * @param  str $column The name of the column to show in the graph e.g. likes, dislikes etc.
     * @param  str $as If set renames the column selected in the results, useful for formatting names in the graph
     * @return Array An array of video $column or $as counts if $as is set, designed to be fed into
     * getHotVideosVisualizationData()
     */
    public function getHotVideos($username, $network, $limit, $column, $as=null);
    /**
     * Returns the average of the average view percentage for the last $duration days if $duration is not null or
     * all time if it is.
     * @param  str $username Username of the user to retrieve the count for
     * @param  str $network  Network the videos were posted on
     * @param  int $duration Defaults to null and includes every video in the count, if set only includes videos from
     *             the last $duration days.
     * @return int the average of the average view percentage
     */
    public function getAverageOfAverageViewPercentage($username, $network, $duration=null);
    /**
     * Returns the lowest of the average view percentage for the last $duration days if $duration is not null or
     * all time if it is.
     * @param  str $username Username of the user to retrieve the count for
     * @param  str $network  Network the videos were posted on
     * @param  int $duration Defaults to null and includes every video in the count, if set only includes videos from
     *             the last $duration days.
     * @return int the lowest of the average view percentage
     */
    public function getAverageViewPercentageLow($username, $network, $duration=null);
    /**
     * Returns the highest of the average view percentage for the last $duration days if $duration is not null or
     * all time if it is.
     * @param  str $username Username of the user to retrieve the count for
     * @param  str $network  Network the videos were posted on
     * @param  int $duration Defaults to null and includes every video in the count, if set only includes videos from
     *             the last $duration days.
     * @return int the highest of the average view percentage
     */
    public function getAverageViewPercentageHigh($username, $network, $duration=null);
    /**
     * Returns the highest number of likes a users video has, since $since_date if it is set or since today if it
     * is null and for all time since $since_date if $duration is null or for $duration days before $since_date if it
     * is set.
     * @param  str $username Username of the user to retrieve the count for
     * @param  str $network  Network the videos were posted on
     * @param  str $duration Defaults to null and includes every video in the count, if set only includes videos from
     *                       the last $duration days.
     * @param str $since_date Defaults to todays date or if set includes videos since $since_date
     * @return int The highest percentage of likes a users video has
     */
    public function getHighestLikes($username, $network, $duration=null, $since_date=null);
    /**
     * Returns the average number of likes a users videos has, since $since_date if it is set or since today if it
     * is null and for all time since $since_date if $duration is null or for $duration days before $since_date if it
     * is set.
     * @param  str $username Username of the user to retrieve the count for
     * @param  str $network  Network the videos were posted on
     * @param  str $duration Defaults to null and includes every video in the count, if set only includes videos from
     *                       the last $duration days.
     * @param str $since_date Defaults to todays date or if set includes videos since $since_date
     * @return int The average percentage of likes a users video has
     */
    public function getAverageLikeCount($username, $network, $duration=null);
    /**
     * Returns true if the user has posted videos which have likes in $duration days since today if $since is null or
     * since $since if it is not.
     * @param  str $username Username of the user to retrieve the count for
     * @param  str $network  Network the videos were posted on
     * @param  str $duration Defaults to null and includes every video in the check, if set only includes videos from
     *                       the last $duration days.
     * @param str $since_date Defaults to todays date or if set includes videos since $since_date
     * @return int True or False
     */
    public function doesUserHaveVideosWithLikesSinceDate($username, $network, $duration, $since=null);
}
