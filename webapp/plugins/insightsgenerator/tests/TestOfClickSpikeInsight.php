<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfClickSpikeInsight.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * Test of Click Spike Insight
 *
 * Test for ClickSpikeInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/clickspike.php';

class TestOfClickSpikeInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function test7DayClickHigh() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $yesterday = date ('Y-m-d', strtotime('-1 day'));
        $result = $insight_dao->getInsight('click_high_7_day_28', 1, $yesterday);
        $this->assertNull($result);

        $builders = self::buildData();

        // Add post with a link that has lots of clicks
        $post1_builder = FixtureBuilder::build('posts', array('id'=>28, 'post_id'=>'28',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 28',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        $link1_builder = FixtureBuilder::build('links', array('id'=>28, 'post_key'=>'28',
        'short_url'=>'http://bit.ly/blah', 'expanded_url'=>'http://expandedurl.com/asfasdfadsf/adsfa'
        ));

        $builders[] = FixtureBuilder::build('links_short', array('id'=>28, 'link_id'=>'28',
        'short_url'=>'http://bit.ly/blah'.$counter, 'click_count'=>7609 ));

        $builders[] = FixtureBuilder::build('insights', array('slug'=>'ShortLinkMySQLDAO::getRecentClickStats',
        'date'=>$yesterday, 'instance_id'=>1, 'related_data'=>serialize('sample click spike data')));

        // Get data ready that insight requires
        $post1_object = new Post($post1_builder->columns);
        $link1_object = new Link($link1_builder->columns);
        $post1_object->links = array($link1_object);

        $posts = array();
        $posts[] = $post1_object;
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $clickspike_insight_plugin = new ClickSpikeInsight();
        $clickspike_insight_plugin->generateInsight($instance, $posts, 3);
        //sleep(1000);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('click_high_7_day_28', 1, $yesterday);
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'click_high_7_day_28');
        $this->assertEqual($result->filename, 'clickspike');
        $this->assertPattern('/Viewers clicked \@ev\'s link \<strong\>7,609 times\<\/strong\>/', $result->headline);
    }

    private function buildData() {
        $builders = array();

        //add post authors
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'13', 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));

        //Add 25 links with short URL click counts
        $counter = 2;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        while ($counter < 27) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
            'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>'web', 'pub_date'=>'-'.$counter.'d', 'reply_count_cache'=>0, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

            $builders[] = FixtureBuilder::build('links', array('id'=>$counter, 'post_key'=>$counter,
            'short_url'=>'http://bit.ly/blah', 'expanded_url'=>'http://expandedurl.com/asfasdfadsf/adsfa'
            ));

            $builders[] = FixtureBuilder::build('links_short', array('id'=>$counter, 'link_id'=>$counter,
            'short_url'=>'http://bit.ly/blah'.$counter, 'click_count'=>$counter+2
            ));
            $counter++;
        }

        return $builders;
    }
}
