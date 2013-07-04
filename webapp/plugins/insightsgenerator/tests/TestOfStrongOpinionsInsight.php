<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfStrongOpinionsInsight.php
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
 * Test of Strong Opinions Insight
 *
 * Test for StrongOpinionsInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/strongopinions.php';

class TestOfStrongOpinionsInsight extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testGenerateBaselines() {
        $post1 = FixtureBuilder::build('posts', array('id' => 1, 'post_id' => '1',
        'author_username' => 'ev', 'post_text' => 'My great video', 'pub_date' => '-2d',
        'network' => 'youtube'));
        $video1 = FixtureBuilder::build('videos', array( 'post_key' => 1, 'likes' => 99, 'dislikes' => 1));

        $post2 = FixtureBuilder::build('posts', array('id' => 2, 'post_id' => '2',
        'author_username' => 'ev', 'post_text' => 'My great video 2', 'pub_date' => '-33d',
        'network' => 'youtube'));
        $video2 = FixtureBuilder::build('videos', array( 'post_key' => 2, 'likes' => 50, 'dislikes' => 50));

        $posts[] = new Post($post1->columns);
        $posts[] = new Post($post2->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '1';
        $instance->network = 'youtube';
        $instance->network_username = 'ev';
        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);

        $baseline_dao = new InsightBaselineMySQLDAO();
        $result1 = $baseline_dao->getInsightBaseline('avg_like_percentage_all_time', $instance->id, date('Y-m-d'));
        $this->assertEqual($result1->value, 74);

        $result2 = $baseline_dao->getInsightBaseline('avg_dislike_percentage_all_time', $instance->id, date('Y-m-d'));
        $this->assertEqual($result2->value, 25);

        $result3 = $baseline_dao->getInsightBaseline('avg_like_percentage_1_month', $instance->id, date('Y-m-d'));
        $this->assertEqual($result3->value, 99);

        $result4 = $baseline_dao->getInsightBaseline('avg_dislike_percentage_1_month', $instance->id, date('Y-m-d'));
        $this->assertEqual($result4->value, 1);

        $result5 = $baseline_dao->getInsightBaseline('high_like_percentage_all_time', $instance->id, date('Y-m-d'));
        $this->assertEqual($result5->value, 99);

