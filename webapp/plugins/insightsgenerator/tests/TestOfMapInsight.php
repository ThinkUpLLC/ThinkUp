<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfMapInsight.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * Test of MapInsight
 *
 * Test for the MapInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/map.php';

class TestOfMapInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testMapInsightPostGeoencoded() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $yesterday = date ('Y-m-d', strtotime('-1 day'));
        $result = $insight_dao->getInsight('geoencoded_replies', 10, $yesterday);
        $this->assertNull($result);

        // Activate geoencoder plugin
        $plugin_builder = FixtureBuilder::build('plugins', array('folder_name' => 'geoencoder', 'is_active' => 1) );
        $plugin_id = $plugin_builder->columns['last_insert_id'];
        // Set plugin options
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-' . $plugin_id;
        $plugin_options_builder = FixtureBuilder::build('options',
        array('namespace'=>$namespace, 'option_name' => 'gmaps_api_key', 'option_value' => "1234"));

        // Insert post from yesterday that is geoencoded and has more than 5 reply count cache
        $counter = 1;
        $post1_builder = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
        'author_user_id'=>13, 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter, 'source'=>$source, 'pub_date'=>'-1d',
        'reply_count_cache'=>6, 'is_protected'=>0, 'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter',
        'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null, 'in_reply_to_post_id'=>null,
        'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>1, 'in_reply_to_user_id'=>null));
        $counter++;
        $post1_object = new Post($post1_builder->columns);

        // Get data ready that insight requires
        $posts = array();
        $posts[] = $post1_object;
        $instance = new Instance();
        $instance->id = 10;
        $map_insight_plugin = new MapInsight();
        $map_insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight did not get inserted because replies don't exist / haven't been geoencoded
        $insight_dao = new InsightMySQLDAO();
        $yesterday = date ('Y-m-d', strtotime('-1 day'));
        $result = $insight_dao->getInsight('geoencoded_replies', 10, $yesterday);
        $this->assertNull($result);
    }

    public function testMapInsightPostWithGeoencodedReplies() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $two_days_ago = date ('Y-m-d', strtotime('-2 day'));
        $result = $insight_dao->getInsight('geoencoded_replies', 10, $two_days_ago);
        $this->assertNull($result);

        // Activate geoencoder plugin
        $plugin_builder = FixtureBuilder::build('plugins', array('folder_name' => 'geoencoder', 'is_active' => 1) );
        $plugin_id = $plugin_builder->columns['last_insert_id'];
        // Set plugin options
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-' . $plugin_id;
        $plugin_options_builder = FixtureBuilder::build('options',
        array('namespace'=>$namespace, 'option_name' => 'gmaps_api_key', 'option_value' => "1234"));

        // Insert post from yesterday that is geoencoded and has more than 5 reply count cache
        $counter = 1;
        $post1_builder = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
        'author_user_id'=>13, 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter, 'source'=>$source, 'pub_date'=>'-2d',
        'reply_count_cache'=>6, 'is_protected'=>0, 'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter',
        'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null, 'in_reply_to_post_id'=>null,
        'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>1, 'in_reply_to_user_id'=>null));
        $counter++;
        $post1_object = new Post($post1_builder->columns);

        // Insert geoencoded replies
        $reply_builders = array();
        while ($counter < 7) {
            $reply_builders[] = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
            'author_user_id'=>14, 'author_username'=>'amelia', 'author_fullname'=>'Amelia Earhart',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter, 'source'=>$source, 'pub_date'=>'-1d',
            'reply_count_cache'=>6, 'is_protected'=>0, 'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter',
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null, 'in_reply_to_post_id'=>1,
            'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>1, 'in_reply_to_user_id'=>null));
            $counter++;
        }
        // Insert replying user
        $reply_builders[] = FixtureBuilder::build('users', array('user_id'=>14, 'user_name'=>'amelia',
        'full_name'=>'Amelia Earhart', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'last_updated'=>'2005-01-01 13:01:00', 'network'=>'twitter'));

        // Get data ready that insight requires
        $posts = array();
        $posts[] = $post1_object;
        $instance = new Instance();
        $instance->id = 10;
        $map_insight_plugin = new MapInsight();
        $map_insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $two_days_ago = date ('Y-m-d', strtotime('-2 day'));
        $result = $insight_dao->getInsight('geoencoded_replies', 10, $two_days_ago);
        $this->assertNotNull($result);
        $this->assertIsA($result, 'Insight');
        $this->assertEqual($result->instance_id, 10);
        $this->assertEqual($result->slug, 'geoencoded_replies');
        $this->assertEqual($result->prefix, 'Going global!');
        $this->assertEqual($result->filename, 'map');
    }

    public function testMapInsightPostWithNonGeoencodedReplies() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $two_days_ago = date ('Y-m-d', strtotime('-2 day'));
        $result = $insight_dao->getInsight('geoencoded_replies', 10, $two_days_ago);
        $this->assertNull($result);

        // Activate geoencoder plugin
        $plugin_builder = FixtureBuilder::build('plugins', array('folder_name' => 'geoencoder', 'is_active' => 1) );
        $plugin_id = $plugin_builder->columns['last_insert_id'];
        // Set plugin options
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-' . $plugin_id;
        $plugin_options_builder = FixtureBuilder::build('options',
        array('namespace'=>$namespace, 'option_name' => 'gmaps_api_key', 'option_value' => "1234"));

        // Insert post from yesterday that is geoencoded and has more than 5 reply count cache
        $counter = 1;
        $post1_builder = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
        'author_user_id'=>13, 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter, 'source'=>$source, 'pub_date'=>'-2d',
        'reply_count_cache'=>6, 'is_protected'=>0, 'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter',
        'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null, 'in_reply_to_post_id'=>null,
        'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>1, 'in_reply_to_user_id'=>null));
        $counter++;
        $post1_object = new Post($post1_builder->columns);

        // Insert geoencoded replies
        $reply_builders = array();
        while ($counter < 7) {
            $reply_builders[] = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
            'author_user_id'=>14, 'author_username'=>'amelia', 'author_fullname'=>'Amelia Earhart',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter, 'source'=>$source, 'pub_date'=>'-1d',
            'reply_count_cache'=>6, 'is_protected'=>0, 'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter',
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null, 'in_reply_to_post_id'=>1,
            'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id'=>null));
            $counter++;
        }
        // Insert replying user
        $reply_builders[] = FixtureBuilder::build('users', array('user_id'=>14, 'user_name'=>'amelia',
        'full_name'=>'Amelia Earhart', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'last_updated'=>'2005-01-01 13:01:00', 'network'=>'twitter'));

        // Get data ready that insight requires
        $posts = array();
        $posts[] = $post1_object;
        $instance = new Instance();
        $instance->id = 10;
        $map_insight_plugin = new MapInsight();
        $map_insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $two_days_ago = date ('Y-m-d', strtotime('-2 day'));
        $result = $insight_dao->getInsight('geoencoded_replies', 10, $two_days_ago);
        $this->assertNull($result);
    }

}
