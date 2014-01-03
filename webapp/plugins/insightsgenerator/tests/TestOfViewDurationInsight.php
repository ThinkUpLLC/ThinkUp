<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfViewDuration.php
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
 * Test of View Duration
 *
 * Tests the view duration insight
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/viewduration.php';

class TestOfViewDurationInsight extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testLowEmphasisViewDurationIncreaseAllTime() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>10));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>34));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder2->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 2 <strong>34%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>12%</strong> longer than <a href="http://plus.google.com/1">ev</a>\'s all-time average.');
        $this->assertEqual($result->emphasis, 0);
    }

    public function testLowEmphasisViewDurationDecreaseAllTime() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>10));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>34));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video <strong>10%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>12%</strong> less than <a href="http://plus.google.com/1">ev</a>\'s all-time average.');
        $this->assertEqual($result->emphasis, 0);
    }

    public function testLowEmphasisViewDurationIncreaseMonth() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>80));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>34));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4',
        'average_view_percentage'=>34));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>45));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video <strong>45%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>8%</strong> longer than <a href="http://plus.google.com/1">ev</a>\'s 30-day average.');
        $this->assertEqual($result->emphasis, 0);
    }

    public function testLowEmphasisViewDurationDecreaseMonth() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>10));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>34));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>55));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder2->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 2 <strong>34%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>10%</strong> less than <a href="http://plus.google.com/1">ev</a>\'s 30-day average.');
        $this->assertEqual($result->emphasis, 0);
    }

    public function testLowEmphasisViewDurationIncrease90() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-401d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>75));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>34));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>55));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 3 <strong>55%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>11%</strong> longer than <a href="http://plus.google.com/1">ev</a>\'s 90-day average.');
        $this->assertEqual($result->emphasis, 0);
    }

    public function testLowEmphasisViewDurationDecrease90() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-401d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>1));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-401d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4',
        'average_view_percentage'=>1));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>34));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>10));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 3 <strong>10%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>12%</strong> less than <a href="http://plus.google.com/1">ev</a>\'s 90-day average.');
        $this->assertEqual($result->emphasis, 0);
    }

    public function testMediumEmphasisViewDurationIncreaseAllTime() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>10));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>64));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder2->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 2 <strong>64%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>27%</strong> longer than <a href="http://plus.google.com/1">ev</a>\'s all-time average.');
        $this->assertEqual($result->emphasis, 1);
    }

    public function testMediumEmphasisViewDurationDecreaseAllTime() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>64));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>10));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder2->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 2 <strong>10%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>27%</strong> less than <a href="http://plus.google.com/1">ev</a>\'s all-time average.');
        $this->assertEqual($result->emphasis, 1);
    }

    public function testMediumEmphasisViewDurationIncreaseMonth() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>100));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>1));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>55));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 3 <strong>55%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>27%</strong> longer than <a href="http://plus.google.com/1">ev</a>\'s 30-day average.');
        $this->assertEqual($result->emphasis, 1);
    }

    public function testMediumEmphasisViewDurationDecreaseMonth() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>1));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>29));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>85));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder2->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 2 <strong>29%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>28%</strong> less than <a href="http://plus.google.com/1">ev</a>\'s 30-day average.');
        $this->assertEqual($result->emphasis, 1);
    }

    public function testMediumEmphasisViewDurationIncrease90() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-410d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>80));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>34));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>75));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video <strong>75%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>21%</strong> longer than <a href="http://plus.google.com/1">ev</a>\'s 90-day average.');
        $this->assertEqual($result->emphasis, 1);
    }

    public function testMediumEmphasisViewDurationDecrease90() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-410d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>1));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>40));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-401d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4',
        'average_view_percentage'=>1));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>10));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video <strong>10%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>15%</strong> less than <a href="http://plus.google.com/1">ev</a>\'s 90-day average.');
        $this->assertEqual($result->emphasis, 1);
    }

    public function testHighEmphasisViewDurationIncreaseAllTime() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>10));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>1));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>90));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 3 <strong>90%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>57%</strong> longer than <a href="http://plus.google.com/1">ev</a>\'s all-time average.');
        $this->assertEqual($result->emphasis, 2);
    }

    public function testHighEmphasisViewDurationDecreaseAllTime() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>100));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>100));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>9));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 3 <strong>9%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>60%</strong> less than <a href="http://plus.google.com/1">ev</a>\'s all-time average.');
        $this->assertEqual($result->emphasis, 2);
    }

    public function testHighEmphasisViewDurationIncreaseMonth() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>100));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>30));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>90));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4',
        'average_view_percentage'=>30));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 3 <strong>90%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>40%</strong> longer than <a href="http://plus.google.com/1">ev</a>\'s 30-day average.');
        $this->assertEqual($result->emphasis, 2);
    }

    public function testHighEmphasisViewDurationDecreaseMonth() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>1));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>55));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>7));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4',
        'average_view_percentage'=>65));

        $post_builder5 = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'5', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder5 = FixtureBuilder::build('videos', array('id'=>5, 'post_key'=>'5',
        'average_view_percentage'=>1));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video 3 <strong>7%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>35%</strong> less than <a href="http://plus.google.com/1">ev</a>\'s 30-day average.');
        $this->assertEqual($result->emphasis, 2);
    }

    public function testHighEmphasisViewDurationIncrease90() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-410d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>100));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>1));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4',
        'average_view_percentage'=>1));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>50));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video <strong>50%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>33%</strong> longer than <a href="http://plus.google.com/1">ev</a>\'s 90-day average.');
        $this->assertEqual($result->emphasis, 2);
    }
    public function testHighEmphasisViewDurationDecrease90() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-410d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>100));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>34));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>10));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration');
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "On average, viewers watched ev's video My Great Video <strong>10%</strong> of the way through.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->text, 'That\'s <strong>38%</strong> less than <a href="http://plus.google.com/1">ev</a>\'s all-time average.');
        $this->assertEqual($result->emphasis, 2);
    }

    public function testAllTimeHigh() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>85));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration_record', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration_record');
        $this->assertPattern('/all-time high/', $result->text);
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "ev's video My Great Video was viewed <strong>85%</strong> of the way through on average.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 2);
    }

    public function testAllTimeLow() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>85));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>5));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder2->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration_record', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration_record');
        // $this->assertPattern('/all-time low/', $result->text); // this one was too depressing
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "ev's video My Great Video 2 was viewed <strong>5%</strong> of the way through on average.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 2);
    }

    public function test365High() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-401d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>85));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>75));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>5));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder2->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration_record', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration_record');
        $this->assertPattern('/365-day record/', $result->text);
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "ev's video My Great Video 2 was viewed <strong>75%</strong> of the way through on average.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 1);
    }

    public function test365Low() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-401d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>4));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>75));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>5));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration_record', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration_record');
        // $this->assertPattern('/365-day record/', $result->text); // nope, too depressing
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "ev's video My Great Video 3 was viewed <strong>5%</strong> of the way through on average.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 1);
    }

    public function test90High() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-401d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>85));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-45d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>75));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-364d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>84));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builde4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4',
        'average_view_percentage'=>5));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder2->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration_record', 1, date('Y-m-d', strtotime('-45 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration_record');
        $this->assertPattern('/90-day record/', $result->text);
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "ev's video My Great Video 2 was viewed <strong>75%</strong> of the way through on average.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 0);
    }

    public function test90Low() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-401d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>2));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-364d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>3));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'average_view_percentage'=>4));

        $post_builder4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-401d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4',
        'average_view_percentage'=>80));

        $post_builder5 = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'5', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-364d', 'network'=>'youtube', 'in_reply_to_post_id'=>null));
        $video_builder5 = FixtureBuilder::build('videos', array('id'=>5, 'post_key'=>'5',
        'average_view_percentage'=>85));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $posts[] = new Post($post_builder3->columns);

        $insight = new ViewDurationInsight();
        $insight->generateInsight($instance, $posts, 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('view_duration_record', 1, date('Y-m-d', strtotime('-1 day')) );
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'view_duration_record');
        $this->assertPattern('/90-day record/', $result->text);
        $this->assertEqual($result->filename, 'viewduration');
        $headline = "ev's video My Great Video 3 was viewed <strong>4%</strong> of the way through on average.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 0);
    }
}

