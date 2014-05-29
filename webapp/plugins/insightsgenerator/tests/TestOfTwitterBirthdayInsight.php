<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfTwitterBirthdayInsight.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * Test of TwitterBirthdayInsight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/twitterbirthday.php';

class TestOfTwitterBirthdayInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_user_id = 42;
        $this->instance->network_username = 'hbtome';
        $this->instance->network = 'twitter';
        $this->instance->is_public = 1;

        TimeHelper::setTime(1400745664);
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new TwitterBirthdayInsight();
        $this->assertIsA($insight_plugin, 'TwitterBirthdayInsight' );
    }

    public function testNoFacebookInsight() {
        $this->instance->network = 'facebook';
        $insight_plugin = new TwitterBirthdayInsight();
        $user = $this->makeUser('hbtome', ('2013-05-22 04:01'));
        $insight_plugin->generateInsight($this->instance, $user, array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('twitterbirthday', $this->instance->id, $today);
        $this->assertNull($result);
    }

    public function testNoInsightOnNonTwitterBD() {
        $insight_plugin = new TwitterBirthdayInsight();
        $user = $this->makeUser('hbtome', ('2013-04-22 04:01'));
        $insight_plugin->generateInsight($this->instance, $user, array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('twitterbirthday', $this->instance->id, $today);
        $this->assertNull($result);

        $user = $this->makeUser('hbtome', ('2011-01-02 01:01'));
        $insight_plugin->generateInsight($this->instance, $user, array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('twitterbirthday', $this->instance->id, $today);
        $this->assertNull($result);
    }

    public function testNormalUse() {
        $builders = array();
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'2', 'follower_id'=>'1',
            'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'2', 'network' => 'twitter','is_protected'=>0,
            'network' => 'twitter',
            'user_name' => 'first_user', 'joined' => ('2013-05-20 01:01')));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'3', 'follower_id'=>'1',
            'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'3', 'network' => 'twitter','is_protected'=>0,
            'network' => 'twitter',
            'user_name' => 'second_user', 'joined' => ('2013-05-20 18:01')));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'4', 'follower_id'=>'1',
            'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'4', 'network' => 'twitter','is_protected'=>0,
            'network' => 'twitter',
            'user_name' => 'third_user', 'joined' => ('2013-05-23 04:01')));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'5', 'follower_id'=>'1',
            'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'5', 'network' => 'twitter','is_protected'=>0,
            'network' => 'twitter',
            'user_name' => 'fourth_user', 'joined' => ('2013-05-25 03:12')));

        $insight_plugin = new TwitterBirthdayInsight();
        $user = $this->makeUser('hbtome', ('2013-05-22 04:01'), 1);
        $insight_plugin->generateInsight($this->instance, $user, array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('twitterbirthday', $this->instance->id, $today);
        $this->assertEqual('Happy Twitter birthday!', $result->headline);
        $this->assertEqual('@hbtome joined Twitter 1 year ago today. @second_user just beat @hbtome, joining '
           .'1 day earlier, and @third_user was a little slower, getting on Twitter 1 day later.', $result->text);

        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 2);
        $this->assertEqual($data['people'][0]->username, 'second_user');
        $this->assertEqual($data['people'][1]->username, 'third_user');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testNoFollowers() {
        $insight_plugin = new TwitterBirthdayInsight();
        $user = $this->makeUser('hbtome', ('2013-05-22 04:01'));
        $insight_plugin->generateInsight($this->instance, $user, array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('twitterbirthday', 10, $today);
        $this->assertEqual('Happy Twitter birthday!', $result->headline);
        $this->assertEqual('@hbtome joined Twitter 1 year ago today.', $result->text);
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 0);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testNoFollowersAfter() {
        $builders = array();
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'2', 'follower_id'=>'1',
            'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'2', 'network' => 'twitter','is_protected'=>0,
            'network' => 'twitter',
            'user_name' => 'first_user', 'joined' => ('2013-05-20 12:19')));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'3', 'follower_id'=>'1',
            'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'3', 'network' => 'twitter','is_protected'=>0,
            'network' => 'twitter',
            'user_name' => 'second_user', 'joined' => ('2013-05-20 18:01')));

        $insight_plugin = new TwitterBirthdayInsight();
        $user = $this->makeUser('hbtome', ('2013-05-22 04:01'), 1);
        $insight_plugin->generateInsight($this->instance, $user, array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('twitterbirthday', $this->instance->id, $today);
        $this->assertEqual('Happy Twitter birthday!', $result->headline);
        $this->assertEqual('@hbtome joined Twitter 1 year ago today. @second_user just beat @hbtome, joining '
           .'1 day earlier.', $result->text);

        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);
        $this->assertEqual($data['people'][0]->username, 'second_user');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    /**
     * Create a test user.
     * @param username $username The user's username
     * @param str $joined Joined date for the user
     * @return User
     */
    private function makeUser($username, $joined, $user_id = 999) {
        $user = new User();
        $user->username = $username;
        $user->joined = $joined;
        $user->user_id = $user_id;
        $user->network = "twitter";
        return $user;
    }
}
