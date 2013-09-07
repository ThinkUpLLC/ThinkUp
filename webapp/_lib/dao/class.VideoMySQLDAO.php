<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.VideoMySQLDAO.php
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
 * Video Data Access Object
 *
 * The data access object for retrieving and saving videos in the ThinkUp database
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Aaron Kalair
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 */
class VideoMySQLDAO extends PostMySQLDAO implements VideoDAO  {

    /**
     * Fields required for a video.
     * @var array
     */
    var $REQUIRED_VIDEO_FIELDS =  array('likes','dislikes','views','minutes_watched','average_view_duration',
    'average_view_percentage', 'favorites_added', 'favorites_removed', 'shares', 'subscribers_gained',
    'subscribers_lost', 'description');

    /**
     * Checks to see if the $vals array contains all the required fields to insert a post
     * @param array $vals
     * @return bool
     */
    private function hasAllRequiredFields($vals) {
        $result = true;
        foreach ($this->REQUIRED_VIDEO_FIELDS as $field) {
            if ( !isset($vals[$field]) ) {
                $this->logger->logError("Missing post $field value", __METHOD__.','.__LINE__);
                $result = false;
            }
        }
        return $result;
    }

    public function addVideo($vals) {
        $res = null;
        // Check all the fields we need are set
        if (self::hasAllRequiredFields($vals)) {

            // Insert the values which go into the post table
            $row_id = parent::addPost($vals);

            // If the post insertion went fine insert the values that go into the videos table
            if ($row_id != null) {
                //SQL variables to bind
                $vars = array();
                //SQL query
                $q = "INSERT IGNORE INTO #prefix#videos SET ";
                //Set up required fields
                foreach ($this->REQUIRED_VIDEO_FIELDS as $field) {
                    $q .= $field."=:".$field.", ";
                    $vars[':'.$field] = $vals[$field];
                }

                // Append the internal post ID
                $q .= 'post_key=:post_key';
                $vars[':post_key'] = $row_id;

                // Try to insert the video
                if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
                $ps = $this->execute($q, $vars);
                $res = $this->getInsertId($ps);
            } else {
                // If the video was already in the database, update the counts
                $res = self::updateVideoCounts($vals);
            }
        }
        return $res;
    }

