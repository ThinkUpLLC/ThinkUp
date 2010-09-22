<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/classes/mock.facebook.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Dwi Widiastuti
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
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti
*/
class MockFacebookRestClient {
    var $data_file_path;

    public function __construct() {
        $this->data_file_path = THINKUP_ROOT_PATH . 'webapp/plugins/facebook/tests/testdata/';
    }

    public function & stream_get($viewer_id = null, $source_ids = null, $start_time = 0, $end_time = 0, $limit = 30,
    $filter_key = '', $exportable_only = false, $metadata = null, $post_ids = null, $query = null,
    $everyone_stream = false) {
        if ($viewer_id==$source_ids) { //user profile
            //echo "Fetching user profile...";
            switch ($viewer_id) {
                case '606837591': //return two posts with no comments
                    $stream = unserialize(
                    file_get_contents($this->data_file_path.'testFetchUserStreamWithTwoPostsNoComments'));
                    return $stream;
                    break;
                case '6068375911': //return two posts, one with one comment
                    $stream = unserialize(file_get_contents(
                    $this->data_file_path.'testFetchUserStreamWithTwoPostsAndOneComment'));
                    return $stream;
                    break;
                default:
                    return null;
                    break;
            }
        } else {
            switch ($source_ids) {
                case '63811549237':
                    $stream = unserialize(file_get_contents($this->data_file_path.'testFetchPageStream'));
                    return $stream;
                    break;
                default:
                    return null;
                    break;

            }
        }
    }

    public function & users_getInfo($uid,$fields) {
        switch ($uid) {
            case '606837591': //return user profile
                $details = unserialize(file_get_contents($this->data_file_path.'testFetchInstanceUserInfo'));
                break;
            default:
                $details = unserialize(file_get_contents($this->data_file_path.'testFetchInstanceUserInfo'));
                break;
        }
        return $details;

    }

    public function & fql_query($q) {
        $data = null;
        //        echo $q .'
        //        ';

        $fan_of_q = 'SELECT page_id, name, page_url, pic_square FROM page WHERE page_id IN ';
        $fan_of_q .= '(SELECT page_id FROM page_fan WHERE uid=606837591)';  //return list of pages for this user

        $long_comment_thread_q = "SELECT xid, fromid, time, text, id FROM comment WHERE object_id=125634574117714";
        $long_comment_thread_q1 = "SELECT xid, fromid, time, text, id FROM comment WHERE object_id=391364449237";

        switch ($q) {
            case $fan_of_q:
                $data = unserialize(file_get_contents($this->data_file_path.'testFetchUserPagesThatUserIsaFanOf'));
                break;
            case $long_comment_thread_q:
                $data = unserialize(file_get_contents($this->data_file_path.'testLongCommentThread'));
                break;
            case $long_comment_thread_q1:
                $data = unserialize(file_get_contents($this->data_file_path.'testLongCommentThread'));
                break;
            default:
                break;
        }
        return $data;
    }
}