        $result6 = $baseline_dao->getInsightBaseline('high_dislike_percentage_all_time', $instance->id, date('Y-m-d'));
        $this->assertEqual($result6->value, 50);
    }

    public function testHighLikesChangeMonth() {
        // Insert videos to get the month like average to just 30%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>1, 'dislikes'=>99));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>1, 'dislikes'=>99));
        // Like % for this video is 90% so 90-30 = 60 and this will generate the insight
        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>90, 'dislikes'=>10));

        $post4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'pub_date'=>'-41d', 'network'=>'youtube' ));
        $video4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'likes'=>99, 'dislikes'=>1));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 2);
        $this->assertEqual($result->text, 'My Great Video 3 got 60% more likes than your monthly average');
    }

    public function testHighLikesChangeYear() {
        // Insert videos to get the year like average to just 30%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>1, 'dislikes'=>99));
        // Like % for this video is 90% so 90-30 = 60 and this will generate the insight
        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>1, 'dislikes'=>99));

        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>90, 'dislikes'=>10));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 2);
        $this->assertEqual($result->text, 'My Great Video 3 got 60% more likes than your all time average');
    }

    public function testHighDislikesChangeMonth() {
        // Insert videos to get the month dislike average to just 30%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>99, 'dislikes'=>1));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>99, 'dislikes'=>1));
        // Dislike % for this video is 90% so 90-30 = 60 and this will generate the insight
        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>10, 'dislikes'=>90));

        $post4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'pub_date'=>'-41d', 'network'=>'youtube' ));
        $video4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'likes'=>1, 'dislikes'=>99));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 2);
        $this->assertEqual($result->text, 'My Great Video 3 got 60% more dislikes than your monthly average');
    }

    public function testHighDislikesChangeYear() {
        // Insert videos to get the year dislike average to just 30%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>99, 'dislikes'=>1));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>99, 'dislikes'=>1));
        // Dislike % for this video is 90% so 90-30 = 60 and this will generate the insight
        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>10, 'dislikes'=>90));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 2);
        $this->assertEqual($result->text, 'My Great Video 3 got 60% more dislikes than your all time average');
    }

    public function testMediumLikesChangeMonth() {
        // Insert videos to get the month like average to 20%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>1, 'dislikes'=>99));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>1, 'dislikes'=>99));
        // This videos like percentage is 60, 60-20 = 40 so this video will generate the insight
        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>60, 'dislikes'=>40));

        $post4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'pub_date'=>'-41d', 'network'=>'youtube' ));
        $video4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'likes'=>100, 'dislikes'=>0));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 1);
        $this->assertEqual($result->text, 'My Great Video 3 got 40% more likes than your monthly average');
    }

    public function testMediumLikesChangeYear() {
        // Insert videos to get the year like average to 25%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>1, 'dislikes'=>99));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>1, 'dislikes'=>99));
        // This videos like percentage is 66.66, 66.66-25 = 41.67 so this video will generate the insight
        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>80, 'dislikes'=>40));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 1);
        $this->assertEqual($result->text, 'My Great Video 3 got 41.67% more likes than your all time average');
    }

    public function testMediumDislikesChangeMonth() {
        // Insert videos to get the month dislike average to 20%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>99, 'dislikes'=>1));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>99, 'dislikes'=>1));
        // This videos dislike percentage is 60, 60-20 = 40 so this video will generate the insight
        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>40, 'dislikes'=>60));

        $post4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'pub_date'=>'-41d', 'network'=>'youtube' ));
        $video4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'likes'=>0, 'dislikes'=>100));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 1);
        $this->assertEqual($result->text, 'My Great Video 3 got 40% more dislikes than your monthly average');
    }

    public function testMediumDislikesChangeYear() {
        // Insert videos to get the year dislike average to 25%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>99, 'dislikes'=>1));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>99, 'dislikes'=>1));
        // This videos dislike percentage is 66.66, 66.66-25 = 41.67 so this video will generate the insight
        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>40, 'dislikes'=>80));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 1);
        $this->assertEqual($result->text, 'My Great Video 3 got 41.67% more dislikes than your all time average');
    }

    public function testLowLikesChangeMonth() {
        // Insert videos to get the month like average to 12%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>1, 'dislikes'=>99));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>1, 'dislikes'=>99));
        // This videos like percentage is 35, 35-12 = 23 so this video will generate the insight
        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>35, 'dislikes'=>65));

        $post4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'pub_date'=>'-41d', 'network'=>'youtube' ));
        $video4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'likes'=>100, 'dislikes'=>0));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 0);
        $this->assertEqual($result->text, 'My Great Video 3 got 23% more likes than your monthly average');
    }

    public function testLowLikesChangeYear() {
        // Insert videos to get the month like average to 12%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>1, 'dislikes'=>99));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>1, 'dislikes'=>99));
        // This videos like percentage is 35, 35-12 = 23 so this video will generate the insight
        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>35, 'dislikes'=>65));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 0);
        $this->assertEqual($result->text, 'My Great Video 3 got 23% more likes than your all time average');
    }

    public function testLowLDislikesChangeMonth() {
        // Insert videos to get the month like average to 12%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>99, 'dislikes'=>1));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>99, 'dislikes'=>1));
        // This videos like percentage is 35, 35-12 = 23 so this video will generate the insight
        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>65, 'dislikes'=>35));

        $post4 = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'4', 'author_username'=>'ev',
        'post_text'=>'My Great Video 4', 'pub_date'=>'-41d', 'network'=>'youtube' ));
        $video4 = FixtureBuilder::build('videos', array('id'=>4, 'post_key'=>'4', 'likes'=>0, 'dislikes'=>100));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 0);
        $this->assertEqual($result->text, 'My Great Video 3 got 23% more dislikes than your monthly average');
    }

    public function testLowDislikesChangeYear() {
        // Insert videos to get the month like average to 12%
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>99, 'dislikes'=>1));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>99, 'dislikes'=>1));
        // This videos like percentage is 35, 35-12 = 23 so this video will generate the insight
        $post3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3', 'author_username'=>'ev',
        'post_text'=>'My Great Video 3', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3', 'likes'=>65, 'dislikes'=>35));

        // Only supply the insight generator with a single post so we generate the version we're testing
        $posts[] = new Post($post3->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions3', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions3');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 0);
        $this->assertEqual($result->text, 'My Great Video 3 got 23% more dislikes than your all time average');
    }

    public function testLikeHigh() {
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>99, 'dislikes'=>1));

        $posts[] = new Post($post1->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions_high1', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions_high1');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 2);
        $this->assertEqual($result->text, '99% of people liked My Great Video a new all time high');
    }

    public function testDislikeHigh() {
        $post1 = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video1 = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>1, 'dislikes'=>99));

        $post2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'likes'=>99, 'dislikes'=>1));

        $posts[] = new Post($post1->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $strong_opinions_insight = new StrongOpinionsInsight();
        $strong_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('strong_opinions_high1', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'strong_opinions_high1');
        $this->assertEqual($result->prefix, 'Your fans have spoken:');
        $this->assertEqual($result->filename, 'strongopinions');
        $this->assertEqual($result->emphasis, 2);
        $this->assertEqual($result->text, '99% of people disliked My Great Video a new all time high');
    }
}
