<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfViewSpikeInsight.php
 *
 * Copyright (c) 2013 Aaron Kalair
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
 * Test of View Spike Insight
 *
 * Test for ViewSpikeInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/viewspike.php';

class TestOfViewSpikeInsight extends ThinkUpUnitTestCase {

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
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'views'=>10));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-20d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'views'=>20));

        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3',
        'author_username'=>'ev', 'post_text'=>'My Great Video 3', 'pub_date'=>'-50d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'views'=>30));

        $posts[] = new Post($post1->columns);
        $posts[] = new Post($post2->columns);
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $view_spike_insight = new ViewSpikeInsight();
        $view_spike_insight->generateInsight($instance, $posts, 7);

        // Check the average for 30 days is correct
        $avg_30 = $insight_baseline_dao->getInsightBaseline('avg_view_count_last_30_days', 1);
        $this->assertEqual($avg_30->value, 15);

        // Check the high for 30 days is correct
        $high_30 = $insight_baseline_dao->getInsightBaseline('high_view_count_last_30_days', 1);
        $this->assertEqual($high_30->value, 20);

        // Check the average for 90 days is correct
        $avg_90 = $insight_baseline_dao->getInsightBaseline('avg_view_count_last_90_days', 1);
        $this->assertEqual($avg_90->value, 20);

        // Check the high for 90 days is correct
        $high_90 = $insight_baseline_dao->getInsightBaseline('high_view_count_last_90_days', 1);
        $this->assertEqual($high_90->value, 30);

        // Check the high for 365 days is correct
        $high_365 = $insight_baseline_dao->getInsightBaseline('high_view_count_last_365_days', 1);
        $this->assertEqual($high_365->value, 30);
    }

    public function test365DayHigh() {
        $insight_dao = new InsightMySQLDAO();

        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video 1', 'pub_date'=>'-1d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'views'=>50));

        $posts[] = new Post($post1->columns);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $view_spike_insight = new ViewSpikeInsight();
        $view_spike_insight->generateInsight($instance, $posts, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('view_high_365_day_1', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'view_high_365_day_1');
        $this->assertPattern('/365-day record/', $check->text);
        $headline = '/<strong>50 people<\/strong> viewed ev\'s video My Great Video 1\./';
        $this->assertPattern($headline, $check->headline);
        $this->assertEqual($check->emphasis, 2);
        $this->assertEqual($check->filename, 'viewspike');
    }

    public function test90DayHigh() {
        $insight_dao = new InsightMySQLDAO();

        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video 1', 'pub_date'=>'-95d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'views'=>50));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'views'=>40));


        $posts[] = new Post($post1->columns);
        $posts[] = new Post($post2->columns);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $view_spike_insight = new ViewSpikeInsight();
        $view_spike_insight->generateInsight($instance, $posts, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('view_high_90_day_2', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'view_high_90_day_2');
        $this->assertPattern('/90-day record/', $check->text);
        $headline = "<strong>40 people</strong> viewed ev's video My Great Video 2.";
        $this->assertEqual($check->headline, $headline);
        $this->assertEqual($check->emphasis, 2);
        $this->assertEqual($check->filename, 'viewspike');
    }

    public function test30DayHigh() {
        $insight_dao = new InsightMySQLDAO();

        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video 1', 'pub_date'=>'-95d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'views'=>50));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-85d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'views'=>40));

        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3',
        'author_username'=>'ev', 'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'views'=>30));

        $posts[] = new Post($post1->columns);
        $posts[] = new Post($post2->columns);
        $posts[] = new Post($post3->columns);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $view_spike_insight = new ViewSpikeInsight();
        $view_spike_insight->generateInsight($instance, $posts, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('view_high_30_day_3', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'view_high_30_day_3');
        $this->assertPattern('/30-day record/', $check->text);
        $headline = "<strong>30 people</strong> viewed ev's video My Great Video 3.";
        $this->assertEqual($check->headline, $headline);
        $this->assertEqual($check->emphasis, 2);
        $this->assertEqual($check->filename, 'viewspike');
    }

    public function test90DayAverage() {
        $insight_dao = new InsightMySQLDAO();

        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video 1', 'pub_date'=>'-1d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'views'=>500));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'views'=>40));

        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3',
        'author_username'=>'ev', 'post_text'=>'My Great Video 3', 'pub_date'=>'-3d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'views'=>30));

        $posts[] = new Post($post1->columns);
        $posts[] = new Post($post2->columns);
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $view_spike_insight = new ViewSpikeInsight();
        $view_spike_insight->generateInsight($instance, $posts, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('view_spike_90_day_1', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'view_spike_90_day_1');
        $headline = "<strong>500 people</strong> viewed My Great Video 1";
        $headline .= " &mdash; looks like it's going viral.";
        $this->assertEqual($check->headline, $headline);
        $this->assertEqual($check->emphasis, 0);
        $this->assertEqual($check->filename, 'viewspike');
    }

    public function test30DayAverage() {
        $insight_dao = new InsightMySQLDAO();

        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video 1', 'pub_date'=>'-85d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'views'=>800));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'views'=>40));

        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3',
        'author_username'=>'ev', 'post_text'=>'My Great Video 3', 'pub_date'=>'-3d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'views'=>30));

        $post4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4',
        'author_username'=>'ev', 'post_text'=>'My Great Video 4', 'pub_date'=>'-1d', 'network'=>'youtube',
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id' =>null));
        $video4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'views'=>400));

        $posts[] = new Post($post2->columns);
        $posts[] = new Post($post3->columns);
        $posts[] = new Post($post4->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $view_spike_insight = new ViewSpikeInsight();
        $view_spike_insight->generateInsight($instance, $posts, 7);

        // Check the insight was created
        $check = $insight_dao->getInsight('view_spike_30_day_4', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($check);
        $this->assertEqual($check->slug, 'view_spike_30_day_4');
        $headline = "<strong>400 people</strong> viewed My Great Video 4";
        $headline .= " &mdash; looks like it's doing pretty well.";
        $this->assertEqual($check->headline, $headline);
        $this->assertEqual($check->emphasis, 0);
        $this->assertEqual($check->filename, 'viewspike');
    }
}
