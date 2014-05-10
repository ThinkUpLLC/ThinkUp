<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfTwitterAgeInsight.php
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
 * Test for TwitterAgeInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/twitterage.php';

class TestOfTwitterAgeInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_username = 'princesspeach';
        $this->instance->network = 'twitter';

        $this->insight_dao = DAOFactory::getDAO('InsightDAO');
        TimeHelper::setTime(1399686335);
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new TwitterAgeInsight();
        $this->assertIsA($insight_plugin, 'TwitterAgeInsight' );
    }

    public function testNoFacebook() {
        $this->instance->network = 'facebook';
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2011-10-01'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNull($result);
    }

    public function testOnlyOnce() {
        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>$this->instance->id,
            'slug'=> 'twitter_age', 'date'=>'-1d' ));

        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2011-10-01'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNull($result);
    }

    public function testEarlyAdopter() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2009-08-01'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Hey, early adopter.', $result->headline);
        $this->assertEqual("@princesspeach joined Twitter 4 years and 9 months ago, over 61% of Twitter's lifetime.",
            $result->text);
    }

    public function testEarlyAdopterV2() {
        TimeHelper::setTime(1399686338);
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2009-05-01'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Before it was cool...', $result->headline);
        $this->assertEqual("@princesspeach joined Twitter 5 years ago, over 64% of Twitter's lifetime.",
            $result->text);
    }

    public function testPreBieber() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2009-02-01'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Before Justin Bieber joined Twitter...', $result->headline);
        $this->assertEqual("@princesspeach joined Twitter 5 years and 3 months ago, over 67% of Twitter's lifetime.",
            $result->text);
    }

    public function testPreIPO() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2011-02-01'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Pre-IPO!', $result->headline);
        $this->assertEqual("@princesspeach joined Twitter 3 years and 3 months ago, over 41% of Twitter's lifetime.",
            $result->text);
    }

    public function testLateAdopter() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser(date('Y-m-d', strtotime('-3 week', 1399686335))), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Welcome to the party.', $result->headline);
        $this->assertEqual("@princesspeach joined Twitter 3 weeks ago. Take a bow!", $result->text);
    }

    public function testLateAdopterThisWeek() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('-1 day'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Welcome to the party.', $result->headline);
        $this->assertEqual("@princesspeach joined Twitter this week. Take a bow!", $result->text);
    }

    public function testEveryoneElse() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('-6 month'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('One in 200 million.', $result->headline);
        $this->assertEqual("@princesspeach joined Twitter 6 months and 25 weeks ago, over 6% of Twitter's lifetime.",
            $result->text);
    }

    private function makeUser($join_date) {
        return new User(array(
            'user_name' => $this->instance->network_username, 'user_id' => '12345',
            'network' => $this->instance->network, 'joined' => $join_date,
        ));
    }
}
