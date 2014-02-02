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
 * Test of Flashback Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/flashbacks.php';

class TestOfFlashbackInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $flashback_insight_plugin = new FlashbackInsight();
        $this->assertIsA($flashback_insight_plugin, 'FlashbackInsight' );
    }

    public function testFlashbackInsightForTwitter() {
        // Get data ready that insight requires
        $builders = self::buildData();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = '7654321';
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FlashbackInsight();
        $insight_plugin->generateInsight($instance, $last_week_of_posts, 3);
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('posts_on_this_day_popular_flashback', 10, $today);
        $fav_posts = unserialize($result->related_data);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/On this day&hellip;/', $result->headline);
        $possible_text = array("This was @testeriffic's most popular tweet <strong>1 year ago</strong>.",
        "On this day in 2013, this was @testeriffic's most popular tweet.");
        $this->assertTrue(in_array( $result->text, $possible_text));
        $this->assertIsA($fav_posts, "array");
        $this->assertIsA($fav_posts["posts"][0], "Post");
        $this->assertEqual(count($fav_posts), 1);
    }

    public function testFlashbackInsightForFacebook() {
        // Get data ready that insight requires
        $builders = self::buildData();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = '765432fb';
        $instance->network_username = 'testeriffic';
        $instance->network = 'facebook';
        $insight_plugin = new FlashbackInsight();
        $insight_plugin->generateInsight($instance, $last_week_of_posts, 3);
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('posts_on_this_day_popular_flashback', 10, $today);
        $fav_posts = unserialize($result->related_data);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/On this day&hellip;/', $result->headline);
        $possible_text = array("This was testeriffic's most popular status update <strong>1 year ago</strong>.",
        "On this day in 2013, this was testeriffic's most popular status update.");
        $this->assertTrue(in_array( $result->text, $possible_text));
        $this->assertIsA($fav_posts, "array");
        $this->assertIsA($fav_posts["posts"][0], "Post");
        $this->assertEqual(count($fav_posts), 1);
    }

    private function buildData() {
        $builders = array();

        $now = date('Y-m-d H:i:s');
        $yesterday = date('Y-m-d H:i:s', strtotime('yesterday'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>133, 'post_id'=>133, 'author_user_id'=>'7654321',
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a simple post.', 'source'=>'web',
        'pub_date'=>'-730d', 'reply_count_cache'=>50, 'is_protected'=>0, 'in_reply_to_post_id'=>null,
        'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'in_rt_of_user_id'=>null));

        $builders[] = FixtureBuilder::build('posts', array('id'=>134, 'post_id'=>134, 'author_user_id'=>'7654321',
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
        'pub_date'=>'-365d', 'reply_count_cache'=>60, 'is_protected'=>0, 'in_reply_to_post_id'=>null,
        'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'in_rt_of_user_id'=>null));

        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135, 'author_user_id'=>'7654321',
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is an old post http://t.co/aMHh5XHGfS with a link.', 'source'=>'web',
        'pub_date'=>'-365d', 'reply_count_cache'=>70, 'is_protected'=>0, 'in_reply_to_post_id'=>null,
        'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'in_rt_of_user_id'=>null));

        $builders[] = FixtureBuilder::build('posts', array('id'=>136, 'post_id'=>136, 'author_user_id'=>'765432fb',
        'author_username'=>'testeriffic', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'facebook', 'post_text'=>'This is a simple post.', 'source'=>'web',
        'pub_date'=>'-730d', 'reply_count_cache'=>50, 'is_protected'=>0, 'in_reply_to_post_id'=>null,
        'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'in_rt_of_user_id'=>null));

        $builders[] = FixtureBuilder::build('posts', array('id'=>137, 'post_id'=>137, 'author_user_id'=>'765432fb',
        'author_username'=>'testeriffic', 'author_fullname'=>'facebook User', 'author_avatar'=>'avatar.jpg',
        'network'=>'facebook', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
        'pub_date'=>'-365d', 'reply_count_cache'=>60, 'is_protected'=>0, 'in_reply_to_post_id'=>null,
        'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'in_rt_of_user_id'=>null));

        $builders[] = FixtureBuilder::build('posts', array('id'=>138, 'post_id'=>138, 'author_user_id'=>'765432fb',
        'author_username'=>'testeriffic', 'author_fullname'=>'facebook User', 'author_avatar'=>'avatar.jpg',
        'network'=>'facebook', 'post_text'=>'This is an old post http://t.co/aMHh5XHGfS with a link.', 'source'=>'web',
        'pub_date'=>'-365d', 'reply_count_cache'=>70, 'is_protected'=>0, 'in_reply_to_post_id'=>null,
        'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'in_rt_of_user_id'=>null));

        return $builders;
    }
}
