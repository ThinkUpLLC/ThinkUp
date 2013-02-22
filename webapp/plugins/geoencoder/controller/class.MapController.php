<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.MapController.php
 *
 * Copyright (c) 2009-2013 Ekansh Preet Singh, Mark Wilkie
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
 * Map Controller
 *
 * Renders the map for a post showing the post and its replies and retweets
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class MapController extends ThinkUpController {

    /**
     * Main control method
     * @return Page markup
     */
    public function control() {
        if ($this->shouldRefreshCache()) {
            $this->setViewTemplate(Utils::getPluginViewDirectory('geoencoder').'geoencoder.map.iframe.tpl');
            $this->setPageTitle('Locate Post on Map');
            $post_dao = DAOFactory::getDAO('PostDAO');

            $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
            $options = $plugin_option_dao->getOptionsHash('geoencoder', true);
            if (isset($options['gmaps_api_key']->option_value)) {
                $api_key = $options['gmaps_api_key']->option_value;
            } else {
                $api_key = NULL;
            }
            $network = (isset($_GET['n']))?$_GET['n']:'twitter';
            $type = (isset($_GET['t']))?$_GET['t']:'post';
            $post_id = (isset($_GET['pid']))?$_GET['pid']:'post_id';
            $post = $post_dao->getPost($post_id, $network);

            if ($type == 'post' && isset($post) && $post->is_geo_encoded == 1) {
                $this->addToView('post', $post);

                $this->addHeaderJavaScript('plugins/geoencoder/assets/js/generatemap.js');
                $this->addToView('gmaps_api', $api_key);

                $post_rows =  $post_dao->getRelatedPostsArray($post_id, $network, !$this->isLoggedIn());

                $posts_json = $this->processLocations($post_rows, $post_id);
                $this->addToView('posts_data', $posts_json);
            } else {
                $this->addErrorMessage('This post has not been geoencoded yet; cannot display map.');
            }
        }
        return $this->generateView();
    }

    /**
     * Process location data in post rows and return JSON for Google map
     * @param array $all_rows Post data
     * @param int $post_id Original post ID
     * @return str JSON of post data
     */
    protected function processLocations($all_rows, $post_id) {
        $all_locations = array();
        $include_main_post = true;
        $main_post_location = NULL;
        // Loop to group together posts with same locations
        foreach ($all_rows as $post) {
            $place = str_replace(" ", "", $post['location']);
            $place = str_replace(",", "", $place);
            if ($include_main_post && $post['post_id'] == $post_id) {
                $pub_date = explode(' ', $post['pub_date']);
                ${$place}[] = array (
                    'post_id'=>$post['post_id'],
                    'author_username'=>$post['author_username'],
                    'author_avatar'=>$post['author_avatar'],
                    'post_text'=>$post['post_text'],
                    'is_reply'=>0,
                    'is_retweet'=>0,
                    'pub_date'=>$pub_date[0],
                );
                $include_main_post = false;
                $main_post_location = $place;
            }
            $pub_date = explode(' ', $post['pub_date']);
            if ($post['post_id'] != $post_id) {
                $is_reply = 0;
                $is_retweet = 0;
                if (isset($post['in_reply_to_post_id'])) {
                    $is_reply = 1;
                } else {
                    $is_retweet = 1;
                }
                ${$place}[] = array (
                    'post_id'=>$post['post_id'],
                    'author_username'=>$post['author_username'],
                    'author_avatar'=>$post['author_avatar'],
                    'post_text'=>$post['post_text'],
                    'is_reply'=>$is_reply,
                    'is_retweet'=>$is_retweet,
                    'pub_date'=>$pub_date[0]
                );
            }
        }
        $flag = 1;
        // Loop to get names of unique locations along with their total count.
        foreach ($all_rows as $post) {
            foreach ($all_locations as $distinct_location) {
                if ($post['location'] == $distinct_location['name']) {
                    $flag = 0;
                }
            }
            if ($flag) {
                $reply_count = 0;
                $retweet_count = 0;
                $place = str_replace(" ", "", $post['location']);
                $place = str_replace(",", "", $place);
                // Calculate Reply Count for each location.
                foreach ($$place as $value) {
                    if ($value['is_reply']) {
                        $reply_count++;
                    }
                }
                // Calculate retweet count for each location.
                if ($place == $main_post_location) {
                    $main_post_included = 1;
                    $retweet_count = count($$place) - $reply_count - 1;
                } else {
                    $main_post_included = 0;
                    $retweet_count = count($$place) - $reply_count;
                }
                // Split geo into latitude and longitude.
                $geo_data = explode(',',$post['geo']);
                if (isset($geo_data[0]) && isset($geo_data[1])) {
                    $latitude = $geo_data[0];
                    $longitude = $geo_data[1];

                    $all_locations[] = array (
                    'name'=>$post['location'],
                    'latitude'=>$latitude,
                    'longitude'=>$longitude,
                    'reply_count'=>$reply_count,
                    'retweet_count'=>$retweet_count,
                    'includes_main_post'=>$main_post_included,
                    'posts'=>$$place
                    );
                }
            }
            $main_post_included = 0;
            $flag = 1;
        }
        // Generate JSON data to pass to script.
        $json_data = "var locations = ";
        $json_data .= json_encode($all_locations);
        return $json_data;
    }
}