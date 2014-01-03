<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfSavedSearchResultsInsight.php
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
 * Test of Saved Search Results Insight
 *
 * Test for SavedSearchResultsInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/savedsearchresults.php';

class TestOfSavedSearchResultsInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testSavedSearchResults() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('saved_search_results_102', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $builders = self::buildData();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'facebook';
        $instance->network_username = 'ev';
        $stylestats_insight_plugin = new SavedSearchResultsInsight();
        $stylestats_insight_plugin->generateInsight($instance, $last_week_of_posts, 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('saved_search_results_102', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'saved_search_results_102');
        $this->assertEqual($result->filename, 'savedsearchresults');
        $this->assertEqual('7 new posts contain "<strong>#thinkupsavedsearch</strong>".', $result->headline);
    }

    private function buildData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('hashtags',
        array('id'=>102, 'hashtag' => '#thinkupsavedsearch', 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('instances_hashtags',
        array('instance_id'=>1, 'hashtag_id' => 102));

        $today = date('Y-m-d H:i:s');
        $count = 0;
        while ($count < 7) { // Add 7 posts for a hashtag today
            $builders[] = FixtureBuilder::build('posts', array('post_id' => $count+14, 'author_user_id' => '1',
                'author_username' => 'aun', 'author_fullname' => 'afn',
                'author_avatar' => 'http://aa.com', 'author_follower_count' => 0, 'post_text' => 'pt',
                'is_protected' => 0, 'source' => '<a href=""></a>', 'location' => 'BCN', 'place' => '',
                'place_id' => '', 'geo' => '', 'pub_date' => $today, 'in_reply_to_user_id' => '1',
                'in_reply_to_post_id' => '1', 'reply_count_cache' => 1, 'is_reply_by_friend' => 0,
                'in_retweet_of_post_id' => '', 'old_retweet_count_cache' => 0, 'is_retweet_by_friend' => 0,
                'reply_retweet_distance' => 0, 'network' => 'facebook', 'is_geo_encoded' => 0,
                'in_rt_of_user_id' => '', 'retweet_count_cache' => 0, 'retweet_count_api' => 0,
                'favlike_count_cache' => 0));
            $builders[] = FixtureBuilder::build('hashtags_posts', array('post_id' => $count+14, 'hashtag_id' => 102,
                'network'=>'facebook'));
            $count++;
        }
        return $builders;
    }
}
