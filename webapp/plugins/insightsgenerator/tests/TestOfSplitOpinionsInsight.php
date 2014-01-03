<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfSplitOpinionsInsight.php
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
 * Test of Split Opinions Insight
 *
 * Test for SplitOpinionsInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/splitopinions.php';

class TestOfSplitOpinionsInsight extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testSplitOpinionsLikes() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>60, 'dislikes'=>40));

        $posts[] = new Post($post_builder->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $split_opinions_insight = new SplitOpinionsInsight();
        $split_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('split_opinions1', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'split_opinions1');
        $this->assertEqual($result->filename, 'splitopinions');
        $this->assertEqual($result->emphasis, 1);
        $this->assertEqual($result->headline, "My Great Video really touched a nerve!");
        $text = "/60% of people liked ";
        $text .= "<a href=\"http:\/\/plus\.google\.com\/1\/about\"\>ev\<\/a\>'s";
        $text .= " video \<a href=\"http:\/\/www\.youtube\.com\/watch\?v=1\">My Gre";
        $text .= "at Video\<\/a\> and 40% disliked it\./";
        $this->assertPattern($text, $result->text);
    }

    public function testSplitOpinionsDislikes() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>40, 'dislikes'=>60));

        $posts[] = new Post($post_builder->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $split_opinions_insight = new SplitOpinionsInsight();
        $split_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('split_opinions1', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'split_opinions1');
        $this->assertEqual($result->filename, 'splitopinions');
        $this->assertEqual($result->headline, "My Great Video really touched a nerve!");
        $text = "/40% of people liked ";
        $text .= "<a href=\"http:\/\/plus\.google\.com\/1\/about\"\>ev\<\/a\>'s";
        $text .= " video \<a href=\"http:\/\/www\.youtube\.com\/watch\?v=1\">My Gre";
        $text .= "at Video\<\/a\> and 60% disliked it\./";
        $this->assertPattern($text, $result->text);
    }

    public function testSplitOpinionsEqualSplit() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'likes'=>50, 'dislikes'=>50));

        $posts[] = new Post($post_builder->columns);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';

        $split_opinions_insight = new SplitOpinionsInsight();
        $split_opinions_insight->generateInsight($instance, $posts, 7);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('split_opinions1', 1, date ('Y-m-d',strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'split_opinions1');
        $this->assertEqual($result->filename, 'splitopinions');
        $this->assertEqual($result->headline, "My Great Video really touched a nerve!");
        $this->assertEqual($result->emphasis, 1);
        $text = "/50% of people liked ";
        $text .= "<a href=\"http:\/\/plus\.google\.com\/1\/about\"\>ev\<\/a\>'s";
        $text .= " video \<a href=\"http:\/\/www\.youtube\.com\/watch\?v=1\">My Gre";
        $text .= "at Video\<\/a\> and 50% disliked it\./";
        $this->assertPattern($text, $result->text);
    }

}
