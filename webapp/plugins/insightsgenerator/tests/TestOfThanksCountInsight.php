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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/thankscount.php';

class TestOfThanksCountInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'testy';
        $instance->network = 'twitter';
        $this->instance = $instance;

        TimeHelper::setTime(2); // Force one headline for most tests
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new ThanksCountInsight();
        $this->assertIsA($insight_plugin, 'ThanksCountInsight' );
    }

    public function testNoThanks() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => "I am not feeling that feeling of appreciating stuff."));

        $insight_plugin = new ThanksCountInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), 10, $today);
        $this->assertNull($result);
    }

    public function testOneThankNoBaseline() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Thanks thank you thanks a lot!.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new ThanksCountInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'Gratitude is contagious.');
        $this->assertEqual($result->text, '@testy thanked someone once on Twitter last month.');

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(1, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMultipleThankNoBaseline() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Thanks thank you thanks a lot!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Thank you so much', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I want to thank you.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new ThanksCountInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'Gratitude is contagious.');
        $this->assertEqual($result->text, '@testy thanked someone 3 times on Twitter last month.');

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(3, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAboveBaseline() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Thanks thank you thanks a lot!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Thank you so much', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I want to thank you.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new ThanksCountInsight();
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $last_month = date('Y-m-d', strtotime('-1 month'));
        $insight_baseline_dao->insertInsightBaseline($baseline_name, $this->instance->id, 1, $last_month);

        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'Gratitude is contagious.');
        $this->assertEqual($result->text, '@testy thanked someone 3 times on Twitter last month.'
            .' Sounds like there was even more to be thankful about in '.date('F', strtotime('-1 month'))
            .' than in '.date('F', strtotime('-2 month')).'.');

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(3, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateHeadlines() {
        TimeHelper::setTime(1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Thanks thank you thanks a lot!.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new ThanksCountInsight();
        $today = date ('Y-m-d');

        $insight_plugin->generateInsight($this->instance, $posts, 3);
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'Gratitude makes everybody happy.');

        TimeHelper::setTime(2);
        $insight_plugin->generateInsight($this->instance, $posts, 3);
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'Gratitude is contagious.');

        TimeHelper::setTime(3);
        $insight_plugin->generateInsight($this->instance, $posts, 3);
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'Saying &ldquo;thanks&rdquo; is a great way to spend time on Twitter.');

        TimeHelper::setTime(4);
        $insight_plugin->generateInsight($this->instance, $posts, 3);
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'Way to show appreciation.');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateText() {
        TimeHelper::setTime(1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Thanks thank you thanks a lot!.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new ThanksCountInsight();
        $today = date ('Y-m-d');

        $insight_plugin->generateInsight($this->instance, $posts, 3);
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertPattern('/\@testy tweeted 1 thank-you last month./', $result->text);

        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Thanks thank you thanks a lot!.', 'pub_date' => date('Y-m-d')));

        $insight_plugin->generateInsight($this->instance, $posts, 3);
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertPattern('/\@testy tweeted 2 thank-yous last month./', $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWithThankee() {
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'testuser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>1,
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter', 'in_reply_to_user_id' => '9654000768',
            'post_text' => 'Thanks thank you thanks a lot!', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Thank you so much', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Nope.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new ThanksCountInsight();

        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '@testuser probably appreciated it.');
        $this->assertEqual($result->text, '@testy tweeted 2 thank-yous last month.');
        $this->assertEqual('avatar.jpg', $result->header_image);


        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(2, $baseline->value);


        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPostMatcher() {
        $insight_plugin = new ThanksCountInsight();
        $post = new Post();

        $post->post_text = 'thank you';
        $this->assertTrue($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not true');

        $post->post_text = 'Thanks!';
        $this->assertTrue($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not true');

        $post->post_text = 'thanks!';
        $this->assertTrue($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not true');

        $post->post_text = 'Hey, thank you for your help.';
        $this->assertTrue($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not true');

        $post->post_text = 'No, thank you!';
        $this->assertTrue($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not true');

        $post->post_text = 'No thanks.';
        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not false');

        $post->post_text = 'No thank you';
        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not false');

        $post->post_text = 'Thanks, but no.';
        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not false');

        $post->post_text = 'Thanks but I have already got one.';
        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not false');

        $post->post_text = 'Thanksgiving is great';
        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not false');

        $post->post_text = 'I was thanking my mom.';
        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not false');

        $post->post_text = 'no thanks';
        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not false');

        $post->post_text = 'Thanks!';
        $this->assertTrue($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not true');

        $post->in_reply_to_user_id = $this->instance->network_user_id;
        $this->assertFalse($insight_plugin->postMatchesCriteria($post, $this->instance), $post->post_text.' not false');
    }
}
