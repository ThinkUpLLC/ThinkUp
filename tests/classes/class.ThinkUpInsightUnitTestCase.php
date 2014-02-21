<?php
/**
 *
 * ThinkUp/tests/classes/class.ThinkUpInsightUnitTestCase.php
 *
 * Copyright (c) 2014 Gina Trapani
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
 * ThinkUp Insight Unit Test Case
 *
 * Adds database support to the basic unit test case, for tests that need ThinkUp's database structure.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ThinkUpInsightUnitTestCase extends ThinkUpUnitTestCase {

    /**
     * Set up necessary owner, instance, user, and owner_instance data to see fully-rendered insight markup
     * on debug.
     * @param Instance $instance Must have id, network, and network_username set
     * @return arr FixtureBuilders
     */
    protected function setUpPublicInsight(Instance $instance) {
        if (!isset($instance->network_user_id)) {
            $instance->network_user_id = '1001';
        }

        $builders = array();

        //Owner
        $pwd = ThinkUpTestLoginHelper::hashPasswordUsingCurrentMethod('pwd3', 'salt');
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'tuuser1@example.com', 'is_activated'=>1, 'pwd'=>$pwd, 'pwd_salt'=>'salt'));

        //Public instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>$instance->id,
        'network_user_id'=>$instance->network_user_id, 'network_username'=>$instance->network_username ,
        'network'=>$instance->network, 'network_viewer_id'=>'10', 'crawler_last_run'=>'1988-01-20 12:00:00',
        'is_active'=>1, 'is_public'=>1, 'posts_per_day'=>11, 'posts_per_week'=>77));

        //Owner instance
        $builders[] = FixtureBuilder::build('owner_instances', array('instance_id' => $instance->id, 'owner_id'=>1) );

        //User
        $builders[] = FixtureBuilder::build('users', array('user_id' => $instance->network_user_id,
            'network'=>$instance->network) );

        return $builders;
    }

    /**
     * Get serialized list of posts for insight tests.
     * @param str $network
     * @return str Serialized related_data
     */
    protected function getRelatedDataListOfPosts($network='twitter') {
        $posts = array();
        $i = 3;
        while ($i > 0) {
            $post = new Post(array('id'=>1, 'author_user_id'=>$network.'-20', 'author_username'=>'UserAt'.$network,
            'author_fullname'=>"No One", 'author_avatar'=>'http://example.com/yo.jpg', 'source'=>'TweetDeck',
            'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'',
            'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'',
            'retweet_count_api' =>'', 'old_retweet_count_cache' => '', 'in_rt_of_user_id' =>'',
            'post_id'=>'9021481076', 'is_protected'=>1, 'place_id' => 'ece7b97d252718cc', 'favlike_count_cache'=>0,
            'post_text'=>'I like cookies', 'network'=>$network, 'geo'=>'', 'place'=>'', 'location'=>'',
            'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0));
            $posts[] = $post;
            $i--;
        }
        $related_data = array();
        $related_data["posts"] = $posts;
        return serialize($related_data);
    }

    /**
     * Get serialized list of users for insight tests.
     * @param str $network
     * @return str Serialized related_data
     */
    protected function getRelatedDataListOfUsers($network='twitter') {
        $users = array();
        $i = 3;
        while ($i > 0) {
            $user_array = array('id'=>$i, 'user_id'=>$network.'-'.$i, 'user_name'=>'ginatrapani'.$i,
            'full_name'=>'Gina Trapani', 'avatar'=>'http://example.com/avatar.jpg', 'location'=>'NYC',
            'description'=>'Blogger', 'url'=>'http://ginatrapani.org', 'is_verified'=>1, 'is_protected'=>0,
            'follower_count'=>5000, 'post_count'=>1000, 'joined'=>'2007-03-06 13:48:05', 'network'=>$network,
            'last_post_id'=>'abc102');
            $user = new User($user_array, 'Test Insert');
            $users[] = $user;
            $i--;
        }
        $related_data = array();
        $related_data["people"] = $users;
        return serialize($related_data);
    }
}
