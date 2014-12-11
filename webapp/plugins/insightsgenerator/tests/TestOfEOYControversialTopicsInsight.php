<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYControversialTopicsInsight.php
 *
 * Copyright (c) 2012-2014 Gina Trapani
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
 * Test for the EOYControversialTopicsInsight class.
 *
 * Copyright (c) 2014 Chris Moyer
 *
 * @author Chris Moyer chris@inarow.net
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Adam Pash
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoycontroversialtopics.php';

class TestOfEOYControversialTopicsInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'Your Rude Uncle';
        $instance->author_id = '18';
        $instance->network_user_id = '18';
        $instance->network = 'facebook';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testNoTopics() {
        $insight_plugin = new EOYControversialTopicsInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Your Rude Uncle kept the drama off of Facebook");
        $this->assertEqual($result->text, "Your Rude Uncle avoided contentious topics like immigration and ebola, ".
            "which can be a great way to keep Facebook a little more friendly.");
        $data = unserialize($result->related_data);
        $this->assertNull($data['posts']);

        $this->dumpRenderedInsight($result, $this->instance, "No Topics");

        /*
         */
    }

    public function testNoTopicsQualified() {
        $this->instance->last_post_id = '99999';
        $builders[] = FixtureBuilder::build('posts',
            array( 'post_id' => '99999', 'post_text' => 'This is my old post',
                'pub_date' => date('Y-m-d', strtotime('June 15')),
                'author_username' => $this->instance->network_username, 'network' => $this->instance->network,
                'favlike_count_cache' => 25
            )
        );

        $insight_plugin = new EOYControversialTopicsInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Your Rude Uncle kept the drama off of Facebook");
        $this->assertEqual($result->text, "Your Rude Uncle avoided contentious topics like immigration and ebola, ".
            "which can be a great way to keep Facebook a little more friendly (at least since June).");
        $data = unserialize($result->related_data);
        $this->assertNull($data['posts']);

        $this->dumpRenderedInsight($result, $this->instance, "No Topics, Qualified");
    }

    public function testOneTopic() {
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-02-01', 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'This Ferguson stuff is crazy!',
            )
        );
        $insight_plugin = new EOYControversialTopicsInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Your Rude Uncle wasn't afraid of 2014's big issues");
        $this->assertEqual($result->text, "Your Rude Uncle talked about and Ferguson in 2014. It's great to use "
            . "Facebook to address things that matter.");
        $data = unserialize($result->related_data);
        $this->assertEqual(1, count($data['posts']));

        $this->dumpRenderedInsight($result, $this->instance, "One Topic");
    }

    public function testTwoTopics() {
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-02-01', 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'This Ferguson stuff is crazy!',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-02-05', 'post_id' => 2, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'I don\'t understand immigration.',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-02-01', 'post_id' => 3, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'I should be in charge of immigration.',
            )
        );
        $insight_plugin = new EOYControversialTopicsInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Your Rude Uncle wasn't afraid of 2014's big issues");
        $this->assertEqual($result->text, "Your Rude Uncle talked about Ferguson and immigration in 2014. "
            . "It's great to use Facebook to address things that matter.");
        $data = unserialize($result->related_data);
        $this->assertEqual(2, count($data['posts']));
        $this->assertEqual($data['posts'][0]->post_text, 'This Ferguson stuff is crazy!');
        $this->assertEqual($data['posts'][1]->post_text, 'I should be in charge of immigration.');

        $this->dumpRenderedInsight($result, $this->instance, "Two Topics");
    }

    public function testThreeTopics() {
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-02-01', 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'This Ferguson stuff is crazy!',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-04-05', 'post_id' => 2, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'I don\'t understand immigration.',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-01-01', 'post_id' => 3, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'What is an ISIS?',
            )
        );
        $insight_plugin = new EOYControversialTopicsInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Your Rude Uncle wasn't afraid of 2014's big issues");
        $this->assertEqual($result->text, "Your Rude Uncle talked about ISIS, Ferguson, and immigration in 2014. "
            . "It's great to use Facebook to address things that matter.");
        $data = unserialize($result->related_data);
        $this->assertEqual(3, count($data['posts']));
        $this->assertEqual($data['posts'][0]->post_text, 'What is an ISIS?');
        $this->assertEqual($data['posts'][1]->post_text, 'This Ferguson stuff is crazy!');
        $this->assertEqual($data['posts'][2]->post_text, 'I don\'t understand immigration.');

        $this->dumpRenderedInsight($result, $this->instance, "Three Topics");
    }

    public function testFourTopics() {
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-02-01', 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'This Ferguson stuff is crazy!',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-03-01', 'post_id' => 2, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'Climate Change is made up! Buffalo has always had killer storms.',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-04-05', 'post_id' => 3, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'I don\'t understand immigration.',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-01-01', 'post_id' => 4, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'What is an ISIS?',
            )
        );
        $insight_plugin = new EOYControversialTopicsInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Your Rude Uncle wasn't afraid of 2014's big issues");
        $this->assertEqual($result->text, "Your Rude Uncle talked about ISIS, Ferguson, climate change, and "
            . "immigration in 2014. It's great to use Facebook to address things that matter.");
        $data = unserialize($result->related_data);
        $this->assertEqual(3, count($data['posts']));
        $this->assertEqual($data['posts'][0]->post_text, 'What is an ISIS?');
        $this->assertEqual($data['posts'][1]->post_text, 'This Ferguson stuff is crazy!');
        $this->assertEqual($data['posts'][2]->post_text, 'Climate Change is made up! Buffalo has always had killer '
            .'storms.');

        $this->dumpRenderedInsight($result, $this->instance, "Three Topics");
    }

    public function testSameTopicMultipleTerms() {
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-02-01', 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'This Ferguson stuff is crazy!',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-01-11', 'post_id' => 2, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'Where is Ferguson, anyways?  Antartica?',
            )
        );
        $insight_plugin = new EOYControversialTopicsInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Your Rude Uncle wasn't afraid of 2014's big issues");
        $this->assertEqual($result->text, "Your Rude Uncle talked about and Ferguson in 2014. It's great to use "
            . "Facebook to address things that matter.");
        $data = unserialize($result->related_data);
        $this->assertEqual(1, count($data['posts']));
        $this->assertEqual($data['posts'][0]->post_text, 'Where is Ferguson, anyways?  Antartica?');

        $this->dumpRenderedInsight($result, $this->instance, "One Topic, Mentioned multiple times");
    }
}
