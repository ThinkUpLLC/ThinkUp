<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfMinutesViewedInsight.php
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
 * Minutes Viewed Insight
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/minutesviewed.php';

class TestOfMinutesViewedInsight extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testBaselineGeneration() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'minutes_watched'=>4, 'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-40d', 'network'=>'youtube'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'minutes_watched'=>2, 'average_view_percentage'=>54.6));

        $posts[] = new Post($post_builder->columns);
        $posts[] = new Post($post_builder2->columns);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightBaselineMySQLDAO();
        $result = $insight_dao->getInsightBaseline('avg_minutes_viewed_all_time', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'avg_minutes_viewed_all_time');
        $this->assertEqual($result->value, 3);

        $result = $insight_dao->getInsightBaseline('avg_minutes_viewed_month', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'avg_minutes_viewed_month');
        $this->assertEqual($result->value, 4);

        $result = $insight_dao->getInsightBaseline('avg_minutes_viewed_90', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'avg_minutes_viewed_90');
        $this->assertEqual($result->value, 3);

        $result = $insight_dao->getInsightBaseline('all_time_mins_viewed_high', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'all_time_mins_viewed_high');
        $this->assertEqual($result->value, 4);

        $result = $insight_dao->getInsightBaseline('year_mins_viewed_high', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'year_mins_viewed_high');
        $this->assertEqual($result->value, 4);

        $result = $insight_dao->getInsightBaseline('90_mins_viewed_high', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, '90_mins_viewed_high');
        $this->assertEqual($result->value, 4);
    }

    public function testLowEmphasisMinsViewedMonth() {
        // Insert videos to get the average to 3 minutes for the month and >3 minutes for the year.
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>4,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>2,
        'average_view_percentage'=>54.6));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'minutes_watched'=>6,
        'average_view_percentage'=>54.6));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder5 = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'5', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube'));
        $video_builder5 = FixtureBuilder::build('videos', array('id'=>5, 'post_key'=>'5', 'minutes_watched'=>20,
        'average_view_percentage'=>54.6));

        // Only supply the insight with a limited number of the videos so that other insights are not generated
        // preventing the insight we want to test from being generated.
        $posts[] = new Post($post_builder2->columns);
        $posts[] = new Post($post_builder3->columns);
        $posts[] = new Post($post_builder4->columns);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();

        $result = $insight_dao->getInsight('minutes_viewed3', 1, date ('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 3 for a total of 6 minutes.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 0);
        $this->assertEqual($result->filename, 'minutesviewed');
    }

    public function testLowEmphasisMinsViewedAllTime() {
        // Insert videos to get the average to 5 minutes for the for the year.
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>4,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>2,
        'average_view_percentage'=>54.6));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'minutes_watched'=>6,
        'average_view_percentage'=>54.6));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder5 = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'5', 'author_username'=>'ev',
        'post_text'=>'My Great Video 5', 'pub_date'=>'-41d', 'network'=>'youtube'));
        $video_builder5 = FixtureBuilder::build('videos', array('id'=>5, 'post_key'=>'5', 'minutes_watched'=>12,
        'average_view_percentage'=>54.6));

        // Only supply the insight with a limited number of the videos so that other insights are not generated
        // preventing the insight we want to test from being generated.
        $posts[] = new Post($post_builder->columns);
        $posts[] = new Post($post_builder2->columns);
        $posts[] = new Post($post_builder3->columns);
        $posts[] = new Post($post_builder5->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('minutes_viewed5', 1, date ('Y-m-d', strtotime('-41 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 5 for a total of 12 minutes.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 0);
        $this->assertEqual($result->filename, 'minutesviewed');
    }

    public function testLowEmphasisMinsViewed90() {
        // Insert videos to get the average to 5 minutes for the for the year.
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>4,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>2,
        'average_view_percentage'=>54.6));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'minutes_watched'=>10,
        'average_view_percentage'=>54.6));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder5 = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'5', 'author_username'=>'ev',
        'post_text'=>'My Great Video 5', 'pub_date'=>'-91d', 'network'=>'youtube'));
        $video_builder5 = FixtureBuilder::build('videos', array('id'=>5, 'post_key'=>'5', 'minutes_watched'=>120,
        'average_view_percentage'=>54.6));

        // Only supply the insight with a limited number of the videos so that other insights are not generated
        // preventing the insight we want to test from being generated.

        $posts[] = new Post($post_builder3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('minutes_viewed3', 1, date ('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 3 for a total of 10 minutes.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 0);
        $this->assertEqual($result->filename, 'minutesviewed');
    }

    public function testMediumEmphasisMinsViewedMonth() {
        // Insert videos to get the average to 5 minutes for the for the year.
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>4,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>2,
        'average_view_percentage'=>54.6));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'minutes_watched'=>35,
        'average_view_percentage'=>54.6));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder5 = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'5', 'author_username'=>'ev',
        'post_text'=>'My Great Video 5', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder5 = FixtureBuilder::build('videos', array('id'=>5, 'post_key'=>'5', 'minutes_watched'=>2,
        'average_view_percentage'=>54.6));

        $post_builder6 = FixtureBuilder::build('posts', array('id'=>6, 'post_id'=>'6', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder6 = FixtureBuilder::build('videos', array('id'=>6, 'post_key'=>'6', 'minutes_watched'=>2,
        'average_view_percentage'=>54.6));

        $post_builder7 = FixtureBuilder::build('posts', array('id'=>7, 'post_id'=>'7', 'author_username'=>'ev',
        'post_text'=>'My Great Video 7', 'network'=>'youtube', 'pub_date'=>'-41d'));
        $video_builder7 = FixtureBuilder::build('videos', array('id'=>7, 'post_key'=>'7', 'minutes_watched'=>20,
        'average_view_percentage'=>54.6));

        // Only supply the insight with a limited number of the videos so that other insights are not generated
        // preventing the insight we want to test from being generated.
        $posts[] = new Post($post_builder->columns);
        $posts[] = new Post($post_builder2->columns);
        $posts[] = new Post($post_builder3->columns);
        $posts[] = new Post($post_builder4->columns);
        $posts[] = new Post($post_builder5->columns);
        $posts[] = new Post($post_builder6->columns);
        $posts[] = new Post($post_builder7->columns);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('minutes_viewed3', 1, date ('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 3 for a total of 35 minutes.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 1);
        $this->assertEqual($result->filename, 'minutesviewed');
    }

    public function testMediumEmphasisMinsViewedAllTime() {
        // Insert videos to get the average to 4 minutes for the for the year.
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>4,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>2,
        'average_view_percentage'=>54.6));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'minutes_watched'=>20,
        'average_view_percentage'=>54.6));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder5 = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'5', 'author_username'=>'ev',
        'post_text'=>'My Great Video 5', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder5 = FixtureBuilder::build('videos', array('id'=>5, 'post_key'=>'5', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder6 = FixtureBuilder::build('posts', array('id'=>6, 'post_id'=>'6', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder6 = FixtureBuilder::build('videos', array('id'=>6, 'post_key'=>'6', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        // Only supply the insight with a limited number of the videos so that other insights are not generated
        // preventing the insight we want to test from being generated.
        $posts[] = new Post($post_builder->columns);
        $posts[] = new Post($post_builder2->columns);
        $posts[] = new Post($post_builder3->columns);
        $posts[] = new Post($post_builder4->columns);
        $posts[] = new Post($post_builder5->columns);
        $posts[] = new Post($post_builder6->columns);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('minutes_viewed3', 1, date ('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 3 for a total of 20 minutes.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 1);
        $this->assertEqual($result->filename, 'minutesviewed');
    }

    public function testMediumEmphasisMinsViewed90() {
        // Insert videos to get the average to 4 minutes for the for the year.
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'minutes_watched'=>35,
        'average_view_percentage'=>54.6));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder5 = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'5', 'author_username'=>'ev',
        'post_text'=>'My Great Video 5', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder5 = FixtureBuilder::build('videos', array('id'=>5, 'post_key'=>'5', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder6 = FixtureBuilder::build('posts', array('id'=>6, 'post_id'=>'6', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-91d'));
        $video_builder6 = FixtureBuilder::build('videos', array('id'=>6, 'post_key'=>'6', 'minutes_watched'=>100,
        'average_view_percentage'=>54.6));

        // Only supply the insight with a limited number of the videos so that other insights are not generated
        // preventing the insight we want to test from being generated.
        $posts[] = new Post($post_builder->columns);
        $posts[] = new Post($post_builder2->columns);
        $posts[] = new Post($post_builder3->columns);
        $posts[] = new Post($post_builder4->columns);
        $posts[] = new Post($post_builder5->columns);
        $posts[] = new Post($post_builder6->columns);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('minutes_viewed3', 1, date ('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 3 for a total of 35 minutes.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 1);
        $this->assertEqual($result->filename, 'minutesviewed');
    }


    public function testHighEmphasisMinsViewedMonth() {
        // Insert videos to get the average to 4 minutes for the for the year.
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'network'=>'youtube', 'pub_date'=>'-1d',));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'minutes_watched'=>400,
        'average_view_percentage'=>54.6));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder5 = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'5', 'author_username'=>'ev',
        'post_text'=>'My Great Video 5', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder5 = FixtureBuilder::build('videos', array('id'=>5, 'post_key'=>'5', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder6 = FixtureBuilder::build('posts', array('id'=>6, 'post_id'=>'6', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder6 = FixtureBuilder::build('videos', array('id'=>6, 'post_key'=>'6', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder7 = FixtureBuilder::build('posts', array('id'=>7, 'post_id'=>'7', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder7 = FixtureBuilder::build('videos', array('id'=>7, 'post_key'=>'7', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder8 = FixtureBuilder::build('posts', array('id'=>8, 'post_id'=>'8', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder8 = FixtureBuilder::build('videos', array('id'=>8, 'post_key'=>'8', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder9 = FixtureBuilder::build('posts', array('id'=>9, 'post_id'=>'9', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder9 = FixtureBuilder::build('videos', array('id'=>9, 'post_key'=>'9', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder10 = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>'10', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder10 = FixtureBuilder::build('videos', array('id'=>10, 'post_key'=>'10', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder11 = FixtureBuilder::build('posts', array('id'=>11, 'post_id'=>'11', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-41d'));
        $video_builder11 = FixtureBuilder::build('videos', array('id'=>11, 'post_key'=>'11', 'minutes_watched'=>100,
        'average_view_percentage'=>54.6));

        // Only supply the insight with a limited number of the videos so that other insights are not generated
        // preventing the insight we want to test from being generated.
        $posts[] = new Post($post_builder->columns);
        $posts[] = new Post($post_builder2->columns);
        $posts[] = new Post($post_builder3->columns);
        $posts[] = new Post($post_builder4->columns);
        $posts[] = new Post($post_builder5->columns);
        $posts[] = new Post($post_builder6->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('minutes_viewed3', 1, date ('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 3 for a total of 6 hours.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 2);
        $this->assertEqual($result->filename, 'minutesviewed');
    }

    public function testHighEmphasisMinsViewedAllTime() {
        // Insert videos to get the average to 4 minutes for the for the year.
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'network'=>'youtube', 'pub_date'=>'-1d',  ));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'minutes_watched'=>400,
        'average_view_percentage'=>54.6));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder5 = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'5', 'author_username'=>'ev',
        'post_text'=>'My Great Video 5', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder5 = FixtureBuilder::build('videos', array('id'=>5, 'post_key'=>'5', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder6 = FixtureBuilder::build('posts', array('id'=>6, 'post_id'=>'6', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder6 = FixtureBuilder::build('videos', array('id'=>6, 'post_key'=>'6', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder7 = FixtureBuilder::build('posts', array('id'=>7, 'post_id'=>'7', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder7 = FixtureBuilder::build('videos', array('id'=>7, 'post_key'=>'7', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder8 = FixtureBuilder::build('posts', array('id'=>8, 'post_id'=>'8', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder8 = FixtureBuilder::build('videos', array('id'=>8, 'post_key'=>'8', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder9 = FixtureBuilder::build('posts', array('id'=>9, 'post_id'=>'9', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder9 = FixtureBuilder::build('videos', array('id'=>9, 'post_key'=>'9', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder10 = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>'10', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder10 = FixtureBuilder::build('videos', array('id'=>10, 'post_key'=>'10', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        // Only supply the insight with a limited number of the videos so that other insights are not generated
        // preventing the insight we want to test from being generated.
        $posts[] = new Post($post_builder->columns);
        $posts[] = new Post($post_builder2->columns);
        $posts[] = new Post($post_builder3->columns);
        $posts[] = new Post($post_builder4->columns);
        $posts[] = new Post($post_builder5->columns);
        $posts[] = new Post($post_builder6->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('minutes_viewed3', 1, date ('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 3 for a total of 6 hours.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 2);
        $this->assertEqual($result->filename, 'minutesviewed');
    }

    public function testHighEmphasisMinsViewed90() {
        // Insert videos to get the average to 4 minutes for the for the year.
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'network'=>'youtube', 'pub_date'=>'-1d',  ));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'minutes_watched'=>100,
        'average_view_percentage'=>54.6));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder5 = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'5', 'author_username'=>'ev',
        'post_text'=>'My Great Video 5', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder5 = FixtureBuilder::build('videos', array('id'=>5, 'post_key'=>'5', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder6 = FixtureBuilder::build('posts', array('id'=>6, 'post_id'=>'6', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder6 = FixtureBuilder::build('videos', array('id'=>6, 'post_key'=>'6', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder7 = FixtureBuilder::build('posts', array('id'=>7, 'post_id'=>'7', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder7 = FixtureBuilder::build('videos', array('id'=>7, 'post_key'=>'7', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder8 = FixtureBuilder::build('posts', array('id'=>8, 'post_id'=>'8', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder8 = FixtureBuilder::build('videos', array('id'=>8, 'post_key'=>'8', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder9 = FixtureBuilder::build('posts', array('id'=>9, 'post_id'=>'9', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder9 = FixtureBuilder::build('videos', array('id'=>9, 'post_key'=>'9', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder10 = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>'10', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-91d'));
        $video_builder10 = FixtureBuilder::build('videos', array('id'=>10, 'post_key'=>'10', 'minutes_watched'=>10000,
        'average_view_percentage'=>54.6));

        $post_builder11 = FixtureBuilder::build('posts', array('id'=>11, 'post_id'=>'11', 'author_username'=>'ev',
        'post_text'=>'My Great Video 6', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder11 = FixtureBuilder::build('videos', array('id'=>11, 'post_key'=>'11', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        // Only supply the insight with a limited number of the videos so that other insights are not generated
        // preventing the insight we want to test from being generated.
        $posts[] = new Post($post_builder->columns);
        $posts[] = new Post($post_builder2->columns);
        $posts[] = new Post($post_builder3->columns);
        $posts[] = new Post($post_builder4->columns);
        $posts[] = new Post($post_builder5->columns);
        $posts[] = new Post($post_builder6->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('minutes_viewed3', 1, date ('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 3 for a total of 1 hour.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 2);
        $this->assertEqual($result->filename, 'minutesviewed');
    }

    public function testAllTimeHigh() {
        // Insert some videos to get the all time high to 145 mins
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>1,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=> 'My Great Video 2', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>145,
        'average_view_percentage'=>54.6));

        $posts[] = new Post($post_builder->columns);
        $posts[] = new Post($post_builder2->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('minutes_viewed_high2', 1, date ('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 2 for a total of 2 hours.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 2);
        $this->assertEqual($result->filename, 'minutesviewed');
    }

    public function test365High() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'network'=>'youtube', 'pub_date'=>'-400d'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>1000,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=> 'My Great Video 2', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>145,
        'average_view_percentage'=>54.6));

        $posts[] = new Post($post_builder->columns);
        $posts[] = new Post($post_builder2->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('minutes_viewed_high2', 1, date ('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 2 for a total of 2 hours.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 1);
        $this->assertEqual($result->filename, 'minutesviewed');
    }

    public function test90High() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'network'=>'youtube', 'pub_date'=>'-400d'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'minutes_watched'=>1000,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=> 'My Great Video 2', 'network'=>'youtube', 'pub_date'=>'-361d'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'minutes_watched'=>1450,
        'average_view_percentage'=>54.6));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=> 'My Great Video 3', 'network'=>'youtube', 'pub_date'=>'-1d'));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'minutes_watched'=>145,
        'average_view_percentage'=>54.6));

        $posts[] = new Post($post_builder->columns);
        $posts[] = new Post($post_builder2->columns);
        $posts[] = new Post($post_builder3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $insight = new MinutesViewedInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('minutes_viewed_high3', 1, date ('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $headline = "Viewers watched My Great Video 3 for a total of 2 hours.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 0);
        $this->assertEqual($result->filename, 'minutesviewed');
    }
}
