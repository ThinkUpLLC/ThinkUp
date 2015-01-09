<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfLocationAwarenessInsight.php
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
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/locationawareness.php';

class TestOfLocationAwarenessInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'supermayor';
        $instance->network = 'twitter';
        $this->instance = $instance;

        $this->insight_dao = DAOFactory::getDAO('InsightDAO');

        TimeHelper::setTime(2); // Force one headline for most tests
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new LocationAwarenessInsight();
        $this->assertIsA($insight_plugin, 'LocationAwarenessInsight' );
    }

    public function testWeeklySomePosts() {
        $this->instance->network = 'test_no_monthly';
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost(true, $i);
            $builders[] = $this->generatePost(false, $i);
        }

        $insight_plugin = new LocationAwarenessInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "5 location shares");
        $this->assertEqual($result->text, "Last week, supermayor included precise location details in 5 posts."
            ." That's roughly 3 hours anyone could have found supermayor in person.");

        $data = unserialize($result->related_data);
        $this->assertNotNull($data['hero_image']);
        $this->assertNull($data['button']);
        $this->assertIsA($data['map_points'], 'Array');
        $this->assertequal(count($data['map_points']), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMapPoints() {
        $builders = array();
        $builders[] = FixtureBuilder::build('posts', array( 'geo' => '42.886927111,-78.877383111',
            'network' => $this->instance->network, 'author_username' => $this->instance->network_username,
            'post_text' => 'Hello Buffalo', 'pub_date' => '-2d'));
        $builders[] = FixtureBuilder::build('posts', array( 'geo' => '34.425323111,-103.191544111',
            'network' => $this->instance->network, 'author_username' => $this->instance->network_username,
            'post_text' => 'I am at the mall of America', 'pub_date' => '-3d'));
        $builders[] = FixtureBuilder::build('posts', array( 'geo' => '34.425323111,-103.191544111',
            'network' => $this->instance->network, 'author_username' => $this->instance->network_username,
            'post_text' => 'Back at the mall', 'pub_date' => '-3d'));
        $builders[] = FixtureBuilder::build('posts', array( 'geo' => '34.425323111,-103.191544111',
            'network' => $this->instance->network, 'author_username' => $this->instance->network_username,
            'post_text' => 'Still here.', 'pub_date' => '-3d'));
        $builders[] = FixtureBuilder::build('posts', array( 'geo' => '34.425323111,-103.191544111',
            'network' => $this->instance->network, 'author_username' => $this->instance->network_username,
            'post_text' => 'I love shopping', 'pub_date' => '-3d'));
        $builders[] = FixtureBuilder::build('posts', array( 'geo' => '34.425323111,-103.191544111',
            'network' => $this->instance->network, 'author_username' => $this->instance->network_username,
            'post_text' => 'Shopping is my life', 'pub_date' => '-3d'));

        $insight_plugin = new LocationAwarenessInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "6 location shares");
        $this->assertEqual($result->text, "Last month, @supermayor included precise location details in 6 tweets. "
            . "That's roughly 4 hours anyone could have found @supermayor in person.");

        $data = unserialize($result->related_data);
        $this->assertIsA($data, 'Array');
        $this->assertIsA($data['map_points'], 'Array');
        $this->assertEqual(count($data['map_points']), 2);
        $this->assertEqual($data['map_points'][0], '42.886927111,-78.877383111');
        $this->assertEqual($data['map_points'][1], '34.425323111,-103.191544111');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWeeklySomePostsOneDay() {
        $this->instance->network = 'test_no_monthly';
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost(true, 1);
            $builders[] = $this->generatePost(false, $i);
        }

        $insight_plugin = new LocationAwarenessInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "6 location shares");
        $this->assertEqual($result->text, "Last week, supermayor included precise location details in 6 posts. "
            . "That's roughly 4 hours anyone could have found supermayor in person.");

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWeeklyTooFewPost() {
        $this->instance->network = 'test_no_monthly';
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost(false, $i);
        }
        $builders[] = $this->generatePost(true, 1);
        $builders[] = $this->generatePost(true, 1);
        $builders[] = $this->generatePost(true, 1);

        $insight_plugin = new LocationAwarenessInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNull($result);
    }

    public function testMonthlyNoPosts() {
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost(false, $i);
        }

        $insight_plugin = new LocationAwarenessInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNull($result);
    }

    public function testMonthlyOnePost() {
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost(false, $i);
        }
        $builders[] = $this->generatePost(true, 1);

        $insight_plugin = new LocationAwarenessInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "1 location share");
        $this->assertEqual($result->text, "Last month, @supermayor included precise location details in 1 tweet. "
            . "That's roughly 45 minutes anyone could have found @supermayor in person.");

        $data = unserialize($result->related_data);
        $this->assertNotNull($data['hero_image']);
        $this->assertIsA($data['button'],'Array');
        $this->assertEqual($data['button']['url'], 'https://twitter.com/settings/security');
        $this->assertEqual($data['button']['label'], 'Update location settings');
        $this->assertIsA($data['map_points'], 'Array');
        $this->assertequal(count($data['map_points']), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMonthlySomePost() {
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost(false, $i);
            $builders[] = $this->generatePost(true, $i*2);
        }

        $insight_plugin = new LocationAwarenessInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $this->insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "5 location shares");
        $this->assertEqual($result->text, "Last month, @supermayor included precise location details in 5 tweets. "
            . "That's roughly 3 hours anyone could have found @supermayor in person.");

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateText() {
        $builders = array();
        for ($i=0; $i<6; $i++) {
            $builders[] = $this->generatePost(false, $i);
        }
        $builders[] = $this->generatePost(true, $i*2);

        $insight_plugin = new LocationAwarenessInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username, $this->instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7,
            $iterator = false, $is_public = false);


        $headlines = array(
            null,
            "@supermayor has been sharing location data",
            "1 location share",
            "45 minutes on the map",
            "@supermayor has been spotted in the wild",
        );

        $text = array(
            null,
            "@supermayor added precise location information to 1 tweet last month.",
            "Last month, @supermayor included precise location details in 1 tweet. "
                . "That's roughly 45 minutes anyone could have found @supermayor in person.",
            "@supermayor added precise location information to 1 tweet last month.",
            "Last month, @supermayor included precise location details in 1 tweet. "
                . "That's roughly 45 minutes anyone could have found @supermayor in person."
        );
        for ($i=1; $i<=4; $i++) {
            TimeHelper::setTime($i);
            $insight_plugin->generateInsight($this->instance, null, $posts, 3);
            $today = date('Y-m-d');
            $result = $this->insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
            $this->assertNotNull($result);
            $this->assertEqual($result->headline, $headlines[$i]);
            $this->assertEqual($result->text, $text[$i]);
            $this->debug($this->getRenderedInsightInHTML($result));
            $this->debug($this->getRenderedInsightInEmail($result));
        }

        // test headlines again with more location shares
        $builders[] = $this->generatePost(true, $i*2);
        $builders[] = $this->generatePost(true, $i*2+1);
        $headlines[2] = "3 location shares";
        $headlines[3] = "2 hours on the map";
        for ($i=1; $i<=4; $i++) {
            TimeHelper::setTime($i);
            $insight_plugin->generateInsight($this->instance, null, $posts, 3);
            $today = date('Y-m-d');
            $result = $this->insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
            $this->assertNotNull($result);
            $this->assertEqual($result->headline, $headlines[$i]);
            $this->debug($this->getRenderedInsightInHTML($result));
            $this->debug($this->getRenderedInsightInEmail($result));
        }
    }

    public function testIsPreciselyLocated() {
        $insight_plugin = new LocationAwarenessInsight();
        $p = new Post();
        $this->assertFalse($insight_plugin->isPreciselyLocated($p));

        $p->geo = '';
        $this->assertFalse($insight_plugin->isPreciselyLocated($p));

        $p->geo = 'buffalo, ny';
        $this->assertFalse($insight_plugin->isPreciselyLocated($p));

        $p->geo = '1,2';
        $this->assertFalse($insight_plugin->isPreciselyLocated($p));

        $p->geo = '1.1234567,2.1234567';
        $this->assertFalse($insight_plugin->isPreciselyLocated($p));

        $p->geo = '1.1234567,2.12345678';
        $this->assertTrue($insight_plugin->isPreciselyLocated($p));

        $p->geo = '1.12345678,2.12345678';
        $this->assertTrue($insight_plugin->isPreciselyLocated($p));

        $p->geo = '-121.12345678,2.2';
        $this->assertTrue($insight_plugin->isPreciselyLocated($p));

        $p->geo = '1.1234567,-2.1232345678';
        $this->assertTrue($insight_plugin->isPreciselyLocated($p));

        $p->geo = '1.1234567,2.12348125678';
        $this->assertTrue($insight_plugin->isPreciselyLocated($p));
    }

    private function generatePost($is_geo, $days_ago) {
        static $i = 1;
        return FixtureBuilder::build('posts', array(
            'post_id' => $i++,
            'geo' => $is_geo ? '1.12345678,2.12345678' : '-1.123456,-2.1234567',
            'network' => $this->instance->network,
            'author_username' => $this->instance->network_username,
            'post_text' => $is_geo ? 'Look where I am!' : 'I am hiding.',
            'pub_date' => (-1*$days_ago).'d'

        ));
    }
}
