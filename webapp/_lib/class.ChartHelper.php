<?php
/**
 *
 * ThinkUp/webapp/_lib/class.ChartHelper.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * Chart Helper
 *
 * Create expected data formats for various chart visualizations
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 *
 */
class ChartHelper {
    /**
     * Convert Posts data to JSON for use with Google Charts
     * Intended for displaying a bar graph of replies, reteets and favorites
     * @param arr $posts Array of Post objects
     * @param str @network Network that is being visualized
     * @return str JSON
     */
    public static function getPostActivityVisualizationData($posts, $network) {
        switch ($network) {
            case 'twitter':
                $post_label = 'Tweet';
                $approval_label = 'Favorites';
                $share_label = 'Retweets';
                $reply_label = 'Replies';
                break;
            case 'facebook':
            case 'facebook page':
                $post_label = 'Post';
                $approval_label = 'Likes';
                $share_label = 'Shares';
                $reply_label = 'Comments';
                break;
            case 'google+':
                $post_label = 'Post';
                $approval_label = "+1s";
                $share_label = 'Shares';
                $reply_label = 'Comments';
                break;
            default:
                $post_label = 'Post';
                $approval_label = 'Favorites';
                $share_label = 'Shares';
                $reply_label = 'Comments';
                break;
        }
        $metadata = array(
        array('type' => 'string', 'label' => $post_label),
        array('type' => 'number', 'label' => $reply_label),
        array('type' => 'number', 'label' => $share_label),
        array('type' => 'number', 'label' => $approval_label),
        );
        $result_set = array();
        foreach ($posts as $post) {
            if (isset($post->post_text) && $post->post_text != '') {
                $post_text_label = htmlspecialchars_decode(strip_tags($post->post_text), ENT_QUOTES);
            } elseif (isset($post->link->title) && $post->link->title != '') {
                $post_text_label = str_replace('|','', $post->link->title);
            } elseif (isset($post->link->url) && $post->link->url != "") {
                $post_text_label = str_replace('|','', $post->link->url);
            } else {
                $post_text_label = date("M j",  date_format (date_create($post->pub_date), 'U' ));
            }

            // Concat text and clean up any encoding snags
            $text_shortened = substr($post_text_label, 0, 100) . '...';
            // Doesn't work as expected on PHP 5.4
            //$text_clean = iconv("UTF-8", "ISO-8859-1//IGNORE", $text_shortened);
            $text_clean= mb_convert_encoding($text_shortened, 'UTF-8', 'UTF-8');

            $result_set[] = array('c' => array(
            array('v' => $text_clean),
            array('v' => intval($post->reply_count_cache)),
            array('v' => intval($post->all_retweets)),
            array('v' => intval($post->favlike_count_cache)),
            ));
        }
        return json_encode(array('rows' => $result_set, 'cols' => $metadata));
    }
}
