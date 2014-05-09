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

class TestOfTwitterAgeInsight extends ThinkUpInsightUnitTestCase {

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

    public function testEarlyAdopterV1() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2010-06-11'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Achievement unlocked: @princesspeach is old-school.', $result->headline);
        // Don't assert exact number of years/months/weeks because they will change over time
        $this->assertPattern("/\@princesspeach joined Twitter/", $result->text);
        $this->assertPattern("/That\'s over/", $result->text);
        $this->assertPattern("/\% of Twitter's lifetime\!/", $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testEarlyAdopterV2() {
        TimeHelper::setTime(1399686338);
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2009-09-12'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Somebody is an early bird!', $result->headline);
        // Don't assert exact number of years/months/weeks because they will change over time
        $this->assertPattern("/\@princesspeach joined Twitter/", $result->text);
        $this->assertPattern("/That\'s over/", $result->text);
        $this->assertPattern("/\% of Twitter's lifetime\!/", $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPreObama() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2007-03-01'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Before Barack Obama joined Twitter...', $result->headline);
        // Don't assert exact number of years/months/weeks because they will change over time
        $this->assertPattern("/\@princesspeach joined Twitter/", $result->text);
        $this->assertPattern("/That\'s over/", $result->text);
        $this->assertPattern("/\% of Twitter's lifetime\!/", $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPreHashtag() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2007-08-01'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Before the hashtag, there was @princesspeach.', $result->headline);
        // Don't assert exact number of years/months/weeks because they will change over time
        $this->assertPattern("/\@princesspeach joined Twitter/", $result->text);
        $this->assertPattern("/That\'s before the hashtag was even/", $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPreBieber() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2009-02-21'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Before Justin Bieber joined Twitter...', $result->headline);
        $this->assertPattern("/\@princesspeach joined Twitter/", $result->text);
        $this->assertPattern("/That\'s over/", $result->text);
        $this->assertPattern("/\% of Twitter's lifetime\!/", $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPreIPO() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('2013-11-01'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Pre-IPO!', $result->headline);
        $this->assertPattern("/\@princesspeach joined Twitter/", $result->text);
        $this->assertPattern("/That\'s even before Twitter's initial public offering on November 7, 2013./",
            $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testLateAdopter() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser(date('Y-m-d', strtotime('-3 week', 1399686335))),
            array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Welcome to the party.', $result->headline);
        $this->assertPattern("/\@princesspeach joined Twitter/", $result->text);
        $this->assertPattern("/Take a bow\!/", $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testLateAdopterThisWeek() {
        $plugin = new TwitterAgeInsight();
        $joined_date = gmdate("Y-m-d", strtotime('-1 day'));
        $plugin->generateInsight($this->instance, $this->makeUser($joined_date, array(), 1));
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('Welcome to the party.', $result->headline);
        $this->assertPattern("/Take a bow\!/", $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testEveryoneElse() {
        $plugin = new TwitterAgeInsight();
        $plugin->generateInsight($this->instance, $this->makeUser('-6 month'), array(), 1);
        $result = $this->insight_dao->getInsight('twitter_age', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('One in 200 million...', $result->headline);
        $this->assertPattern("/\@princesspeach joined Twitter/", $result->text);
        $this->assertPattern("/That\'s over/", $result->text);
        $this->assertPattern("/\% of Twitter's lifetime\!/", $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    private function makeUser($join_date) {
        return new User(array(
            'user_name' => $this->instance->network_username, 'user_id' => '12345',
            'network' => $this->instance->network, 'joined' => $join_date,
        ));
    }
}