    public function updateVideoCounts($vals) {
        $q = "UPDATE #prefix#videos SET ";
        foreach ($this->REQUIRED_VIDEO_FIELDS as $field) {
            $q .= $field."=:".$field.", ";
            $vars[':'.$field] = $vals[$field];
        }

        // remove the final , and space
        $q = substr($q, 0, (strlen($q)-2));

        // Find out the internal ID for this post so we know which row to update
        $post_dao = DAOFactory::getDAO('PostDAO');
        $post_row = $post_dao->getPost($vals['post_id'], 'youtube');
        // Set the video to update
        $q .= " WHERE post_key=:post_id";
        $vars[':post_id'] = $post_row->id;
        // Update it
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function getVideoByID($video_id, $network) {
        $q = "SELECT po.id, po.post_id, po.author_user_id, po.author_username, po.author_fullname, po.author_avatar, ";
        $q .= "po.post_text, po.is_protected, po.location, po.place, po.geo, pub_date + interval #gmt_offset# hour as ";
        $q .= "adj_pub_date, po.pub_date, po.reply_count_cache, po.network, vid.id, vid.post_key, vid.description, ";
        $q .= "vid.dislikes, vid.views, vid.minutes_watched, vid.average_view_duration, vid.average_view_percentage, ";
        $q .= "vid.favorites_added, vid.favorites_removed, vid.shares, vid.subscribers_gained, vid.subscribers_lost, ";
        $q .= "vid.likes FROM #prefix#posts po JOIN #prefix#videos vid ON po.id = vid.post_key ";
        $q .= "WHERE po.post_id=:video_id AND po.network=:network";
        $vars = array(
            ':video_id'=>$video_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        $post_row = $this->getDataRowAsArray($ps);
        if ($post_row) {
            return new Video($post_row);
        }
        else{
            return null;
        }
    }

    public function getHotVideos($network_username, $network, $limit, $column, $as=null) {
        $q .= "SELECT $column ";
        if ($as != null ) {
            $q .= "AS '$as'";
        }
        $q .= ", pub_date, post_text FROM #prefix#posts as posts JOIN #prefix#videos as videos ON ";
        $q .= "posts.id = videos.post_key WHERE author_username=:username AND ";
        $q .= "network=:network AND $column > 0 ORDER BY pub_date DESC LIMIT :limit";
        $vars = array(
            ':username'=>$network_username,
            ':network'=>$network,
            ':limit'=>(int)$limit
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    /**
     * Convert hot video data to JSON for Google Charts
     * @param arr $client_usage Array returned from VideoDAO::getHotPosts
     * @return str JSON
     */
    public static function getHotVideosVisualizationData($hot_videos, $column) {
        $metadata = array(
        array('type' => 'string', 'label' => 'Video Title'),
        array('type' => 'number', 'label' => $column),
        );
        $result_set = array();
        foreach ($hot_videos as $video) {
            $result_set[] = array('c' => array(
            array('v' => $video['post_text'], 'f' => $video['post_text']),
            array('v' => intval($video[$column])),
            ));
        }
        return json_encode(array('rows' => $result_set, 'cols' => $metadata));
    }

    public function getAverageOfAverageViewPercentage($username, $network, $duration=null) {
        $q = "SELECT AVG(average_view_percentage) AS count FROM #prefix#posts AS posts JOIN #prefix#videos AS videos ";
        $q .= "ON posts.id = videos.post_key WHERE author_username=:username AND network=:network ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($duration != null) {
            $q .= "AND pub_date >= DATE_SUB(NOW(), INTERVAL :duration DAY)";
            $vars[':duration'] = $duration;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataCountResult($ps);
    }

    public function getAverageViewPercentageHigh($username, $network, $duration=null) {
        $q = "SELECT MAX(average_view_percentage) AS count FROM #prefix#posts AS posts JOIN #prefix#videos AS videos ";
        $q .= "ON posts.id = videos.post_key WHERE author_username=:username AND network=:network ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($duration != null) {
            $q .= "AND pub_date >= DATE_SUB(NOW(), INTERVAL :duration DAY)";
            $vars[':duration'] = $duration;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataCountResult($ps);
    }

    public function getAverageViewPercentageLow($username, $network, $duration=null) {
        $q = "SELECT MIN(average_view_percentage) AS count FROM #prefix#posts AS posts JOIN #prefix#videos AS videos ";
        $q .= "ON posts.id = videos.post_key WHERE author_username=:username AND network=:network ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($duration != null) {
            $q .= "AND pub_date >= DATE_SUB(NOW(), INTERVAL :duration DAY)";
            $vars[':duration'] = $duration;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataCountResult($ps);
    }

    public function getHighestLikes($username, $network, $duration=null, $start_date=null) {
        $q = "SELECT MAX(likes) AS count FROM #prefix#posts as posts ";
        $q .= "JOIN #prefix#videos as videos ON posts.id = videos.post_key WHERE author_username =:username ";
        $q .= "AND network =:network ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($duration != null) {
            if ($start_date == null) {
                $start_date = date('Y-m-d');
            }
            $q .= "AND pub_date >= DATE_SUB(:start, INTERVAL :duration DAY)";
            $vars[':duration'] = $duration;
            $vars[':start'] = $start_date;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataCountResult($ps);
    }

    public function getAverageLikeCount($username, $network, $duration=null, $start_date=null) {
        $q = "SELECT AVG(likes) AS count FROM #prefix#posts as posts ";
        $q .= "JOIN #prefix#videos as videos ON posts.id = videos.post_key WHERE author_username =:username ";
        $q .= "AND network =:network ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($duration != null) {
            if ($start_date == null) {
                $start_date = date('Y-m-d');
            }
            $q .= "AND pub_date >= DATE_SUB(:start, INTERVAL :duration DAY)";
            $vars[':duration'] = $duration;
            $vars[':start'] = $start_date;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataCountResult($ps);
    }

    public function doesUserHaveVideosWithLikesSinceDate($network_username, $network, $duration, $since=null) {
        if ($since==null) {
            $since = date('Y-m-d');
        }

        $q = "SELECT posts.id FROM #prefix#posts as posts JOIN #prefix#videos AS videos ON posts.id = videos.post_key ";
        $q .= "WHERE network=:network and author_username=:author_username AND in_reply_to_user_id IS null ";
        $q .= "AND in_reply_to_post_id IS null AND likes > 0 ";
        $q .= "AND pub_date >= DATE_SUB(:since, INTERVAL :duration DAY) LIMIT 1";
        $vars = array(
            ':author_username'=>$network_username,
            ':network'=>$network,
            ':duration'=>(int)$duration,
            ':since'=>$since
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowsAsArrays($ps);
        if (sizeof($result) < 1 ) {
            return false;
        } else {
            return true;
        }
    }

    public function getAverageViews($username, $network, $duration=null, $start_date=null) {
        if ($start_date == null) {
            $start_date = date('Y-m-d');
        }
        $q = "SELECT AVG(views) AS count FROM #prefix#posts as posts JOIN #prefix#videos as videos ON ";
        $q .= "posts.id = videos.post_key WHERE author_username=:username AND network=:network ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($duration != null) {
            $q .= "AND pub_date >= DATE_SUB(:start_date, INTERVAL :duration DAY)";
            $vars[':duration'] = $duration;
            $vars[':start_date'] = $start_date;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataCountResult($ps);
    }

    public function getHighestViews($username, $network, $duration=null, $start_date=null) {
        if($start_date == null) {
            $start_date = date('Y-m-d');
        }
        $q = "SELECT MAX(views) AS count FROM #prefix#posts as posts JOIN #prefix#videos as videos ON ";
        $q .= "posts.id = videos.post_key WHERE author_username=:username AND network=:network ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($duration != null) {
            $q .= "AND pub_date >= DATE_SUB(:start_date, INTERVAL :duration DAY)";
            $vars[':duration'] = $duration;
            $vars[':start_date'] = $start_date;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataCountResult($ps);
    }

    public function doesUserHaveVideosWithViewsSinceDate($network_username, $network, $duration, $since=null) {
        if ($since==null) {
            $since = date('Y-m-d');
        }
        $q = "SELECT posts.id FROM #prefix#posts as posts JOIN #prefix#videos AS videos ON posts.id = videos.post_key ";
        $q .= "WHERE network=:network and author_username=:author_username AND in_reply_to_user_id IS null ";
        $q .= "AND in_reply_to_post_id IS null AND views > 0 ";
        $q .= "AND pub_date >= DATE_SUB(:since, INTERVAL :duration DAY) LIMIT 1";
        $vars = array(
            ':author_username'=>$network_username,
            ':network'=>$network,
            ':duration'=>(int)$duration,
            ':since'=>$since
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowsAsArrays($ps);
        if (sizeof($result) < 1 ) {
            return false;
        } else {
            return true;
        }
    }

    public function getNetSubscriberChange($username, $network, $limit) {
        $q = "SELECT (subscribers_gained - subscribers_lost) AS 'Subscriber Change', pub_date, post_text FROM ";
        $q .= "#prefix#videos AS videos JOIN #prefix#posts AS posts ON posts.id = videos.post_key WHERE ";
        $q .= "author_username=:username AND network=:network AND (subscribers_gained - subscribers_lost) != 0 ";
        $q .= "ORDER BY pub_date LIMIT :limit";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network,
            ':limit'=>$limit
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($ps);
    }

    public function getAverageMinutesViewed($user_name, $network, $duration=null) {
        $q = "SELECT AVG(minutes_watched) AS count FROM #prefix#posts AS posts JOIN #prefix#videos AS videos ON ";
        $q .= "posts.id = videos.post_key WHERE author_username=:user_name AND network=:network ";
        $vars = array(
            ':user_name'=>$user_name,
            ':network'=>$network
        );
        if($duration != null) {
            $q .= "AND pub_date >= DATE_SUB(NOW(), INTERVAL :duration DAY) ";
            $vars[':duration'] = $duration;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataCountResult($ps);
    }

    public function getHighestMinutesViewed($user_name, $network, $duration=null) {
        $q = "SELECT MAX(minutes_watched) AS count FROM #prefix#posts AS posts JOIN #prefix#videos AS videos ON ";
        $q .= "posts.id = videos.post_key WHERE author_username=:user_name AND network=:network ";
        $vars = array(
            ':user_name'=>$user_name,
            ':network'=>$network,
        );
        if($duration != null) {
            $q .= "AND pub_date >= DATE_SUB(NOW(), INTERVAL :duration DAY) ";
            $vars[':duration'] = $duration;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataCountResult($ps);
    }
}