<?php
/**
 * Map Controller
 *
 * Renders the map for a post showing the post and its replies and retweets
 *
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class MapController extends ThinkUpController {

    /**
     * Constructor
     * @param boolean $session_started
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setViewTemplate('map.tpl');
        $this->setPageTitle('Locate Post on Map');
    }

    /**
     * Main control method
     * @return Page markup
     */
    public function control() {
        if ($this->shouldRefreshCache()) {
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

            if ($type == 'post' && $post_dao->isPostInDB($post_id, $network)) {
                $this->addHeaderJavaScript('plugins/geoencoder/assets/js/generatemap.js');
                $this->addToView('gmaps_api', $api_key);

                $post = $post_dao->getPost($post_id, $network);
                $this->addToView('post', $post);

                $post_rows =  $post_dao->getRelatedPosts($post_id, $network);
                $posts_json = $this->processLocations($post_rows, $post_id);
                $this->addToView('posts_data', $posts_json);
            } else {
                $this->addErrorMessage('No visualization data found for this post');
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
            $main_post_included = 0;
            $flag = 1;
        }
        // Generate JSON data to pass to script.
        $json_data = "var locations = ";
        $json_data .= json_encode($all_locations);
        return $json_data;
    }
}