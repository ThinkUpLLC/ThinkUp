<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFollowerComparisonInsight.php
 *
 * Copyright (c) Chris Moyer
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
 * Test of LOL Count Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/followercomparison.php';

class TestOfFollowerComparisonInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'testy';
        $instance->network = 'twitter';
        $this->instance = $instance;
        $this->slug = 'follower_comparison';

        TimeHelper::setTime(2); // Force one headline for most tests
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new FollowerComparisonInsight();
        $this->assertIsA($insight_plugin, 'FollowerComparisonInsight' );
    }

    public function testSubFifty() {
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'999', 'follower_count'=>100));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'999', 'follower_id'=>42,
            'active' => 1, 'last_seen'=>'-1d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'888', 'follower_count'=>3000));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'888', 'follower_id'=>42,
            'active' => 1, 'last_seen'=>'-1d', 'network'=>'twitter'));

        $user = new User(array('follower_count'=>1, 'user_id' => 42, 'network' => 'twitter'));

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $insight_plugin = new FollowerComparisonInsight();
        $insight_plugin->generateInsight($this->instance, $user, array(), 3);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($this->slug, $this->instance->id, $today);
        $this->assertNull($result);
    }

    public function testOverFifty() {
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'999', 'follower_count'=>100));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'999', 'follower_id'=>42,
            'active' => 1, 'last_seen'=>'-1d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'888', 'follower_count'=>3000));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'888', 'follower_id'=>42,
            'active' => 1, 'last_seen'=>'-1d', 'network'=>'twitter'));

        $user = new User(array('follower_count'=>2000, 'user_id' => 42, 'network' => 'twitter'));

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $insight_plugin = new FollowerComparisonInsight();
        $insight_plugin->generateInsight($this->instance, $user, array(), 3);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($this->slug, $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '@testy could lend a hand');
        $this->assertEqual($result->text, '@testy has more followers than 50% of the 2 people @testy follows. That '
            . "means <strong>1</strong> of @testy's friends would reach a bigger audience if @testy retweeted them.");

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateHeadlines() {
        $builders = array();
        for ($i=0; $i<1000; $i++) {
            $builders[] = FixtureBuilder::build('users', array('user_id'=>1000+$i, 'follower_count'=>10*$i));
            $builders[] = FixtureBuilder::build('follows', array('user_id'=>1000+$i, 'follower_id'=>42,
                'active' => 1, 'last_seen'=>'-1d', 'network'=>'twitter'));
        }

        $user = new User(array('follower_count'=>8000, 'user_id' => 42, 'network' => 'twitter'));

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $insight_plugin = new FollowerComparisonInsight();

        for ($i=2; $i<=4; $i++) {
            TimeHelper::setTime($i);
            $insight_plugin->generateInsight($this->instance, $user, array(), 3);
            $today = date ('Y-m-d');
            $result = $insight_dao->getInsight($this->slug, $this->instance->id, $today);
            $this->assertNotNull($result);
            $this->debug($this->getRenderedInsightInHTML($result));
            $this->debug($this->getRenderedInsightInEmail($result));

            if ($i == 2) {
                $this->assertEqual($result->headline, '@testy could lend a hand');
                $this->assertEqual($result->text, '@testy has more followers than 80% of the 1,000 people @testy '
                    . "follows. That means <strong>800</strong> of @testy's friends would reach a bigger audience if "
                    . "@testy retweeted them.");
            } else if ($i == 3) {
                $this->assertEqual($result->headline, '@testy has it good');
                $this->assertEqual($result->text, '@testy has more followers than 80% of the people @testy '
                    . "follows. That means <strong>800</strong> of @testy's friends would reach a bigger audience if "
                    . "@testy retweeted them.");
            } else if ($i ==4 ) {
                $this->assertEqual($result->headline, "A closer look at @testy's follower count");
                $this->assertEqual($result->text, '@testy has more followers than 80% of the 1,000 people @testy '
                    . "follows. That means <strong>800</strong> of @testy's friends would reach a bigger audience "
                    . "if @testy retweeted them.");
            }
        }
    }
}
