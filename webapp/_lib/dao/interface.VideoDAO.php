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
}
