<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFaveLikeSpikeInsight.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * Test of Fave Like Spike Insight
 *
 * Test for FaveLikeSpikeInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/favlikespike.php';

class TestOfFaveLikeSpikeInsight extends ThinkUpUnitTestCase {

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
        parent::tearDown();
    }

    public function testConstructor() {
        $favelikespike_insight_plugin = new FaveLikeSpikeInsight();
        $this->assertIsA($favelikespike_insight_plugin, 'FaveLikeSpikeInsight' );
    }

    public function testUnpopularPost() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('high_fave_count_last_7_days', $this->instance->id, $avg=2, $today);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 5, 'retweet_count_cache' => 1, 'favlike_count_cache' => 1,
            'post_text' => 'This is a really good post',
            'author_username' => $this->instance->network_username, 'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d H:i:s'), 'id' => 1
        ));
        $insight_plugin = new FaveLikeSpikeInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
    }

    public function test7dayAverage() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_7_days', $this->instance->id, $avg=2, $today);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 5, 'retweet_count_cache' => 1, 'favlike_count_cache' => 10,
            'post_text' => 'This is a really good post',
            'author_username' => $this->instance->network_username, 'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d H:i:s'), 'id' => 1
        ));
        $insight_plugin = new FaveLikeSpikeInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('This one hit a nerve this week.', $result->headline);
        $this->assertEqual("<strong>10 people</strong> favorited @buffy's tweet, more than" .
            " <strong>5x</strong> @buffy's 7-day average.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_LOW);

    }

    public function test7dayHigh() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_7_days', $this->instance->id, 2, $today);
        $baseline_dao->insertInsightBaseline('high_fave_count_last_7_days', $this->instance->id, 2, $today);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 5, 'retweet_count_cache' => 1, 'favlike_count_cache' => 10,
            'post_text' => 'This is a really good post',
            'author_username' => $this->instance->network_username, 'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d H:i:s'), 'id' => 1
        ));
        $insight_plugin = new FaveLikeSpikeInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);

        $result = $insight_dao->getInsight('fave_high_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('This one really got some favorites.', $result->headline);
        $this->assertEqual("<strong>10 people</strong> favorited @buffy's tweet.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);
    }

    public function test30dayAverage() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_30_days', $this->instance->id, $avg=2, $today);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 5, 'retweet_count_cache' => 1, 'favlike_count_cache' => 10,
            'post_text' => 'This is a really good post',
            'author_username' => $this->instance->network_username, 'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d H:i:s'), 'id' => 1
        ));
        $insight_plugin = new FaveLikeSpikeInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        // At this point, we should have the 7, not 30, because we don't have 30 day old baselines
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNull($result);

        $baseline_dao->insertInsightBaseline('high_fave_count_last_365_days', $this->instance->id, $avg=2,
            date('Y-m-d', time() - (31*24*60*60)));

        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('This tweet got 5x the favorites for @buffy.', $result->headline);
        $this->assertEqual("<strong>10 people</strong> favorited @buffy's tweet, which is more than "
            . "<strong>5x</strong> @buffy's 30-day average.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_LOW);

    }

    public function test30daySpike() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_30_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('high_fave_count_last_30_days', $this->instance->id, $avg=2, $today);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 5, 'retweet_count_cache' => 1, 'favlike_count_cache' => 10,
            'post_text' => 'This is a really good post',
            'author_username' => $this->instance->network_username, 'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d H:i:s'), 'id' => 1
        ));
        $insight_plugin = new FaveLikeSpikeInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        // At this point, we should have the 7, not 30, because we don't have 30 day old baselines
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_30_day_1', 10, $today);
        $this->assertNull($result);

        $baseline_dao->insertInsightBaseline('high_fave_count_last_365_days', $this->instance->id, $avg=2,
            date('Y-m-d', time() - (31*24*60*60)));

        $insight_plugin->generateInsight($this->instance, $posts, 3);

        // Also, the high supersedes the average
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_30_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("That's the highest number of favorites @buffy's gotten in the past 30 days.", $result->headline);
        $this->assertEqual("<strong>10 people</strong> favorited @buffy's tweet.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);
    }

    public function test30dayHigh() {
        $today = date('Y-m-d');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_7_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('avg_fave_count_last_30_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('high_fave_count_last_30_days', $this->instance->id, $avg=2, $today);
        $baseline_dao->insertInsightBaseline('high_fave_count_last_365_days', $this->instance->id, $avg=2, $today);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 5, 'retweet_count_cache' => 1, 'favlike_count_cache' => 10,
            'post_text' => 'This is a really good post',
            'author_username' => $this->instance->network_username, 'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg', 'network' => $this->instance->network,
            'pub_date' => date('Y-m-d H:i:s'), 'id' => 1
        ));
        $insight_plugin = new FaveLikeSpikeInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        // At this point, we should have the 7, not 30, because we don't have 30 day old baselines
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNotNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_365_day_1', 10, $today);
        $this->assertNull($result);

        $baseline_dao->insertInsightBaseline('high_fave_count_last_365_days', $this->instance->id, $avg=2,
            date('Y-m-d', time() - (366*24*60*60)));

        $insight_plugin->generateInsight($this->instance, $posts, 3);

        $result = $insight_dao->getInsight('fave_spike_7_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_spike_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_30_day_1', 10, $today);
        $this->assertNull($result);
        $result = $insight_dao->getInsight('fave_high_365_day_1', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual("That's a 365-day record for favorites!", $result->headline);
        $this->assertEqual("<strong>10 people</strong> favorited @buffy's tweet.", $result->text);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);
    }
}
