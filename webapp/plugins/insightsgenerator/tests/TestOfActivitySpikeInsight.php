<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfActivitySpikeInsight.php
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
 * Test for ActivitySpikeInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/activityspike.php';

class TestOfActivitySpikeInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_username = 'buffy';
        $this->instance->network = 'twitter';

        $this->builders[] = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>10,
            'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>date('Y-m-d'),
            'related_data'=>serialize('sample hot posts data') ));
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new ActivitySpikeInsight();
        $this->assertIsA($insight_plugin, 'ActivitySpikeInsight' );
    }

    public function testUnpopularPost() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('high_fave_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_reply_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('high_reply_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_retweet_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('high_retweet_count_last_7_days', $this->instance->id, $avg=2, $today);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 1, 'retweet_count_cache' => 1, 'favlike_count_cache' => 1,
            'post_text' => 'This is a really good post',
            'author_username' => $this->instance->network_username, 'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d H:i:s'), 'id' => 1
        ));
        $insight_plugin = new ActivitySpikeInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_spike_7_day_1', 10, $today);
        $this->assertNull($result);

        $result = $insight_dao->getInsight('fave_high_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_high_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_high_7_day_1', 10, $today);
        $this->assertNull($result);
    }


    public function test7dayAverage() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_retweet_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_reply_count_last_7_days', $this->instance->id, $avg=2, $today);

        // We add an old baseline so that 7 day checks
        $baseline_dao->insertInsightBaseline('high_fave_count_last_365_days', $this->instance->id, $avg=2,
            date('Y-m-d', time() - (8*24*60*60)));

        $insight_dao = new InsightMySQLDAO();

        $posts = array($this->makePost($replies=5, $retweets=1, $faves=10));
        $insight_plugin = new ActivitySpikeInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $result = $insight_dao->getInsight('retweet_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('@buffy hit a nerve this week', $result->headline);
        $this->assertEqual("<strong>10 people</strong> favorited @buffy's tweet, more than" .
            " <strong>5x</strong> @buffy's 7-day average.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_LOW);

        $posts = array($this->makePost($replies=10, $retweets=1, $faves=5));
        $insight_plugin = new ActivitySpikeInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_spike_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("@buffy got <strong>10 replies</strong>", $result->headline);
        $this->assertEqual("That's more than <strong>5x</strong> @buffy's 7-day average.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_LOW);

        $posts = array($this->makePost($replies=1, $retweets=5, $faves=2));
        $insight_plugin = new ActivitySpikeInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_spike_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("<strong>5 people</strong> thought @buffy was worth retweeting", $result->headline);
        $this->assertEqual("That's more than <strong>double</strong> @buffy's average over the last 7 days.",
            $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_LOW);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function test7dayHigh() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        foreach (array('fave','reply','retweet') as $act) {
            $baseline_dao->insertInsightBaseline("avg_{$act}_count_last_7_days", $this->instance->id, 2, $today);
            $baseline_dao->insertInsightBaseline("high_{$act}_count_last_7_days", $this->instance->id, 2, $today);
        }

        // We add an old baseline so that 7 day checks
        $baseline_dao->insertInsightBaseline('high_fave_count_last_365_days', $this->instance->id, $avg=2,
            date('Y-m-d', time() - (8*24*60*60)));

        $posts = array($this->makePost($replies=1, $retweets=10, $faves=50));
        $insight_plugin = new ActivitySpikeInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_high_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_high_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('@buffy really got some favorites', $result->headline);
        $this->assertEqual("<strong>50 people</strong> favorited @buffy's tweet.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_MED);

        $posts = array($this->makePost($replies=1, $retweets=10, $faves=5));
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('reply_high_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_high_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("<strong>10 people</strong> retweeted @buffy", $result->headline);
        $this->assertEqual("That's a new 7-day record.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_MED);

        $posts = array($this->makePost($replies=10, $retweets=10, $faves=5));
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('retweet_high_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_high_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("@buffy got <strong>10 replies</strong>", $result->headline);
        $this->assertEqual("That's a new 7-day record.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_MED);

        $posts = array($this->makePost($replies=10, $retweets=10, $faves=50));
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('retweet_high_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_high_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("@buffy really got some favorites", $result->headline);
        $this->assertEqual("<strong>50 people</strong> favorited @buffy's tweet.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_MED);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function test30dayAverage() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_30_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_reply_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_reply_count_last_30_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_retweet_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_retweet_count_last_30_days', $this->instance->id, $avg=2, $today);

        $posts = array($this->makePost($replies=1, $retweets=2, $faves=10));
        $insight_plugin = new ActivitySpikeInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // At this point, we should not have the 7 or 30, because we don't have 30 day old baselines
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNull($result);

        // We add an old baseline so that 30 day checks are done.
        $baseline_dao->insertInsightBaseline('high_fave_count_last_365_days', $this->instance->id, $avg=2,
            date('Y-m-d', time() - (31*24*60*60)));

        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('@buffy got 5x the favorites', $result->headline);
        $this->assertEqual("<strong>10 people</strong> favorited @buffy's tweet, which is more than "
            . "<strong>5x</strong> @buffy's 30-day average.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);

        $posts = array($this->makePost($replies=1, $retweets=10, $faves=1));
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_spike_30_day_1', 10, $today);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function test30daySpike() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_30_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('high_fave_count_last_30_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_reply_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_reply_count_last_30_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('high_reply_count_last_30_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_retweet_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_retweet_count_last_30_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('high_retweet_count_last_30_days', $this->instance->id, $avg=2, $today);

        $posts = array($this->makePost($replies=5, $retweets=1, $faves=10));
        $insight_plugin = new ActivitySpikeInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // At this point, we should not have the 7 or 30, because we don't have 30 day old baselines
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_30_day_1', 10, $today);
        $this->assertNull($result);

        $baseline_dao->insertInsightBaseline('high_fave_count_last_365_days', $this->instance->id, $avg=2,
            date('Y-m-d', time() - (31*24*60*60)));

        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Also, the high supersedes the average
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_spike_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_spike_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_30_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("Highest number of favorites in the past 30 days",
            $result->headline);
        $this->assertEqual("<strong>10 people</strong> favorited @buffy's tweet.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);

        $posts = array($this->makePost($replies=5, $retweets=10, $faves=1));
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('fave_high_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_high_30_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("That's the most one of @buffy's tweets has been retweeted in the past month!",
            $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);
        $this->assertEqual("<strong>10 people</strong> retweeted @buffy", $result->headline);

        $posts = array($this->makePost($replies=10, $retweets=1, $faves=1));
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('fave_high_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_high_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_high_30_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("@buffy got <strong>10 replies</strong>", $result->headline);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);
        $this->assertEqual("That's a new 30-day record for @buffy.", $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function test365dayHigh() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        foreach (array('fave','reply','retweet') as $act) {
            $baseline_dao->insertInsightBaseline("avg_{$act}_count_last_7_days", $this->instance->id, $avg=2, $today);
            $baseline_dao->insertInsightBaseline("avg_{$act}_count_last_30_days", $this->instance->id, $avg=2, $today);
            $baseline_dao->insertInsightBaseline("high_{$act}_count_last_30_days", $this->instance->id, $avg=2, $today);
            $baseline_dao->insertInsightBaseline("high_{$act}_count_last_365_days",$this->instance->id, $avg=2, $today);
        }

        $posts = array($this->makePost($replies=5, $retweets=1, $faves=10));
        $insight_plugin = new ActivitySpikeInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // At this point, we should not have the 7 or 30, because we don't have 30 day old baselines
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_365_day_1', 10, $today);
        $this->assertNull($result);

        $baseline_dao->insertInsightBaseline('high_fave_count_last_365_days', $this->instance->id, $avg=2,
            date('Y-m-d', time() - (366*24*60*60)));

        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_high_365_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_high_365_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_365_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("A 365-day record for favorites!", $result->headline);
        $this->assertEqual("<strong>10 people</strong> favorited @buffy's tweet.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);

        $posts = array($this->makePost($replies=5, $retweets=1, $faves=1));
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('retweet_high_365_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_365_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('reply_high_365_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("@buffy got <strong>5 replies</strong> &mdash; a 365-day high!", $result->headline);
        $this->assertEqual("Why do you think this tweet did so well?", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);

        $posts = array($this->makePost($replies=5, $retweets=100, $faves=1));
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $result = $insight_dao->getInsight('reply_high_365_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_365_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('retweet_high_365_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("A new 365-day record!", $result->headline);
        $this->assertEqual("<strong>100 people</strong> retweeted @buffy's tweet.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testTimeGeneratedDoesntUpdate() {
        $today = date('Y-m-d');
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        foreach (array('fave','reply','retweet') as $act) {
            $baseline_dao->insertInsightBaseline("avg_{$act}_count_last_7_days", $this->instance->id, 2, $today);
            $baseline_dao->insertInsightBaseline("high_{$act}_count_last_7_days", $this->instance->id, 2, $today);
        }

        // We add an old baseline so that 7 day checks
        $baseline_dao->insertInsightBaseline('high_fave_count_last_365_days', $this->instance->id, $avg=2,
            date('Y-m-d', time() - (8*24*60*60)));


        $posts = array($this->makePost($replies=1, $retweets=10, $faves=50));
        $insight_plugin = new ActivitySpikeInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_high_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('@buffy really got some favorites', $result->headline);
        $generated = $result->time_generated;
        $date = $result->date;

        sleep(1); // force timestamp to change
        $posts = array($this->makePost($replies=1, $retweets=10, $faves=100));
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $result = $insight_dao->getInsight('fave_high_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('@buffy really got some favorites', $result->headline);
        $this->assertEqual($result->time_generated, $generated);
        $this->assertEqual($result->date, $date);

    }

    private function makePost($replies, $retweets, $faves) {
        return new Post(array(
            'reply_count_cache' => $replies, 'retweet_count_cache' => $retweets, 'favlike_count_cache' => $faves,
            'post_text' => 'This is a really good post',
            'author_username' => $this->instance->network_username, 'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d H:i:s'), 'id' => 1
        ));
    }
}
