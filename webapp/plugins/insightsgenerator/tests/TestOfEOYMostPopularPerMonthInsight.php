<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYMostPopularPerMonthInsight.php
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
 * Test of EOYMostPopularPerMonthInsight
 *
 * Test for the EOYMostPopularPerMonthInsight class.
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoymostpopularpermonth.php';

class TestOfEOYMostPopularPerMonthInsight extends ThinkUpInsightUnitTestCase {

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

    public function testSetPopularPostPosts() {
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);

        // set up posts
        // 2nd most popular post January
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

        // 3rd most popular post January
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

        // most popular post January
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
        // 2nd most popular post Sept
        $builders[] = FixtureBuilder::build('posts', array('id'=>13, 'post_id'=>13,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post that is less popular',
            'source'=>'web',
            'pub_date'=>"$year-09-15",
            'reply_count_cache'=>5, 'is_protected'=>false));

        // 3rd most popular post Sept
        $builders[] = FixtureBuilder::build('posts', array('id'=>14, 'post_id'=>14,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is the least popular post',
            'source'=>'web',
            'pub_date'=>"$year-09-20",
            'reply_count_cache'=>1, 'is_protected'=>false));

        // most popular post Sept
        $builders[] = FixtureBuilder::build('posts', array('id'=>15, 'post_id'=>15,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is the most popular post',
            'source'=>'web',
            'pub_date'=>"$year-09-30",
            'reply_count_cache'=>10,
            'retweet_count_cache'=>20,
            'favlike_count_cache'=>30
        ));

        $post_dao = DAOFactory::getDAO('PostDAO');
        $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
            $author_id = $this->instance->network_user_id,
            $network = $this->instance->network
        );

        $insight_plugin = new EOYMostPopularPerMonthInsight();
        $scored_posts = $insight_plugin->setScoredPosts($last_year_of_posts);
        $this->debug(Utils::varDumpToString($insight_plugin->posts_per_month));
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
            'pub_date'=>"$year-02-01",
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
            'pub_date'=>"$year-04-01",
            'reply_count_cache'=>1, 'is_protected'=>false));

        $posts = array();
        $insight_plugin = new EOYMostPopularPerMonthInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_most_popular_per_month', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        // $this->assertEqual(1, count($result->posts));
        $this->assertEqual("@ev's biggest tweets of each month in $year", $result->headline);
        $this->assertEqual("Twelve months make a year, and this year's almost behind us. Take one last look back ".
            "at @ev's biggest tweets of each month in $year.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Twitter");
    }

    public function testTwitterNormalCaseWithSinceDate() {
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);

        // set up posts
        // October
        $builders[] = FixtureBuilder::build('posts', array('id'=>11, 'post_id'=>11,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post that is less popular',
            'source'=>'web',
            'pub_date'=>"$year-010-01",
            'reply_count_cache'=>5, 'is_protected'=>false));

        // November
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is the most popular post',
            'source'=>'web',
            'pub_date'=>"$year-11-01",
            'reply_count_cache'=>10,
            'retweet_count_cache'=>20,
            'favlike_count_cache'=>30
        ));

        // December
        $builders[] = FixtureBuilder::build('posts', array('id'=>12, 'post_id'=>12,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is the least popular post',
            'source'=>'web',
            'pub_date'=>"$year-12-01",
            'reply_count_cache'=>1, 'is_protected'=>false));

        // Stray June post
        $builders[] = FixtureBuilder::build('posts', array('id'=>13, 'post_id'=>13,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'do not include this post',
            'source'=>'web',
            'pub_date'=>"$year-06-01",
            'reply_count_cache'=>1, 'is_protected'=>false));

        $posts = array();
        // Set earliest post to October
        $this->instance->last_post_id = 11;
        $insight_plugin = new EOYMostPopularPerMonthInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_most_popular_per_month', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/This is the least popular post/', $result->related_data);
        $this->assertNoPattern('/do not include this post/', $result->related_data);
        $this->assertEqual("@ev's biggest tweets of each month in $year", $result->headline);
        $this->assertEqual("Twelve months make a year, and this year's almost behind us. Take one last look back ".
            "at @ev's biggest tweets of each month in $year (at least since October).", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case with since date, Twitter");
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
        $insight_plugin = new EOYMostPopularPerMonthInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_most_popular_per_month', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Mark Zuckerberg's biggest posts of each month in $year", $result->headline);
        $this->assertEqual("This year's about to enter the history books. For better or for worse, these were ".
            "Mark Zuckerberg's most popular status updates of each month of $year ".
            "(at least since May).", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Facebook");
    }
}

