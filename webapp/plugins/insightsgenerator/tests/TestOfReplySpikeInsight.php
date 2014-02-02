<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfReplySpikeInsight.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * Test of Reply Spike Insight
 *
 * Test for ReplySpikeInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/replyspike.php';

class TestOfReplySpikeInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testBaseLines() {
        $insight_baseline_dao = new InsightBaselineMySQLDAO();

        $post1_builder = FixtureBuilder::build('posts', array('id'=>28, 'post_id'=>'28',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 28',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>10, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

        $post2_builder = FixtureBuilder::build('posts', array('id'=>29, 'post_id'=>'29',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 29',
        'source'=>'web', 'pub_date'=>'-20d', 'reply_count_cache'=>20, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

        $post3_builder = FixtureBuilder::build('posts', array('id'=>30, 'post_id'=>'30',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 30',
        'source'=>'web', 'pub_date'=>'-50d', 'reply_count_cache'=>30, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

        $post1 = new Post($post1_builder->columns);
        $post2 = new Post($post2_builder->columns);
        $post3 = new Post($post3_builder->columns);
        $posts[] = $post1;
        $posts[] = $post2;
        $posts[] = $post3;

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';

        $reply_spike_insight = new ReplySpikeInsight();
        $reply_spike_insight->generateInsight($instance, $posts, 7);

        // Check the average for 7 days is correct
        $avg_7 = $insight_baseline_dao->getInsightBaseline('avg_reply_count_last_7_days', 1);
        $this->assertEqual($avg_7->value, 10);

        // Check the high for 7 days is correct
        $high_7 = $insight_baseline_dao->getInsightBaseline('high_reply_count_last_7_days', 1);
        $this->assertEqual($high_7->value, 10);

        // Check the average for 30 days is correct
        $avg_30 = $insight_baseline_dao->getInsightBaseline('avg_reply_count_last_30_days', 1);
        $this->assertEqual($avg_30->value, 15);

        // Check the high for 30 days is correct
        $high_30 = $insight_baseline_dao->getInsightBaseline('high_reply_count_last_30_days', 1);
        $this->assertEqual($high_30->value, 20);

        // Check the high for 365 days is correct
        $high_365 = $insight_baseline_dao->getInsightBaseline('high_reply_count_last_365_days', 1);
        $this->assertEqual($high_365->value, 30);
    }

    // public function test365DayHigh() {
    //     $insight_dao = new InsightMySQLDAO();

    //     // Insert a new post a related hot posts insight
    //     $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>1,
    //     'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d' ));

    //     $post1_builder = FixtureBuilder::build('posts', array('id'=>28, 'post_id'=>'28',
    //     'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
    //     'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 28',
    //     'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>50, 'is_protected'=>0,
    //     'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
    //     'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

    //     $post1 = new Post($post1_builder->columns);
    //     $posts[] = $post1;
    //     $instance = new Instance();
    //     $instance->id = 1;
    //     $instance->network_user_id = '13';
    //     $instance->network = 'twitter';
    //     $instance->network_username = 'ev';

    //     $reply_spike_insight = new ReplySpikeInsight();
    //     $reply_spike_insight->generateInsight($instance, $posts, 7);

    //     // Check the insight was created
    //     $check = $insight_dao->getInsight('reply_high_365_day_28', 1, date('Y-m-d', strtotime('-1 day')));
    //     $this->assertNotNull($check);
    //     $this->assertEqual($check->slug, 'reply_high_365_day_28');
    //     $this->assertPattern('/Why do you think/', $check->text);
    //     $this->assertEqual($check->headline, 'That tweet got <strong>50 replies</strong> &mdash; your 365-day high!');
    //     $this->assertEqual($check->emphasis, 2);
    //     $this->assertEqual($check->filename, 'replyspike');
    // }

    // public function test30DayHigh() {
    //     $insight_dao = new InsightMySQLDAO();

    //     // Insert a new post with a higher count than the baseline and a related hot posts insight
    //     $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>1,
    //     'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d' ));

    //     $post1_builder = FixtureBuilder::build('posts', array('id'=>28, 'post_id'=>'28',
    //     'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
    //     'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 28',
    //     'source'=>'web', 'pub_date'=>'-34d', 'reply_count_cache'=>50, 'is_protected'=>0,
    //     'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
    //     'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

    //     $post2_builder = FixtureBuilder::build('posts', array('id'=>29, 'post_id'=>'29',
    //     'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
    //     'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 29',
    //     'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>40, 'is_protected'=>0,
    //     'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
    //     'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

    //     $post1 = new Post($post1_builder->columns);
    //     $posts[] = $post1;
    //     $post2 = new Post($post2_builder->columns);
    //     $posts[] = $post2;
    //     $instance = new Instance();
    //     $instance->id = 1;
    //     $instance->network_user_id = '13';
    //     $instance->network = 'twitter';
    //     $instance->network_username = 'ev';

    //     $reply_spike_insight = new ReplySpikeInsight();
    //     $reply_spike_insight->generateInsight($instance, $posts, 7);

    //     // Check the insight was created
    //     $check = $insight_dao->getInsight('reply_high_30_day_29', 1, date('Y-m-d', strtotime('-1 day')));
    //     $this->assertNotNull($check);
    //     $this->assertEqual($check->slug, 'reply_high_30_day_29');
    //     $this->assertPattern('/new 30-day record./', $check->text);
    //     $this->assertEqual($check->headline, 'This tweet got replies from <strong>40 people</strong>.');
    //     $this->assertEqual($check->emphasis, 2);
    //     $this->assertEqual($check->filename, 'replyspike');
    // }

    public function test7DayHigh() {
        $insight_dao = new InsightMySQLDAO();

        // Insert a new post with a higher count than the baseline and a related hot posts insight
        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>1,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d' ));

        $post1_builder = FixtureBuilder::build('posts', array('id'=>28, 'post_id'=>'28',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 28',
        'source'=>'web', 'pub_date'=>'-34d', 'reply_count_cache'=>50, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

        $post2_builder = FixtureBuilder::build('posts', array('id'=>29, 'post_id'=>'29',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 28',
        'source'=>'web', 'pub_date'=>'-24d', 'reply_count_cache'=>40, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

        $post3_builder = FixtureBuilder::build('posts', array('id'=>30, 'post_id'=>'30',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 29',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>30, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

        $post1 = new Post($post1_builder->columns);
        $posts[] = $post1;
        $post2 = new Post($post2_builder->columns);
        $posts[] = $post2;
        $post3 = new Post($post3_builder->columns);
        $posts[] = $post3;
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';

        $reply_spike_insight = new ReplySpikeInsight();
        $reply_spike_insight->generateInsight($instance, $posts, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('reply_high_7_day_30', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'reply_high_7_day_30');
        $this->assertPattern('/new 7-day record./', $check->text);
        $this->assertEqual($check->headline, '<strong>30 people</strong> replied to @ev\'s tweet.');
        $this->assertEqual($check->emphasis, 2);
        $this->assertEqual($check->filename, 'replyspike');
    }

    // public function test30DayAverage() {
    //     $insight_dao = new InsightMySQLDAO();

    //     // Insert a new post with a higher count than the baseline and a related hot posts insight
    //     $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>1,
    //     'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-2d' ));

    //     $post1_builder = FixtureBuilder::build('posts', array('id'=>28, 'post_id'=>'28',
    //     'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
    //     'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 28',
    //     'source'=>'web', 'pub_date'=>'-5d', 'reply_count_cache'=>15, 'is_protected'=>0,
    //     'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
    //     'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

    //     $post2_builder = FixtureBuilder::build('posts', array('id'=>29, 'post_id'=>'29',
    //     'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
    //     'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 28',
    //     'source'=>'web', 'pub_date'=>'-10d', 'reply_count_cache'=>3, 'is_protected'=>0,
    //     'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
    //     'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

    //     $post3_builder = FixtureBuilder::build('posts', array('id'=>30, 'post_id'=>'30',
    //     'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
    //     'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 30',
    //     'source'=>'web', 'pub_date'=>'-20d', 'reply_count_cache'=>3, 'is_protected'=>0,
    //     'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
    //     'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

    //     $post4_builder = FixtureBuilder::build('posts', array('id'=>31, 'post_id'=>'31',
    //     'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
    //     'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 31',
    //     'source'=>'web', 'pub_date'=>'-27d', 'reply_count_cache'=>4, 'is_protected'=>0,
    //     'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
    //     'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

    //     $post5_builder = FixtureBuilder::build('posts', array('id'=>32, 'post_id'=>'32',
    //     'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
    //     'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 32',
    //     'source'=>'web', 'pub_date'=>'-34d', 'reply_count_cache'=>31, 'is_protected'=>0,
    //     'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
    //     'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

    //     $post1 = new Post($post1_builder->columns);
    //     $posts[] = $post1;
    //     $post2 = new Post($post2_builder->columns);
    //     $posts[] = $post2;
    //     $post3 = new Post($post3_builder->columns);
    //     $posts[] = $post3;
    //     $post4 = new Post($post4_builder->columns);
    //     $posts[] = $post4;
    //     $post5 = new Post($post5_builder->columns);
    //     $posts[] = $post5;
    //     $instance = new Instance();
    //     $instance->id = 1;
    //     $instance->network_user_id = '13';
    //     $instance->network = 'twitter';
    //     $instance->network_username = 'ev';

    //     $reply_spike_insight = new ReplySpikeInsight();
    //     $reply_spike_insight->generateInsight($instance, $posts, 7);

    //     // Now insert a post with more replies than on average
    //     $post6_builder = FixtureBuilder::build('posts', array('id'=>33, 'post_id'=>'33',
    //     'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
    //     'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 31',
    //     'source'=>'web', 'pub_date'=>'-2d', 'reply_count_cache'=>26, 'is_protected'=>0,
    //     'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
    //     'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));
    //     $posts2[] = new Post($post6_builder->columns);

    //     $reply_spike_insight->generateInsight($instance, $posts2, 7);

    //     // Check the insight was created
    //     $check = $insight_dao->getInsight('reply_high_30_day_33', 1, date('Y-m-d', strtotime('-2 days')));
    //     $this->assertNotNull($check);
    //     $this->assertEqual($check->slug, 'reply_high_30_day_33');
    //     $this->assertPattern('/new 30-day record./', $check->text);
    //     $this->assertEqual($check->headline,
    //     'This tweet got replies from <strong>26 people</strong>.');
    //     $this->assertEqual($check->emphasis, 2);
    //     $this->assertEqual($check->filename, 'replyspike');
    // }

    public function test7DayAverage() {
        $insight_dao = new InsightMySQLDAO();

        // Insert a new post with a higher count than the baseline and a related hot posts insight
        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>1,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-6d' ));

        $post1_builder = FixtureBuilder::build('posts', array('id'=>28, 'post_id'=>'28',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 28',
        'source'=>'web', 'pub_date'=>'-5d', 'reply_count_cache'=>3, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

        $post2_builder = FixtureBuilder::build('posts', array('id'=>29, 'post_id'=>'29',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 28',
        'source'=>'web', 'pub_date'=>'-3d', 'reply_count_cache'=>3, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

        $post3_builder = FixtureBuilder::build('posts', array('id'=>30, 'post_id'=>'30',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 29',
        'source'=>'web', 'pub_date'=>'-2d', 'reply_count_cache'=>3, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

        $post4_builder = FixtureBuilder::build('posts', array('id'=>31, 'post_id'=>'31',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 31',
        'source'=>'web', 'pub_date'=>'-20d', 'reply_count_cache'=>200, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));

        $post1 = new Post($post1_builder->columns);
        $posts[] = $post1;
        $post2 = new Post($post2_builder->columns);
        $posts[] = $post2;
        $post3 = new Post($post3_builder->columns);
        $posts[] = $post3;
        $post4 = new Post($post4_builder->columns);
        $posts[] = $post4;
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';

        $reply_spike_insight = new ReplySpikeInsight();
        $reply_spike_insight->generateInsight($instance, $posts, 7);

        // Now insert a post with more replies than on average
        $post5_builder = FixtureBuilder::build('posts', array('id'=>32, 'post_id'=>'32',
        'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 32',
        'source'=>'web', 'pub_date'=>'-6d', 'reply_count_cache'=>12, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'in_reply_to_user_id' =>null));
        $posts2[] = new Post($post5_builder->columns);

        $reply_spike_insight->generateInsight($instance, $posts2, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('reply_high_7_day_32', 1, date('Y-m-d', strtotime('-6 days')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'reply_high_7_day_32');
        $this->assertPattern('/new 7-day record./', $check->text);
        $this->assertEqual($check->headline,
        '<strong>12 people</strong> replied to @ev\'s tweet.');
        $this->assertEqual($check->emphasis, 2);
        $this->assertEqual($check->filename, 'replyspike');
    }

}
