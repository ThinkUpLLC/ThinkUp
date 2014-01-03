<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfLikeSpikeInsight.php
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
 *
 * TestOfLikeSpike
 *
 * Tests the like spike insight
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair aaronkalair@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/likespike.php';

class TestOfLikeSpikeInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testBaseLines() {
        $insight_baseline_dao = new InsightBaselineMySQLDAO();

        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video 1', 'pub_date'=>'-1d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>10));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-20d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>20));

        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3',
        'author_username'=>'ev', 'post_text'=>'My Great Video 3', 'pub_date'=>'-50d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>30));

        $posts[] = new Post($post1->columns);
        $posts[] = new Post($post2->columns);
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $like_spike_insight = new LikeSpikeInsight();
        $like_spike_insight->generateInsight($instance, $posts, 7);

        // Check the average for 30 days is correct
        $avg_30 = $insight_baseline_dao->getInsightBaseline('avg_like_count_last_30_days', 1);
        $this->assertEqual($avg_30->value, 15);

        // Check the high for 30 days is correct
        $high_30 = $insight_baseline_dao->getInsightBaseline('high_like_count_last_30_days', 1);
        $this->assertEqual($high_30->value, 20);

        // Check the average for 90 days is correct
        $avg_90 = $insight_baseline_dao->getInsightBaseline('avg_like_count_last_90_days', 1);
        $this->assertEqual($avg_90->value, 20);

        // Check the high for 90 days is correct
        $high_90 = $insight_baseline_dao->getInsightBaseline('high_like_count_last_90_days', 1);
        $this->assertEqual($high_90->value, 30);

        // Check the high for 365 days is correct
        $high_365 = $insight_baseline_dao->getInsightBaseline('high_like_count_last_365_days', 1);
        $this->assertEqual($high_365->value, 30);
    }

    public function test365DayHigh() {
        $insight_dao = new InsightMySQLDAO();

        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video 1', 'pub_date'=>'-1d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>50));

        $posts[] = new Post($post1->columns);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $like_spike_insight = new LikeSpikeInsight();
        $like_spike_insight->generateInsight($instance, $posts, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('like_high_365_day_1', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'like_high_365_day_1');
        $text = "<strong>50 people</strong> liked <a href=http://plus.google.com/1>ev</a>'s video <a href=http://www.y";
        $text .= "outube.com/watch?v=1>My Great Video 1</a>.";
        $this->assertEqual($check->headline, $text);
        $this->assertEqual($check->emphasis, 2);
        $this->assertEqual($check->filename, 'likespike');
    }

    public function test90DayHigh() {
        $insight_dao = new InsightMySQLDAO();

        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video 1', 'pub_date'=>'-95d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>50));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>40));


        $posts[] = new Post($post1->columns);
        $posts[] = new Post($post2->columns);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $like_spike_insight = new LikeSpikeInsight();
        $like_spike_insight->generateInsight($instance, $posts, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('like_high_90_day_2', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'like_high_90_day_2');
        $this->assertPattern('/new 90-day record/', $check->text);
        $text = "<strong>40 people</strong> liked <a href=http://plus.google.com/1>ev</a>'s video <a href=http://www.y";
        $text .= "outube.com/watch?v=2>My Great Video 2</a>.";
        $this->assertEqual($check->headline, $text);
        $this->assertEqual($check->emphasis, 2);
        $this->assertEqual($check->filename, 'likespike');
    }

    public function test30DayHigh() {
        $insight_dao = new InsightMySQLDAO();

        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video 1', 'pub_date'=>'-95d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>50));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-85d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>40));

        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3',
        'author_username'=>'ev', 'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>30));

        $posts[] = new Post($post1->columns);
        $posts[] = new Post($post2->columns);
        $posts[] = new Post($post3->columns);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $like_spike_insight = new LikeSpikeInsight();
        $like_spike_insight->generateInsight($instance, $posts, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('like_high_30_day_3', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'like_high_30_day_3');
        $this->assertPattern('/30-day record/', $check->text);
        $headline = "<strong>30 people</strong> liked <a href=http://plus.google.com/1>ev</a>'s video <a href=http://www.y";
        $headline .= "outube.com/watch?v=3>My Great Video 3</a>.";
        $this->assertEqual($check->headline, $headline);
        $this->assertEqual($check->emphasis, 2);
        $this->assertEqual($check->filename, 'likespike');
    }

    public function test90DayAverage() {
        $insight_dao = new InsightMySQLDAO();

        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video 1', 'pub_date'=>'-1d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>500));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>40));

        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3',
        'author_username'=>'ev', 'post_text'=>'My Great Video 3', 'pub_date'=>'-3d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>30));

        $posts[] = new Post($post1->columns);
        $posts[] = new Post($post2->columns);
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $like_spike_insight = new LikeSpikeInsight();
        $like_spike_insight->generateInsight($instance, $posts, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('like_spike_90_day_1', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'like_spike_90_day_1');
        $text = "<strong>500 people</strong> liked <a href=http://plus.google.com/1>ev</a>'s video <a href=http://www.y";
        $text .= "outube.com/watch?v=1>My Great Video 1</a>, more than <strong>double</strong> the 90-day average.";
        $this->assertEqual($check->headline, $text);
        $this->assertEqual($check->emphasis, 0);
        $this->assertEqual($check->filename, 'likespike');
    }

    public function test30DayAverage() {
        $insight_dao = new InsightMySQLDAO();

        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video 1', 'pub_date'=>'-85d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>800));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>40));

        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3',
        'author_username'=>'ev', 'post_text'=>'My Great Video 3', 'pub_date'=>'-3d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>30));

        $post4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4',
        'author_username'=>'ev', 'post_text'=>'My Great Video 4', 'pub_date'=>'-1d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'likes'=>400));

        $posts[] = new Post($post2->columns);
        $posts[] = new Post($post3->columns);
        $posts[] = new Post($post4->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $like_spike_insight = new LikeSpikeInsight();
        $like_spike_insight->generateInsight($instance, $posts, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('like_spike_30_day_4', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'like_spike_30_day_4');
        $text = "<strong>400 people</strong> liked <a href=http://plus.google.com/1>ev</a>'s video <a href=http://www.y";
        $text .= "outube.com/watch?v=4>My Great Video 4</a>, more than <strong>double</strong> the 30-day average.";
        $this->assertEqual($check->headline, $text);
        $this->assertEqual($check->emphasis, 0);
        $this->assertEqual($check->filename, 'likespike');
    }
}

