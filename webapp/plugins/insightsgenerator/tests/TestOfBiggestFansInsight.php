<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfBiggestFansInsight.php
 *
 * Copyright (c) 2012-2016 Gina Trapani
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
 * Test of BiggestFansInsight
 *
 * Test for the BiggestFansInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2016 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/biggestfans.php';

class TestOfBiggestFansInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testBiggestFansInsightTwitter() {
        // Get data ready that insight requires
        $builders = array();
        // Instance
        $posts = array();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'twitter';
        $instance->network_username = 'angel';
        $instance->network_user_id = 101;
        $instance->total_posts_in_system = 1500;
        $builders[] = FixtureBuilder::build('instances', array('id'=>10, 'network'=>'twitter',
            'network_username'=>'angel', 'network_user_id'=>101)) ;

        // Users
        $builders[] = FixtureBuilder::build('users', array('user_id'=>101, 'network'=>'twitter',
            'user_name'=>'angel'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>102, 'network'=>'twitter',
            'user_name'=>'cordelia', 'avatar' => 'http://www.virginmedia.com/images/cordelia-buffy-then.jpg',
            'full_name' => 'Cordelia Chase'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>103, 'network'=>'twitter',
            'user_name'=>'wesley'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>104, 'network'=>'twitter',
            'user_name'=>'fred', 'avatar' => 'http://38.media.tumblr.com/tumblr_m847r5Q62E1ram4jpo1_500.jpg',
            'full_name' => 'Winifred "Fred" Burkle'));

        // Posts by instance
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'network'=>'twitter', 'post_text'=>'You gonna like this', 'author_username'=>'angel', 'pub_date'=>"-1d" ));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abcd', 'author_user_id'=>101,
            'network'=>'twitter', 'post_text'=>"I'm a champion", 'author_username'=>'angel', 'pub_date'=>"-2d" ));

        // Favorites
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'fav_of_user_id'=>104, 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcd', 'author_user_id'=>101,
            'fav_of_user_id'=>104, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'fav_of_user_id'=>105, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'fav_of_user_id'=>102, 'network'=>'twitter'));

        $insight_plugin = new BiggestFansInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that 30 days insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('biggest_fans_last_30_days', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('@fred liked @angel\'s tweets the most over the last 30 days.', $result->text);
        $this->assertEqual('@fred was @angel\'s biggest fan last month', $result->headline);
        $this->assertEqual('http://38.media.tumblr.com/tumblr_m847r5Q62E1ram4jpo1_500.jpg', $result->header_image);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        // Assert that 7 days insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('biggest_fans_last_7_days', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Here\'s who liked @angel\'s tweets most over the past week.', $result->text);
        $this->assertEqual('@fred was @angel\'s biggest admirer last week', $result->headline);
        $this->assertEqual('http://38.media.tumblr.com/tumblr_m847r5Q62E1ram4jpo1_500.jpg', $result->header_image);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcd', 'author_user_id'=>101,
            'fav_of_user_id'=>102, 'network'=>'twitter'));
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $result = $insight_dao->getInsight('biggest_fans_last_30_days', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('They liked @angel\'s tweets the most over the last 30 days.', $result->text);
        $this->assertPattern('/@angel\'s biggest fans last month/', $result->headline);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight('biggest_fans_last_7_days', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Here\'s who liked @angel\'s tweets most over the past week.', $result->text);
        $this->assertEqual('@angel\'s biggest admirers last week', $result->headline);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testBiggestFansInsightFacebook() {
        // Get data ready that insight requires
        $builders = array();
        // Instance
        $posts = array();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'facebook';
        $instance->network_username = 'Angel';
        $instance->network_user_id = 101;
        $instance->total_posts_in_system = 1500;
        $builders[] = FixtureBuilder::build('instances', array('id'=>10, 'network'=>'facebook',
            'network_username'=>'Angel', 'network_user_id'=>101)) ;

        // Users
        $builders[] = FixtureBuilder::build('users', array('user_id'=>101, 'network'=>'facebook',
            'user_name'=>'angel'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>102, 'network'=>'facebook',
            'user_name'=>'cordelia', 'avatar' => 'http://www.virginmedia.com/images/cordelia-buffy-then.jpg',
            'full_name' => 'Cordelia Chase'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>103, 'network'=>'facebook',
            'user_name'=>'wesley'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>104, 'network'=>'facebook',
            'user_name'=>'Winifred', 'avatar' => 'http://38.media.tumblr.com/tumblr_m847r5Q62E1ram4jpo1_500.jpg',
            'full_name' => 'Winifred "Fred" Burkle'));

        // Posts by instance
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'network'=>'facebook', 'post_text'=>'You gonna like this', 'author_username'=>'angel', 'pub_date'=>"-1d" ));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abcd', 'author_user_id'=>101,
            'network'=>'facebook', 'post_text'=>"I'm a champion", 'author_username'=>'angel', 'pub_date'=>"-2d" ));

        // Favorites
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'fav_of_user_id'=>104, 'network'=>'facebook'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcd', 'author_user_id'=>101,
            'fav_of_user_id'=>104, 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'fav_of_user_id'=>105, 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'fav_of_user_id'=>102, 'network'=>'facebook'));

        $insight_plugin = new BiggestFansInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that 30 days insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('biggest_fans_last_30_days', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Winifred liked Angel\'s status updates the most over the last 30 days.', $result->text);
        $this->assertEqual('Winifred was Angel\'s biggest fan last month', $result->headline);
        $this->assertEqual('http://38.media.tumblr.com/tumblr_m847r5Q62E1ram4jpo1_500.jpg', $result->header_image);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        // Assert that 7 days insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('biggest_fans_last_7_days', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Here\'s who liked Angel\'s status updates most over the past week.', $result->text);
        $this->assertEqual('Winifred was Angel\'s biggest admirer last week', $result->headline);
        $this->assertEqual('http://38.media.tumblr.com/tumblr_m847r5Q62E1ram4jpo1_500.jpg', $result->header_image);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcd', 'author_user_id'=>101,
            'fav_of_user_id'=>102, 'network'=>'facebook'));
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $result = $insight_dao->getInsight('biggest_fans_last_30_days', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('They liked Angel\'s status updates the most over the last 30 days.', $result->text);
        $this->assertPattern('/Angel\'s biggest fans last month/', $result->headline);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight('biggest_fans_last_7_days', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Here\'s who liked Angel\'s status updates most over the past week.', $result->text);
        $this->assertEqual('Angel\'s biggest admirers last week', $result->headline);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}
