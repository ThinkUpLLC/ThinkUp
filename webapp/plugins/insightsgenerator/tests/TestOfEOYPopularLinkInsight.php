<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYPopularLinkInsight.php
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
 * Test of EOYPopularLinkInsight
 *
 * Test for the EOYPopularLinkInsight class.
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoypopularlink.php';

class TestOfEOYPopularLinkInsight extends ThinkUpInsightUnitTestCase {

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

    public function testGetScoredLinks() {
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);

        // set up posts with links (not photos)
        $builders[] = FixtureBuilder::build('posts', array(
            'id'=>10,
            'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10,
            'is_protected'=>false
        ));
        $builders[] = FixtureBuilder::Build('links', array(
            'post_key'=>10,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => '',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'
        ));
        $builders[] = FixtureBuilder::build('posts', array(
            'id'=>12,
            'post_id'=>12,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>12,
            'is_protected'=>false
        ));
        $builders[] = FixtureBuilder::Build('links', array(
            'post_key'=>12,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => '',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'
        ));

        // set up posts with photos
        $builders[] = FixtureBuilder::build('posts', array(
            'id'=>13,
            'post_id'=>13,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>12,
            'is_protected'=>false
        ));
        $builders[] = FixtureBuilder::Build('links', array(
            'post_key'=>13,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => 'http://pic.twitter.com.foo.jpg',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'
        ));

        // set up one post with no link
        $builders[] = FixtureBuilder::build('posts', array(
            'id'=>11,
            'post_id'=>11,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This post has no photo',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>1,
            'is_protected'=>false
        ));

        $insight_plugin = new EOYPopularLinkInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $last_year_of_posts = $post_dao->getThisYearOfPostsWithLinksIterator(
            $author_id = $this->instance->network_user_id,
            $network = $this->instance->network
        );

        $scored_pics = $insight_plugin->getScoredLinks($last_year_of_posts);
        $sorted_pics = array(
            0 => array(12 => 60),
            1 => array(10 => 50)
        );

        $i = 0;
        foreach ($scored_pics as $post_id => $score) {
            $this->assertEqual($sorted_pics[$i][$post_id], $score);
            $i++;
        }
        $this->assertEqual($i, 2);
    }

    public function testTwitterNormalCase() {
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);

        // set up posts with links
        // 2nd most popular link
        $builders[] = FixtureBuilder::build('posts', array('id'=>11, 'post_id'=>11,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a less popular link',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>5, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>11,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => '',
            'expanded_url' => 'http://gawker.com/less-popular-link'));

        // 3rd most popular link
        $builders[] = FixtureBuilder::build('posts', array('id'=>12, 'post_id'=>12,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY w/the least popular link',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>1, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>12,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => '',
            'expanded_url' => 'http://dashes.com/third-place-is-pretty-good!'));

        // most popular link
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a v popular link',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>10,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => '',
            'expanded_url' => 'http://nytimes.com/vx4YL7Yz'));

        $posts = array();
        $insight_plugin = new EOYPopularLinkInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_link', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        // $this->assertEqual(1, count($result->posts));
        $this->assertEqual("@ev's most popular links on Twitter, $year", $result->headline);
        $this->assertEqual("The wealth of the web, shared in a constant 23 characters: " .
            "These are the most popular links @ev shared on Twitter in $year.",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Twitter");
    }

    public function testTwitterOneMatch() {
        // set up posts with photos
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);
        // most popular link
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a v popular link',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>10,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => '',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'));

        $posts = array();
        $insight_plugin = new EOYPopularLinkInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_link', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        // $this->assertEqual(1, count($result->posts));
        $this->assertEqual("@ev's most popular link on Twitter, $year", $result->headline);
        $this->assertEqual("The wealth of the web, shared in a constant 23 characters: " .
            "This is the most popular link @ev shared on Twitter in $year.",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "One match, Twitter");
    }

    public function testTwitterNoMatch() {
        // set up post; no photos
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);
        // most popular pic
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post with no link',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10, 'is_protected'=>false));

        $posts = array();
        $insight_plugin = new EOYPopularLinkInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_link', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        // $this->assertEqual(1, count($result->posts));
        $this->assertEqual("@ev's words are good enough", $result->headline);
        $this->assertEqual("@ev didn't share any links on Twitter in $year. Crazy, @ev!",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "No matches, Twitter");
    }

    public function testFacebookNormalCase() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);

        // set up posts with links
        // most popular link
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a v popular link',
            'source'=>'web',
            'pub_date'=>"$year-05-01",
            'reply_count_cache'=>10, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>10,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => '',
            'expanded_url' => 'http://nytimes.com/vx4YL7Yz'));

        // 2nd most popular link
        $builders[] = FixtureBuilder::build('posts', array('id'=>11, 'post_id'=>11,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a less popular link',
            'source'=>'web',
            'pub_date'=>"$year-04-01",
            'reply_count_cache'=>5, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>11,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => '',
            'expanded_url' => 'http://gawker.com/less-popular-link'));

        // 3rd most popular link
        $builders[] = FixtureBuilder::build('posts', array('id'=>12, 'post_id'=>12,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY w/the least popular link',
            'source'=>'web',
            'pub_date'=>"$year-03-01",
            'reply_count_cache'=>1, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>12,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => '',
            'expanded_url' => 'http://dashes.com/third-place-is-pretty-good!'));

        $posts = array();
        $insight_plugin = new EOYPopularLinkInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_link', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Mark Zuckerberg's most popular links on Facebook, $year", $result->headline);
        $this->assertEqual("We laughed, we cried, we linked. These are the most popular " .
            "links Mark Zuckerberg shared on Facebook in $year (at least since March).", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Facebook");
    }

    public function testFacebookOneMatch() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);
        // most popular link
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a v popular link',
            'source'=>'web',
            'pub_date'=>"$year-03-01",
            'reply_count_cache'=>10, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>10,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => '',
            'expanded_url' => 'http://example.com/linkin'));

        $posts = array();
        $insight_plugin = new EOYPopularLinkInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_link', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Mark Zuckerberg's most popular link on Facebook, $year", $result->headline);
        $this->assertEqual("We laughed, we cried, we linked. This is the most popular " .
            "link Mark Zuckerberg shared on Facebook in $year (at least since March).", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "One match, Facebook");
    }

    public function testFacebookNoMatch() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);
        // post with no pic
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post with no link',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10, 'is_protected'=>false));

        $posts = array();
        $insight_plugin = new EOYPopularLinkInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_link', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("No links on Facebook?", $result->headline);
        $this->assertEqual("Mark Zuckerberg didn't link to anything on Facebook in $year. " .
            "The internet promises to try harder, next year.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "No matches, Facebook");
    }
}

