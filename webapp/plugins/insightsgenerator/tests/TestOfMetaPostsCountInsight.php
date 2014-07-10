<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfMetaPostsCountInsight.php
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.CriteriaMatchInsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/metapostscount.php';

class TestOfMetaPostsCountInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'reflection';
        $instance->network = 'twitter';
        $this->instance = $instance;

        TimeHelper::setTime(2); // Force one headline for most tests
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new MetaPostsCountInsight();
        $this->assertIsA($insight_plugin, 'MetaPostsCountInsight' );
    }

    public function testNoMetaPosts() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => "This message has nothing to do with the service it is shown on."));

        $insight_plugin = new MetaPostsCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), 10, $today);
        $this->assertNull($result);
    }

    public function testOneMetaPostsNoBaseline() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $posts = array(
            new Post(array('post_text' => 'I am tweeting tweets on twitter.'))
        );
        $insight_plugin = new MetaPostsCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Tweetin' 'bout Twitter.");
        $this->assertEqual($result->text, "@reflection used Twitter to talk about Twitter once this week. "
            . "That's 100% of @reflection's tweets for the week.");

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(100, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMultipleMetaPostsNoBaseline() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $posts = array(
            new Post(array('post_text' => 'I am tweeting tweets on twitter.')),
            new Post(array('post_text' => 'Twitter is down?')),
            new Post(array('post_text' => 'Do you like my tweets?')),
            new Post(array('post_text' => 'I am eating pizza')),
            new Post(array('post_text' => 'I am flying a plane')),
            new Post(array('post_text' => 'Happy Monday!')),
            new Post(array('post_text' => 'Sad Monday!')),
        );
        $insight_plugin = new MetaPostsCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Tweetin' 'bout Twitter.");
        $this->assertEqual($result->text, "@reflection used Twitter to talk about Twitter 3 times this week. "
            . "That's 42% of @reflection's tweets for the week.");

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(42, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAboveBaseline() {
        $insight_plugin = new MetaPostsCountInsight();
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $last_month = date('Y-m-d', strtotime('-1 month'));
        $insight_baseline_dao->insertInsightBaseline($baseline_name, $this->instance->id, 20, $last_month);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $posts = array(
            new Post(array('post_text' => 'I am tweeting tweets on twitter.')),
            new Post(array('post_text' => 'I am eating pizza')),
        );
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Tweetin' 'bout Twitter.");
        $this->assertEqual($result->text, "@reflection used Twitter to talk about Twitter once this week. "
            . "That's 50% of @reflection's tweets for the week, up 30% from last week.");

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(50, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testBelowBaseline() {
        $insight_plugin = new MetaPostsCountInsight();
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $last_month = date('Y-m-d', strtotime('-1 month'));
        $insight_baseline_dao->insertInsightBaseline($baseline_name, $this->instance->id, 80, $last_month);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $posts = array(
            new Post(array('post_text' => 'I am tweeting tweets on twitter.')),
            new Post(array('post_text' => 'I am eating pizza')),
            new Post(array('post_text' => 'I am eating pizza')),
            new Post(array('post_text' => 'I am eating pizza')),
            new Post(array('post_text' => 'I am eating pizza')),
            new Post(array('post_text' => 'I am eating pizza')),
        );
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Tweetin' 'bout Twitter.");
        $this->assertEqual($result->text, "@reflection used Twitter to talk about Twitter once this week. "
            . "That's 16% of @reflection's tweets for the week, down 64% from last week.");

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(16, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMinimalChange() {
        $insight_plugin = new MetaPostsCountInsight();
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $last_month = date('Y-m-d', strtotime('-1 month'));
        $insight_baseline_dao->insertInsightBaseline($baseline_name, $this->instance->id, 20, $last_month);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $posts = array(
            new Post(array('post_text' => 'I am tweeting tweets on twitter.')),
            new Post(array('post_text' => 'I am eating pizza')),
            new Post(array('post_text' => 'I am eating pizza')),
            new Post(array('post_text' => 'I am eating pizza')),
            new Post(array('post_text' => 'I am eating pizza')),
            new Post(array('post_text' => 'I am eating pizza')),
        );
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Tweetin' 'bout Twitter.");
        $this->assertEqual($result->text, "@reflection used Twitter to talk about Twitter once this week. "
            . "That's 16% of @reflection's tweets for the week.");

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(16, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateHeadlinesTwitter() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $posts = array(new Post(array('post_text' => 'I am tweeting tweets on twitter.')));
        $insight_plugin = new MetaPostsCountInsight();

        TimeHelper::setTime(1);
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertEqual($result->headline, "Tweets on tweets on tweets.");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        TimeHelper::setTime(3);
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertEqual($result->headline, "It's Twitter all the way down.");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateHeadlinesFacebook() {
        $this->instance->network = 'facebook';
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $posts = array(new Post(array('post_text' => 'Behold my news feed and weep.')));
        $insight_plugin = new MetaPostsCountInsight();

        TimeHelper::setTime(1);
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertEqual($result->headline, "Feelings for Facebook.");
        $this->assertEqual($result->text, "reflection used Facebook to talk about Facebook once this "
            ."week. That's 100% of reflection's status updates for the week.");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        TimeHelper::setTime(2);
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertEqual($result->headline, "Feeding the news feed.");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        TimeHelper::setTime(3);
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertEqual($result->headline, "It's Facebook all the way down.");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPostMatcher() {
        $insight_plugin = new MetaPostsCountInsight();
        $post = new Post();

        $tests = array(
            "I am tweeting" => true,
            "I am twittering" => true,
            "Twitter is down." => true,
            "You are a twit." => false,
            "I like when a bird tweets." => true,
            "Did you all see my incredible tweet?" => true,
            "Check out my news feed on facebook" => false,
        );

        foreach ($tests as $string => $expected) {
            $post->post_text = $string;
            $this->assertEqual($insight_plugin->postMatchesCriteria($post, $this->instance), $expected,
                $post->post_text.' not '.($expected?'true':'false'));
        }

        $this->instance->network = 'facebook';
        $tests = array(
            "Check out my news feed on facebook" => true,
            "I am tweeting" => false,
            "I am twittering" => false,
            "Facebook is down." => true,
            "My newsfeed is depressing" => true,
            "My news feed is depressing" => true,
            "Feed me news." => false,
            "FB is blue" => false,
        );

        foreach ($tests as $string => $expected) {
            $post->post_text = $string;
            $this->assertEqual($insight_plugin->postMatchesCriteria($post, $this->instance), $expected,
                $post->post_text.' not '.($expected?'true':'false'));
        }

    }
}
