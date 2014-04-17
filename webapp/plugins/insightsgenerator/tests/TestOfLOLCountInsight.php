<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfLOLCountInsight.php
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/lolcount.php';

class TestOfLOLCountInsight extends ThinkUpInsightUnitTestCase {
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
        $insight_plugin = new LOlCountInsight();
        $this->assertIsA($insight_plugin, 'LOLCountInsight' );
    }

    public function testNoLOL() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => "That is not funny"));

        $insight_plugin = new LOLCountInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), 10, $today);
        $this->assertNull($result);
    }

    public function testOneLOLNoBaseline() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Hahaha, so funny.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new LOLCountInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'OMG LOL!');
        $this->assertEqual($result->text, 'Looks like @testy found 1 thing LOL-worthy in the last week.');

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(1, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMultipleLOLNoBaseline() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Hahahahahaha', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'LOL, that is so good.', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'I am total rofling!', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new LOLCountInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'OMG LOL!');
        $this->assertEqual($result->text, 'Looks like @testy found 3 things LOL-worthy in the last week.');

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(3, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testOneAboveBaseline() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Hahaha', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'ROFL', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'LMAO', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new LOLCountInsight();
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $last_month = date('Y-m-d', strtotime('-1 month'));
        $insight_baseline_dao->insertInsightBaseline($baseline_name, $this->instance->id, 1, $last_month);

        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'OMG LOL!');
        $this->assertEqual($result->text, 'Looks like @testy found 2 things LOL-worthy in the last week. '
            .'That\'s 1 more laugh than the prior month.');

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(2, $baseline->value);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testTwoAboveBaseline() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'Hahaha', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'ROFL', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter',
            'post_text' => 'LOL', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new LOLCountInsight();
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $last_month = date('Y-m-d', strtotime('-1 month'));
        $insight_baseline_dao->insertInsightBaseline($baseline_name, $this->instance->id, 1, $last_month);

        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'OMG LOL!');
        $this->assertEqual($result->text, 'Looks like @testy found 3 things LOL-worthy in the last week. '
            .'That\'s 2 more laughs than the prior month.');

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
            'post_text' => 'LOL', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new LOLCountInsight();
        $today = date ('Y-m-d');

        $insight_plugin->generateInsight($this->instance, $posts, 3);
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'LOL activity detected!');

        TimeHelper::setTime(2);
        $insight_plugin->generateInsight($this->instance, $posts, 3);
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'OMG LOL!');

        TimeHelper::setTime(3);
        $insight_plugin->generateInsight($this->instance, $posts, 3);
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'LOLOLOLOL, indeed.');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWithOneRepliedToPost() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'post_id'=>1234, 'author_username'=> 'funnyguy', 'network' => 'twitter','author_user_id' => 1,
            'author_fullname' => 'Mr. Funny Pants', 'author_avatar' => 'avatar.jpg',
            'post_text' => 'I like cheese', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter', 'in_reply_to_post_id' => 1234,
            'post_text' => 'Hahaha, so funny.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new LOLCountInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'OMG LOL!');
        $this->assertEqual($result->text, 'Looks like @testy found 1 thing LOL-worthy in the last week.');

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(1, $baseline->value);
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['posts']);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual($data['posts'][0]->post_text, 'I like cheese');
        $this->assertEqual($data['posts'][0]->author_fullname, 'Mr. Funny Pants');
        $this->assertEqual($data['posts'][0]->author_username, 'funnyguy');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWithThreeRepliedToPosts() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array(
            'post_id'=>1234, 'author_username'=> 'funnyguy', 'network' => 'twitter','author_user_id' => 1,
            'author_fullname' => 'Mr. Funny Pants', 'author_avatar' => 'avatar.jpg',
            'post_text' => 'I like cheese', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter', 'in_reply_to_post_id' => 1234,
            'post_text' => 'Hahaha, so funny.', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'post_id'=>1235, 'author_username'=> 'anildash', 'network' => 'twitter','author_user_id' => 1,
            'author_fullname' => 'Anil Dash',
            'author_avatar' => 'https://pbs.twimg.com/profile_images/450813957461524480/iNanfzj4_bigger.jpeg',
            'post_text' => 'Gina is going to delete Thinkup.com', 'pub_date' => '-1d'));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter', 'in_reply_to_post_id' => 1235,
            'post_text' => 'Oh, that makes me lol so much.', 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'post_id'=>1236, 'author_username'=> 'imnotjustspike', 'network' => 'twitter','author_user_id' => 1,
            'author_fullname' => 'Spike',
            'author_avatar' => 'https://pbs.twimg.com/profile_images/1598390134/gallery_8.png',
            'post_text' => "First he'll kill her, then I'll save her!", 'pub_date' => date('Y-m-d')));
        $post_builders[] = FixtureBuilder::build('posts', array(
            'author_username'=> 'testy', 'network' => 'twitter', 'in_reply_to_post_id' => 1236,
            'post_text' => 'That line makes me rofl.', 'pub_date' => date('Y-m-d')));
        $insight_plugin = new LOLCountInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight($insight_plugin->getSlug(), $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'OMG LOL!');
        $this->assertEqual($result->text, 'Looks like @testy found 3 things LOL-worthy in the last week.');

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_name = $insight_plugin->getSlug(). '_' . 'count';
        $baseline = $insight_baseline_dao->getInsightBaseline($baseline_name, $this->instance->id, date('Y-m-d'));
        $this->assertEqual(3, $baseline->value);
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['posts']);
        $this->assertEqual(count($data['posts']), 3);
        $this->assertEqual($data['posts'][0]->post_text, 'I like cheese');
        $this->assertEqual($data['posts'][1]->author_fullname, 'Anil Dash');
        $this->assertEqual($data['posts'][2]->author_username, 'imnotjustspike');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPostMatcher() {
        $insight_plugin = new LOLCountInsight();
        $post = new Post();

        $tests = array(
            "Hahaha, so funny!" => true,
            "Haha" => true,
            "Oh, haha, so funny." => true,
            "That makes me LOL" => true,
            "That makes me ROFL" => true,
            "HAHAH, come ride in my ROFLCopter" => true,
            "Not funny at all." => false,
            "Hah, good one" => false,
            "rololing on the floor" => false,
            "not arofl." => false,
        );

        foreach ($tests as $string => $expected) {
            $post->post_text = $string;
            $this->assertEqual($insight_plugin->postMatchesCriteria($post, $this->instance), $expected,
                $post->post_text.' not '.($expected?'true':'false'));
        }

    }
}
