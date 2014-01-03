<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfStyleStatsInsight.php
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
 * Test of Style Stats Insight
 *
 * Test for StyleStatsInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/stylestats.php';

class TestOfStyleStatsInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testStyleStats() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('style_stats', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $builders = self::buildData();

        // Add post that's not a photo, link, quotation, or question
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

        $builders[] = FixtureBuilder::build('insights', array('slug'=>'PostMySQLDAO::getHotPosts',
        'date'=>date ('Y-m-d'), 'instance_id'=>1));

        // Add a question
        $post2_builder = FixtureBuilder::build('posts', array('id'=>29, 'post_id'=>'29',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'Is this post 29?',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        $post_dao = new PostMySQLDAO();
        $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy('ev', 'twitter', $count=0,
        $order_by="pub_date", $in_last_x_days = 7, $iterator = false, $is_public = false);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $stylestats_insight_plugin = new StyleStatsInsight();
        $stylestats_insight_plugin->generateInsight($instance, $last_week_of_posts, 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('style_stats', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'style_stats');
        $this->assertEqual($result->filename, 'stylestats');
        $this->assertPattern('/of \@ev\'s posts this week were photos and 1 was a question/', $result->headline);
        //sleep(1000);
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
