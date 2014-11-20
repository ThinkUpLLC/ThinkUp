<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYThankedPeopleInsight.php
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoythankedpeople.php';

class TestOfEOYThankedPeopleInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'Thankster';
        $instance->network = 'twitter';
        $this->instance = $instance;

        $this->builders = array();
        $this->builders[] = FixtureBuilder::build('users', array('user_id'=>'1','network'=>'twitter','is_protected'=>0,
            'user_name' => 'one', 'joined' => ('2013-05-25 03:12')));
        $this->builders[] = FixtureBuilder::build('users', array('user_id'=>'2','network'=>'twitter','is_protected'=>0,
            'user_name' => 'two', 'joined' => ('2013-05-25 03:12')));
        $this->builders[] = FixtureBuilder::build('users', array('user_id'=>'3','network'=>'facebook','is_protected'=>0,
            'user_name' => 'First User', 'joined' => ('2013-05-25 03:12')));
        $this->builders[] = FixtureBuilder::build('users', array('user_id'=>'4','network'=>'facebook','is_protected'=>0,
            'user_name' => 'Second User', 'joined' => ('2013-05-25 03:12')));
        $this->builders[] = FixtureBuilder::build('users', array('user_id'=>'5','network'=>'facebook','is_protected'=>0,
            'user_name' => 'Third User', 'joined' => ('2013-05-25 03:12')));
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new EOYThankedPeopleInsight();
        $this->assertIsA($insight_plugin, 'EOYThankedPeopleInsight' );
    }

    public function testNoThanks() {
        // No thanks is not enough
        $today = date('Y-m-d');
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 1, 'author_username'=> 'testy', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d', strtotime('January 9')),
            'author_user_id' => $this->instance->network_user_id, 'post_text' => "I hate it all."));

        $insight_plugin = new EOYThankedPeopleInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNull($result);

        // One thank is not quite enough, either
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 1, 'author_username'=> 'testy', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d', strtotime('January 9')),
            'author_user_id' => $this->instance->network_user_id, 'post_text' => "Thanks everyone"));

        $insight_plugin = new EOYThankedPeopleInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNull($result);

        // Two should generate
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 2, 'author_username'=> 'testy', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d', strtotime('January 9')),
            'author_user_id' => $this->instance->network_user_id, 'post_text' => "Thanks everyone"));

        $insight_plugin = new EOYThankedPeopleInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);
    }

    public function testTwitter() {
        $today = date('Y-m-d');
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 1, 'author_username'=> 'testy', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d', strtotime('January 9')),
            'author_user_id' => $this->instance->network_user_id, 'post_text' => "I hate it all."));

        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 1, 'author_username'=> 'testy', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d', strtotime('January 9')),
            'author_user_id' => $this->instance->network_user_id, 'post_text' => "Thanks everyone"));

        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 2, 'author_username'=> 'testy', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d', strtotime('January 9')),
            'author_user_id' => $this->instance->network_user_id, 'post_text' => "Thanks everyone"));

        $insight_plugin = new EOYThankedPeopleInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);

        $data = unserialize($result->related_data);
        $this->assertEqual($result->headline, 'Reflecting on what @Thankster is thankful for');
        $this->assertEqual($result->text, 'These are some of the 2 people @Thankster thanked this year.');
        $this->assertEqual(count($data['people']), 2);
        $this->assertEqual($data['people'][0]->username, 'one');
        $this->assertEqual($data['people'][1]->username, 'two');
        $this->assertEqual($data['hero_image']['alt_text'], $result->headline);
        $this->assertEqual($data['hero_image']['img_link'], 'https://www.flickr.com/photos/voght/2441818832/sizes/z/');
        $this->assertEqual($data['button']['label'], 'Say "thanks" one more time');
        $this->assertEqual($data['button']['url'], 'https://twitter.com/intent/tweet?text=Thanks!');
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFacebook() {
        $this->instance->network = 'facebook';
        $this->instance->network_username = 'Mr. Thankful';
        $today = date('Y-m-d');
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 3, 'author_username'=> 'testy', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d', strtotime('January 9')),
            'author_user_id' => $this->instance->network_user_id, 'post_text' => "I hate it all."));

        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 4, 'author_username'=> 'testy', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d', strtotime('January 9')),
            'author_user_id' => $this->instance->network_user_id, 'post_text' => "Thanks everyone"));

        // We shouldn't get three users now
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 4, 'author_username'=> 'testy', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d', strtotime('January 9')),
            'author_user_id' => $this->instance->network_user_id, 'post_text' => "Thanks everyone"));

        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 5, 'author_username'=> 'testy', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d', strtotime('January 9')),
            'author_user_id' => $this->instance->network_user_id, 'post_text' => "Thanks everyone"));

        // We shouldn't get three users still
        $post_builders[] = FixtureBuilder::build('posts', array(
            'in_reply_to_user_id' => 4, 'author_username'=> 'testy', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d', strtotime('January 14')),
            'author_user_id' => $this->instance->network_user_id, 'post_text' => "Thanks a lot"));

        $insight_plugin = new EOYThankedPeopleInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);

        $data = unserialize($result->related_data);
        $this->assertEqual($result->headline, 'Mr. Thankful has friends to be thankful for');
        $this->assertEqual($result->text, 'These are some of the 2 friends Mr. Thankful thanked this year.');
        $this->assertEqual(count($data['people']), 2);
        $this->assertEqual($data['people'][0]->username, 'Second User');
        $this->assertEqual($data['people'][1]->username, 'Third User');
        $this->assertEqual($data['hero_image']['alt_text'], $result->headline);
        $this->assertEqual($data['hero_image']['img_link'],
            'https://www.flickr.com/photos/aidanmorgan/4135626581/sizes/z/');
        $this->assertEqual($data['button']['label'], 'Share your thanks');
        $this->assertEqual($data['button']['url'], 'http://www.facebook.com/sharer/sharer.php?t=Thanks!');
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}
