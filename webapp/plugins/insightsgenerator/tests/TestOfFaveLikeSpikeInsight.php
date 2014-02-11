<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFaveLikeSpikeInsight.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * Test of Fave Like Spike Insight
 *
 * Test for FaveLikeSpikeInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/favlikespike.php';

class TestOfFaveLikeSpikeInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $favelikespike_insight_plugin = new FaveLikeSpikeInsight();
        $this->assertIsA($favelikespike_insight_plugin, 'FaveLikeSpikeInsight' );
    }

    public function testInsightWithPhotoPost() {
        // Generate the insight and check the photo attributes are available for the view
        $insight_dao = new InsightMySQLDAO();

        // Insert a new post a related hot posts insight
        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>1,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d' ));

        $photo_builder = FixtureBuilder::build('photos', array('id' => 3, 'post_key' => 28, 'filter' => 'Lo-fi',
        'standard_resolution_url' => 'http://instagramstandard.com',
        'low_resolution_url' => 'http://instagramlow.com', 'thumbnail_url' => 'http://instagramthumb.com'));

        $post1_builder = FixtureBuilder::build('posts', array('id'=>28, 'post_id'=>'28',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 28',
        'source'=>'web', 'pub_date'=>'-1d', 'favlike_count_cache'=>50, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'instagram', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

        $post1 = new Post($post1_builder->columns);
        $posts[] = $post1;
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'instagram';
        $instance->network_username = 'ev';

        $favlike_insight = new FaveLikeSpikeInsight();
        $favlike_insight->generateInsight($instance, $posts, 7);
        // Check the related data for the insight has the photo object
        $check = $insight_dao->getInsight('fave_high_365_day_3', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $test = strpos($check->related_data,'Photo');
        $this->assertEqual($test, 14);
    }
}
