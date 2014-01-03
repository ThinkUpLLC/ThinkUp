<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfBiggestFansInsight.php
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
 * Test of BiggestFansInsight
 *
 * Test for the BiggestFansInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/biggestfans.php';

class TestOfBiggestFansInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testBiggestFansInsight() {
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
            'network_username'=>'angel'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>102, 'network'=>'twitter',
            'network_username'=>'cordelia'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>103, 'network'=>'twitter',
            'network_username'=>'wesley'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>104, 'network'=>'twitter',
            'network_username'=>'fred'));

        // Posts by instance
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'network'=>'twitter', 'post_text'=>'You gonna like this', 'author_username'=>'angel', 'pub_date'=>"-1d" ));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abcd', 'author_user_id'=>101,
            'network'=>'twitter', 'post_text'=>"I'm a champion", 'author_username'=>'angel', 'pub_date'=>"-2d" ));

        // Favorites
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'fav_of_user_id'=>104, 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'fav_of_user_id'=>105, 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'aabbccdd', 'author_user_id'=>101,
            'fav_of_user_id'=>106, 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcd', 'author_user_id'=>101,
            'fav_of_user_id'=>104, 'network'=>'twitter'));

        $insight_plugin = new BiggestFansInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);
        //sleep(1000);

        // Assert that 30 days insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('biggest_fans_last_30_days', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('They favorited @angel\'s tweets the most over the last 30 days.', $result->text);
        $this->assertPattern('/These were @angel\'s biggest fans last month./', $result->headline);


        // Assert that 7 days insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('biggest_fans_last_7_days', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Here\'s who favorited @angel\'s tweets most over the last week.', $result->text);
        $this->assertPattern('/Last week, these were/', $result->headline);
    }
}
