<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfCongratsCountInsight.php
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
 * Test of Congrats Count Insight
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/congratscount.php';

class TestOfCongratsCountInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'janesmith';
        $instance->network = 'twitter';
        $this->instance = $instance;

        TimeHelper::setTime(3); // Force one headline for most tests
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new CongratsCountInsight();
        $this->assertIsA($insight_plugin, 'CongratsCountInsight' );
    }

    public function testNoCongrats() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('pub_date' => date('Y-m-d', strtotime('-1 day')),
            'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
            'post_text' => "Well, I'm not going to say that thing people say about good things."));

        $insight_plugin = new CongratsCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), 10, $today);
        $this->assertNull($result);
    }

    public function testOneCongrat() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>1, 'pub_date' => date('Y-m-d', strtotime('-1 day')),
            'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
            'post_text' => "Well, I'm not going to say that thing people say about good things."));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>2, 'pub_date' => date('Y-m-d', strtotime('-1 day')),
            'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
            'post_text' => "A really good thing happened!"));
        $post_builders[] = FixtureBuilder::build('posts', array('pub_date' => date('Y-m-d', strtotime('-1 day')),
            'in_reply_to_user_id' => 1234, 'in_reply_to_post_id' => 2,
            'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
            'post_text' => "Congrats!"));
        $insight_plugin = new CongratsCountInsight();
        $insight_plugin->generateInsight($this->instance, null, array(), 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@janesmith's friend had some great news!");
        $this->assertEqual($result->text, '@janesmith congratulated 1 person in the past month for this tweet.');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMultipleCongratsOfDifferentTypesV1() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>1,
            'author_username'=> 'friend1', 'network' => 'twitter',
            'post_text' => 'I am getting married!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>2,
            'author_username'=> 'friend2', 'network' => 'twitter',
            'post_text' => 'I love cheese!', 'pub_date' => date('Y-m-d')));
        // 3 Congrats, 2 users, 2 available posts
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9999, 'in_reply_to_post_id' => 1,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Congrats!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9998, 'in_reply_to_post_id' => 2,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Well then, congratulations, friend of mine!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9999, 'in_reply_to_post_id' => 999999,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Hey, congrats.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new CongratsCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@janesmith's friends had some great news!");
        $this->assertEqual($result->text,
            "@janesmith congratulated 2 people in the past month for these tweets.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['posts']), 2);
        $this->assertEqual("I am getting married!", $data['posts'][0]->post_text);
        $this->assertEqual("I love cheese!", $data['posts'][1]->post_text);
        $this->assertEqual("friend1", $data['posts'][0]->author_username);
        $this->assertEqual("friend2", $data['posts'][1]->author_username);


        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMultipleCongratsOfDifferentTypesV2() {
        TimeHelper::setTime(2);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>1,
            'author_username'=> 'friend1', 'network' => 'twitter',
            'post_text' => 'I am getting married!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>2,
            'author_username'=> 'friend2', 'network' => 'twitter',
            'post_text' => 'I love cheese!', 'pub_date' => date('Y-m-d')));
        // 3 Congrats, 2 users, 2 available posts
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9999, 'in_reply_to_post_id' => 1,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Congrats!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9998, 'in_reply_to_post_id' => 2,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Well then, congratulations, friend of mine!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9999, 'in_reply_to_post_id' => 999999,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Hey, congrats.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new CongratsCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Twitter is for announcing good news!");
        $this->assertEqual($result->text,
            "Here are the tweets that inspired @janesmith to congratulate people this month.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['posts']), 2);
        $this->assertEqual("I am getting married!", $data['posts'][0]->post_text);
        $this->assertEqual("I love cheese!", $data['posts'][1]->post_text);
        $this->assertEqual("friend1", $data['posts'][0]->author_username);
        $this->assertEqual("friend2", $data['posts'][1]->author_username);


        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMultipleCongratsOfDifferentTypesV3() {
        TimeHelper::setTime(1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>1,
            'author_username'=> 'friend1', 'network' => 'twitter',
            'post_text' => 'I am getting married!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>2,
            'author_username'=> 'friend2', 'network' => 'twitter',
            'post_text' => 'I love cheese!', 'pub_date' => date('Y-m-d')));
        // 3 Congrats, 2 users, 2 available posts
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9999, 'in_reply_to_post_id' => 1,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Congrats!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9998, 'in_reply_to_post_id' => 2,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Well then, congratulations, friend of mine!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9999, 'in_reply_to_post_id' => 999999,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Hey, congrats.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new CongratsCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Congratulations on the congrats, @janesmith!");
        $this->assertEqual($result->text,
            "3 tweets inspired @janesmith to congratulate someone this past month.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['posts']), 2);
        $this->assertEqual("I am getting married!", $data['posts'][0]->post_text);
        $this->assertEqual("I love cheese!", $data['posts'][1]->post_text);
        $this->assertEqual("friend1", $data['posts'][0]->author_username);
        $this->assertEqual("friend2", $data['posts'][1]->author_username);


        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testSingularV1() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>1,
            'author_username'=> 'friend1', 'network' => 'twitter',
            'post_text' => 'I am getting married!', 'pub_date' => date('Y-m-d')));
        // 1 Congrat, 1 users, 1 available posts
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9999, 'in_reply_to_post_id' => 1,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Congrats!', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new CongratsCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@janesmith's friend had some great news!");
        $this->assertEqual($result->text,
            "@janesmith congratulated 1 person in the past month for this tweet.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual("I am getting married!", $data['posts'][0]->post_text);
        $this->assertEqual("friend1", $data['posts'][0]->author_username);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testSingularV2() {
        TimeHelper::setTime(2);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>1,
            'author_username'=> 'friend1', 'network' => 'twitter',
            'post_text' => 'I am getting married!', 'pub_date' => date('Y-m-d')));
        // 1 Congrat, 1 users, 1 available posts
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9999, 'in_reply_to_post_id' => 1,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Congrats!', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new CongratsCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Twitter is for announcing good news!");
        $this->assertEqual($result->text,
            "Here is the tweet that inspired @janesmith to congratulate people this month.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual("I am getting married!", $data['posts'][0]->post_text);
        $this->assertEqual("friend1", $data['posts'][0]->author_username);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testSingularV3() {
        TimeHelper::setTime(1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('post_id'=>1,
            'author_username'=> 'friend1', 'network' => 'twitter',
            'post_text' => 'I am getting married!', 'pub_date' => date('Y-m-d')));
        // 1 Congrat, 1 users, 1 available posts
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 9999, 'in_reply_to_post_id' => 1,
            'author_username'=> 'janesmith', 'network' => 'twitter',
            'post_text' => 'Congrats!', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new CongratsCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Congratulations on the congrats, @janesmith!");
        $this->assertEqual($result->text,
            "1 tweet inspired @janesmith to congratulate someone this past month.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual("I am getting married!", $data['posts'][0]->post_text);
        $this->assertEqual("friend1", $data['posts'][0]->author_username);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPostMatcher() {
        $insight_plugin = new CongratsCountInsight();
        $post = new Post();

        $tests = array(
            "Congrats Joe!" => true,
            "Congratulations Becky!" => true,
            "Well, congrats everyone." => true,
            "Just wanted to say congratulations." => true,
            "CONGRATS" => true,
            "!!CONGRATS!!!!" => true,
            "Here are some conga-rats for you." => false,
            "C O N G R A T U L A T I O N S" => false,
            "whatever" => false,
            "Sometimes we say mazel tov!" => true,
            "I spell mazal tov with two As." => true,
            "Mazel tov" => true,
            "Let me say mazel tov, to you" => true,
            "I'm stuck in a maze to view this." => false,
        );

        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance), 'No in reply user');
        $post->in_reply_to_user_id = $this->instance->network_user_id + 1;
        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance), 'No in reply post');
        $post->in_reply_to_post_id = 1;
        foreach ($tests as $string => $expected) {
            $post->post_text = $string;
            $this->assertEqual($insight_plugin->postMatchesCriteria($post, $this->instance), $expected,
                $post->post_text.' not '.($expected?'true':'false'));
        }

        $post->post_text = 'Congrats!';
        $this->assertTrue($insight_plugin->postMatchesCriteria($post, $this->instance));
        $post->in_reply_to_user_id = $this->instance->network_user_id;
        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance));
    }
}
