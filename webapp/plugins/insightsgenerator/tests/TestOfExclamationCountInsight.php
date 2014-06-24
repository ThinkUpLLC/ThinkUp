<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFollowerCountHistoryInsight.php
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
 * Test of Follower Count History
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.CriteriaMatchInsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/exclamationcount.php';

class TestOfExclamationCountInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'screamy';
        $instance->network = 'twitter';
        $this->instance = $instance;

        TimeHelper::setTime(2); // Force one headline for most tests
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new ExclamationCountInsight();
        $this->assertIsA($insight_plugin, 'ExclamationCountInsight' );
    }

    public function testNoExclamation() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'screamy', 'network' => 'twitter',
            'post_text' => "I am bored.."));

        $insight_plugin = new ExclamationCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), 10, $today);
        $this->assertNull($result);
    }

    public function testOneExclamation() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'screamy', 'network' => 'twitter',
            'post_text' => 'Woo!', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new ExclamationCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'Say it like you mean it!');
        $this->assertEqual($result->text, "@screamy used exclamation points in 1 tweet during the last 30 days! "
            ."That's 100% of @screamy's tweets!");

        $this->assertNull($result->related_data);

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(1, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMultipleExclamations() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array( 'author_username'=> 'screamy', 'network' => 'twitter',
            'post_text' => 'Woo!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array( 'author_username'=> 'screamy', 'network' => 'twitter',
            'post_text' => 'Woo! Yeah!!!!!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array( 'author_username'=> 'screamy', 'network' => 'twitter',
            'post_text' => 'Blah.', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array( 'author_username'=> 'screamy', 'network' => 'twitter',
            'post_text' => 'Boo.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new ExclamationCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'Say it like you mean it!');
        $this->assertEqual($result->text, "@screamy used exclamation points in 2 tweets during the last 30 days! "
            ."That's 50% of @screamy's tweets!");

        $this->assertNull($result->related_data);

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(2, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMultipleExclamationsWithChart() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array( 'author_username'=> 'screamy', 'network' => 'twitter',
            'post_text' => 'Woo!!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array( 'author_username'=> 'screamy', 'network' => 'twitter',
            'post_text' => 'Woo! Yeah!!!!!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array( 'author_username'=> 'screamy', 'network' => 'twitter',
            'post_text' => 'Blah!!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array( 'author_username'=> 'screamy', 'network' => 'twitter',
            'post_text' => 'Boo.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new ExclamationCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'Say it like you mean it!');
        $this->assertEqual($result->text, "@screamy used exclamation points in 3 tweets during the last 30 days! "
            ."That's 75% of @screamy's tweets!");

        $this->assertNotNull($result->related_data);
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['bar_chart']);
        $this->assertNotNull($data['bar_chart']['rows']);
        $this->assertNotNull($data['bar_chart']['cols']);
        $this->assertEqual(count($data['bar_chart']['cols']), 2);
        $this->assertEqual($data['bar_chart']['rows'][0]['c'], array(array('v'=>'!!!!!'), array('v'=>1)));
        $this->assertEqual($data['bar_chart']['rows'][1]['c'], array(array('v'=>'!'), array('v'=>1)));
        $this->assertEqual($data['bar_chart']['rows'][2]['c'], array(array('v'=>'!!'), array('v'=>2)));

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(3, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateHeadlines() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'screamy', 'network' => 'twitter',
            'post_text' => 'Woo!', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new ExclamationCountInsight();
        $today = date ('Y-m-d');
        $headlines = array(
            '',
            '!!!OMG!!!',
            'Say it like you mean it!',
            'Get out!',
            'No way!',
            'How! Emphatic! Are! You!',
        );
        for ($i=1; $i<6; $i++) {
            TimeHelper::setTime($i);
            $insight_plugin->generateInsight($this->instance, null, $posts, 3);
            $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
            $this->assertNotNull($result);
            $this->assertEqual($result->headline, $headlines[$i]);
            $this->assertNull($result->related_data);
        }

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }


    public function testPostMatcher() {
        $insight_plugin = new ExclamationCountInsight();
        $post = new Post();

        $insight_plugin->point_chart = array();
        $post->post_text = 'Thanks!';
        $this->assertTrue($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not true');
        $this->assertEqual($insight_plugin->point_chart, array(1 => 1));


        $insight_plugin->point_chart = array();
        $post->post_text = 'Thanks';
        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not true');
        $this->assertEqual($insight_plugin->point_chart, array());

        $insight_plugin->point_chart = array();
        $post->post_text = 'Thanks!! You rock!';
        $this->assertTrue($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not true');
        $this->assertEqual($insight_plugin->point_chart, array(1 => 1, 2 => 1));

        $post->post_text = 'So Cool!!  Awesome!!!';
        $this->assertTrue($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not true');
        $this->assertEqual($insight_plugin->point_chart, array(1 => 1, 2 => 2, 3 => 1));
    }
}
