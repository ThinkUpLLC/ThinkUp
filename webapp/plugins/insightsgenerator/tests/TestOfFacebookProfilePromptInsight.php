<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFacebookProfilePromptInsight.php
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
 * Test of FacebookPrompt  Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/facebook/model/class.FacebookInstanceMySQLDAO.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/facebook/model/class.FacebookInstance.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/facebookprofileprompt.php';

class TestOfFacebookProfilePromptInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $this->insight_dao = DAOFactory::getDAO('InsightDAO');
        $this->baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $this->builders = array();

        TimeHelper::setTime(1); // Force one headline for most tests
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new FacebookProfilePromptInsight();
        $this->assertIsA($insight_plugin, 'FacebookProfilePromptInsight' );
    }

    public function testFirstRun() {
        $instance = $this->createInstance('2011-01-12');

        $insight_plugin = new FacebookProfilePromptInsight();
        $insight_plugin->generateInsight($instance, new User(), array(), 3);

        $result = $this->insight_dao->getInsight('facebook_profile_prompt', $instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual("Is Jon Snow's Facebook profile up to date?", $result->headline);
        $this->assertEqual("Can't hurt to see if that profile info is still accurate. "
            . "(Jon Snow's Facebook profile hasn't been updated since January 12th, 2011.)", $result->text);

        $last_baseline = $this->baseline_dao->getMostRecentInsightBaseline('facebook_profile_prompted', $instance->id);
        $this->assertNotNull($last_baseline);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFirstRunFreshProfile() {
        $instance = $this->createInstance(date('Y-m-d'));

        $insight_plugin = new FacebookProfilePromptInsight();
        $insight_plugin->generateInsight($instance, new User(), array(), 3);

        $result = $this->insight_dao->getInsight('facebook_profile_prompt', $instance->id, date('Y-m-d'));
        $this->assertNull($result);

        $last_baseline = $this->baseline_dao->getMostRecentInsightBaseline('facebook_profile_prompted', $instance->id);
        $this->assertNull($last_baseline);
    }

    public function testWrongNetwork() {
        $instance = $this->createInstance('2011-01-01');

        $instance->network = 'twitter';
        $insight_plugin = new FacebookProfilePromptInsight();
        $insight_plugin->generateInsight($instance, new User(), array(), 3);

        $result = $this->insight_dao->getInsight('facebook_profile_prompt', $instance->id, date('Y-m-d'));
        $this->assertNull($result);

        $last_baseline = $this->baseline_dao->getMostRecentInsightBaseline('facebook_profile_prompted', $instance->id);
        $this->assertNull($last_baseline);
    }

    public function testRecentBaseline() {
        $instance = $this->createInstance('2011-01-01');

        $this->baseline_dao->insertInsightBaseline('facebook_profile_prompted', $instance->id, 3,
            $tendaysago = date('Y-m-d', strtotime('-10 days')));

        $insight_plugin = new FacebookProfilePromptInsight();
        $insight_plugin->generateInsight($instance, new User(), array(), 3);

        $result = $this->insight_dao->getInsight('facebook_profile_prompt', $instance->id, date('Y-m-d'));
        $this->assertNull($result);

        $last_baseline = $this->baseline_dao->getMostRecentInsightBaseline('facebook_profile_prompted', $instance->id);
        $this->assertNotNull($last_baseline);
        $this->assertEqual(3, $last_baseline->value);
        $this->assertEqual($tendaysago, $last_baseline->date);
    }

    public function testOldBaseline() {
        $instance = $this->createInstance('2011-01-01');

        $this->baseline_dao->insertInsightBaseline('facebook_profile_prompted', $instance->id, 3,
            $seventydaysago = date('Y-m-d', strtotime('-70 days')));
        $last_baseline = $this->baseline_dao->getMostRecentInsightBaseline('facebook_profile_prompted', $instance->id);

        $insight_plugin = new FacebookProfilePromptInsight();
        $insight_plugin->generateInsight($instance, new User(), array(), 3);

        $result = $this->insight_dao->getInsight('facebook_profile_prompt', $instance->id, date('Y-m-d'));
        $this->assertNotNull($result);

        $last_baseline = $this->baseline_dao->getMostRecentInsightBaseline('facebook_profile_prompted', $instance->id);
        $this->assertNotNull($last_baseline);
        $this->assertNotEqual(3, $last_baseline->value);
        $this->assertEqual(date('Y-m-d'), $last_baseline->date);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateText() {
        TimeHelper::setTime(2);
        $instance = $this->createInstance('2012-01-01');
        $insight_plugin = new FacebookProfilePromptInsight();
        $insight_plugin->generateInsight($instance, new User(), array(), 3);

        $result = $this->insight_dao->getInsight('facebook_profile_prompt', $instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertPattern("/It’s been over \d+ months since Jon Snow's profile was updated./", $result->headline);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    private function createInstance($profile_updated = null, $network = 'facebook') {
        $this->builders[] = FixtureBuilder::build('instances',
            array('id' => 1, 'network_username' => 'Jon Snow', 'network' => $network));
        $this->builders[] = FixtureBuilder::build('instances_facebook',
            array('id' => 1, 'profile_updated' => $profile_updated));
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $instance = $instance_dao->get(1);
        return $instance;
    }
}
