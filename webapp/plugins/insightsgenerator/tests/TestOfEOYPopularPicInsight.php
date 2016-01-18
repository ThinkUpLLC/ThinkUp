<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYPopularPicInsight.php
 *
 * Copyright (c) 2012-2016 Gina Trapani
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
 * Test of EOYPopularPicInsight
 *
 * Test for the EOYPopularPicInsight class.
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoypopularpic.php';

class TestOfEOYPopularPicInsight extends ThinkUpInsightUnitTestCase {

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

    public function testGetScoredPics() {
        // set up posts with photos
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);
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
            'image_src' => 'http://pic.twitter.com.foo.jpg',
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
            'image_src' => 'http://pic.twitter.com.foo.jpg',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'
        ));

        // set up one post with no photo
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

        $insight_plugin = new EOYPopularPicInsight();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $last_year_of_posts = $post_dao->getThisYearOfPostsWithLinksIterator(
            $author_id = $this->instance->network_user_id,
            $network = $this->instance->network
        );

        $scored_pics = $insight_plugin->getScoredPics($last_year_of_posts);
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
        // set up posts with photos
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);

        // most popular pic
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a v popular pic',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>10,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => 'https://pbs.twimg.com/media/B25u8s7CYAAQyxN.jpg',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'));

        // 2nd most popular pic
        $builders[] = FixtureBuilder::build('posts', array('id'=>11, 'post_id'=>11,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a less popular pic',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>5, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>11,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => 'https://pbs.twimg.com/media/B25s8QlIgAAi5Ky.jpg',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'));

        // 3rd most popular pic
        $builders[] = FixtureBuilder::build('posts', array('id'=>12, 'post_id'=>12,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY w/the least popular pic',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>1, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>12,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => 'https://pbs.twimg.com/media/B25t1ZvIgAAkdfv.jpg',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'));

        $posts = array();
        $insight_plugin = new EOYPopularPicInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_pic', $this->instance->id, $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        // $this->assertEqual(1, count($result->posts));
        $this->assertEqual("@ev's most popular picture on Twitter, $year", $result->headline);
        $this->assertEqual("With tweets limited to 140 characters, a picture is worth " .
            "at least 1,000 characters. In $year, these were the most popular pics @ev " .
            "shared on Twitter.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Twitter");
    }

    public function testTwitterOneMatch() {
        // set up posts with photos
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);
        // most popular pic
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Twitter User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a v popular pic',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>10,
            'url'=>'http://t.co/B5LAotKMWY',
            'image_src' => 'https://pbs.twimg.com/media/B25u8s7CYAAQyxN.jpg',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'));

        $posts = array();
        $insight_plugin = new EOYPopularPicInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_pic', $this->instance->id, $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        // $this->assertEqual(1, count($result->posts));
        $this->assertEqual("@ev's most popular picture on Twitter, $year", $result->headline);
        $this->assertEqual("With tweets limited to 140 characters, a picture is worth " .
            "at least 1,000 characters. In $year, this was the most popular pic @ev " .
            "shared on Twitter.", $result->text);

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
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a v popular pic',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10, 'is_protected'=>false));

        $posts = array();
        $insight_plugin = new EOYPopularPicInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_pic', $this->instance->id, $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        // $this->assertEqual(1, count($result->posts));
        $this->assertEqual("@ev must yearn for the text-only days of Twitter", $result->headline);
        $this->assertEqual("@ev didn't share any pics on Twitter this year. Bummer! " .
            "On the plus side: @ev probably doesn't need to worry about leaked nudes!", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "No matches, Twitter");
    }

    public function testFacebookNormalCase() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);
        // most popular pic
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a v popular pic',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>10,
            'url'=>'https://www.facebook.com/photo.php?afdsadf',
            'image_src' => 'https://pbs.twimg.com/media/B25u8s7CYAAQyxN.jpg',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'));

        // 2nd most popular pic
        $builders[] = FixtureBuilder::build('posts', array('id'=>11, 'post_id'=>11,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a less popular pic',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>5, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>11,
            'url'=>'https://www.facebook.com/photo.php?afdsadf',
            'image_src' => 'https://pbs.twimg.com/media/B25s8QlIgAAi5Ky.jpg',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'));

        // 3rd most popular pic
        $builders[] = FixtureBuilder::build('posts', array('id'=>12, 'post_id'=>12,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY w/the least popular pic',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>1, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>12,
            'url'=>'https://www.facebook.com/photo.php?afdsadf',
            'image_src' => 'https://pbs.twimg.com/media/B25t1ZvIgAAkdfv.jpg',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'));

        $posts = array();
        $insight_plugin = new EOYPopularPicInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_pic', $this->instance->id, $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Mark Zuckerberg's most popular picture on Facebook, $year", $result->headline);
        $this->assertEqual("What's a newsfeed without the photos? In $year, these " .
            "were the most popular pics Mark Zuckerberg shared on Facebook.",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Facebook");
    }

    public function testFacebookOneMatch() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);
        // most popular pic
        $builders[] = FixtureBuilder::build('posts', array('id'=>10, 'post_id'=>10,
            'author_username'=>$this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id,
            'author_fullname'=>'Facebook User',
            'author_avatar'=>'avatar.jpg',
            'network'=>$this->instance->network,
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a v popular pic',
            'source'=>'web',
            'pub_date'=>"$year-03-01",
            'reply_count_cache'=>10, 'is_protected'=>false));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>10,
            'url'=>'https://www.facebook.com/photo.php?afdsadf',
            'image_src' => 'https://pbs.twimg.com/media/B25u8s7CYAAQyxN.jpg',
            'expanded_url' => 'http://pic.twitter.com/vx4YL7Yz'));

        $posts = array();
        $insight_plugin = new EOYPopularPicInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_pic', $this->instance->id, $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Mark Zuckerberg's most popular picture on Facebook, $year", $result->headline);
        $this->assertEqual("What's a newsfeed without the photos? In $year, this " .
            "was the most popular pic Mark Zuckerberg shared on Facebook (at least since March).",
            $result->text);

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
            'post_text'=>'This is a post http://t.co/B5LAotKMWY with a v popular pic',
            'source'=>'web',
            'pub_date'=>"$year-01-01",
            'reply_count_cache'=>10, 'is_protected'=>false));

        $posts = array();
        $insight_plugin = new EOYPopularPicInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_popular_pic', $this->instance->id, $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("No photos on Facebook?", $result->headline);
        $this->assertEqual("Mark Zuckerberg didn't share any pics on Facebook this year. " .
            "Bummer! On the plus side: " .
            "Mark Zuckerberg probably doesn't need to worry about leaked nudes!", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "No matches, Facebook");
    }
}

