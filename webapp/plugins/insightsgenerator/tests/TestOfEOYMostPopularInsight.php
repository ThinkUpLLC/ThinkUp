<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYMostPopularInsight.php
 *
 * Copyright (c) 2014-2016 Adam Pash
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
 * Test of EOYMostPopularInsight
 *
 * Test for the EOYMostPopularInsight class.
 *
 * Copyright (c) 2014-2016 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Adam Pash
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoymostpopular.php';

class TestOfEOYMostPopularInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'ev';
        $instance->author_id = '18';
        $instance->network_user_id = '18';
        $instance->network = 'twitter';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testGetMostPopularPost() {
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);

        // set up posts
        // 2nd most popular post
        $builders[] = FixtureBuilder::build('posts', array('id'=>11, 'post_id'=>11,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post that is less popular',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>5, 'is_protected'=>false));

        // 3rd most popular post
        $builders[] = FixtureBuilder::build('posts', array('id'=>12, 'post_id'=>12,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is the least popular post',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>1, 'is_protected'=>false));

        // most popular post
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is the most popular post',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10,
            'retweet_count_cache'=>20,
            'favlike_count_cache'=>30
        ));

        $post_dao = DAOFactory::getDAO('PostDAO');
        $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
            $author_id = $this->instance->network_user_id,
            $network = $this->instance->network
        );

        $insight_plugin = new EOYMostPopularInsight();
        $scored_posts = $insight_plugin->getScoredPosts($last_year_of_posts);
        $this->assertEqual($scored_posts[10], 170);
        // $this->debug(Utils::varDumpToString($scored_posts));
        $top_post = $insight_plugin->getMostPopularPost($this->instance, $scored_posts);
        $this->assertEqual($top_post->retweet_count_cache, 20);
        $this->assertEqual($top_post->reply_count_cache, 10);
        $this->assertEqual($top_post->favlike_count_cache, 30);
        // $this->debug(Utils::varDumpToString($top_post));
    }

    public function testTwitterNormalCase() {
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);

        // set up posts
        // 2nd most popular post
        $builders[] = FixtureBuilder::build('posts', array('id'=>11, 'post_id'=>11,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post that is less popular',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>5, 'is_protected'=>false));

        // most popular post
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is the most popular post',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10,
            'retweet_count_cache'=>20,
            'favlike_count_cache'=>30
        ));

        // 3rd most popular post
        $builders[] = FixtureBuilder::build('posts', array('id'=>12, 'post_id'=>12,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is the least popular post',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>1, 'is_protected'=>false));

        $posts = array();
        $insight_plugin = new EOYMostPopularInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_most_popular', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        // $this->assertEqual(1, count($result->posts));
        $this->assertEqual("@ev's most popular tweet of $year", $result->headline);
        $this->assertEqual("We don't tweet for the glory, but a little attention doesn't " .
            "hurt. With <strong>30 likes, 20 retweets, and 10 replies</strong>, this is @ev's " .
            "most popular tweet of $year.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Twitter");
    }

    public function testFacebookNormalCase() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);

        // set up posts
        // 2nd most popular post
        $builders[] = FixtureBuilder::build('posts', array('id'=>11, 'post_id'=>11,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post that is less popular',
            'source'=>'web',
            'pub_date'=>"$year-05-01",
            'reply_count_cache'=>5, 'is_protected'=>false));

        // 3rd most popular post
        $builders[] = FixtureBuilder::build('posts', array('id'=>12, 'post_id'=>12,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is the least popular post',
            'source'=>'web',
            'pub_date'=>"$year-06-01",
            'reply_count_cache'=>1, 'is_protected'=>false));

        // most popular post
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is the most popular post',
            'source'=>'web',
            'pub_date'=>"$year-07-01",
            'reply_count_cache'=>10,
            'retweet_count_cache'=>20,
            'favlike_count_cache'=>30
        ));

        $posts = array();
        $insight_plugin = new EOYMostPopularInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_most_popular', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Mark Zuckerberg's most popular status update of $year", $result->headline);
        $this->assertEqual("Sometimes you just say the right thing. With <strong>30 " .
            "likes, 20 reshares, and 10 comments</strong>, this is Mark Zuckerberg's " .
            "most popular status update of $year (at least since May).", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Facebook");
    }
}

