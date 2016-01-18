<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFavoritedLinksInsight.php
 *
 * Copyright (c) 2012-2016 Nilaksh Das, Gina Trapani
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
 * Test of FavoriteFlashbacksInsight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/favoriteflashbacks.php';

class TestOfFavoriteFlashbacksInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testFavoriteFlashbackTwitter1YearAgo() {
        // Get data ready that insight requires
        $builders = self::buildData('twitter', 1);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = '7612345';
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FavoriteFlashbackInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('favorites_year_ago_flashback', 10, $today);
        $fav_posts = unserialize($result->related_data);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('@testeriffic liked @twitteruser\'s tweet from 1 year ago', $result->headline);
        $this->assertEqual('Can you believe how fast time flies?', $result->text);
        $this->assertIsA($fav_posts, "array");
        $this->assertIsA($fav_posts["posts"][0], "Post");
        $this->assertEqual(count($fav_posts), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFavoriteFlashbackInstagramPhoto1YearAgo() {
        // Get data ready that insight requires
        $builders = self::buildData('instagram', 1);

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = '7612345';
        $instance->network_username = 'testeriffic';
        $instance->network = 'instagram';
        $insight_plugin = new FavoriteFlashbackInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('favorites_year_ago_flashback', 10, $today);
        $fav_posts = unserialize($result->related_data);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('testeriffic liked twitteruser\'s photo from 1 year ago', $result->headline);
        $this->assertEqual('Can you believe how fast time flies?', $result->text);
        $this->assertIsA($fav_posts, "array");
        $this->assertIsA($fav_posts["posts"][0], "Post");
        $this->assertEqual(count($fav_posts), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFavoriteFlashbackInstagramVideo1YearAgo() {
        // Get data ready that insight requires
        $builders = self::buildData('instagram', 1);

        // Set this particular photo to a video
        $builders[] = FixtureBuilder::build('photos', array('post_id'=>133, 'post_key'=>133, 'is_short_video'=>1));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = '7612345';
        $instance->network_username = 'testeriffic';
        $instance->network = 'instagram';
        $insight_plugin = new FavoriteFlashbackInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('favorites_year_ago_flashback', 10, $today);
        $fav_posts = unserialize($result->related_data);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('testeriffic liked twitteruser\'s video from 1 year ago', $result->headline);
        $this->assertEqual('Can you believe how fast time flies?', $result->text);
        $this->assertIsA($fav_posts, "array");
        $this->assertIsA($fav_posts["posts"][0], "Post");
        $this->assertEqual(count($fav_posts), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFavoriteFlashbackTwitter3YearsAgo() {
        // Get data ready that insight requires
        $builders = self::buildData('twitter', 3);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = '7612345';
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FavoriteFlashbackInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('favorites_year_ago_flashback', 10, $today);
        $fav_posts = unserialize($result->related_data);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('@testeriffic liked @twitteruser\'s tweet from 3 years ago', $result->headline);
        $this->assertEqual('Can you believe how fast time flies?', $result->text);
        $this->assertIsA($fav_posts, "array");
        $this->assertIsA($fav_posts["posts"][0], "Post");
        $this->assertEqual(count($fav_posts), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFavoriteFlashbackFacebook1YearAgo() {
        // Get data ready that insight requires
        $builders = self::buildData('facebook', 1);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = '7612345';
        $instance->network_username = 'testeriffic';
        $instance->network = 'facebook';
        $insight_plugin = new FavoriteFlashbackInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('favorites_year_ago_flashback', 10, $today);
        $fav_posts = unserialize($result->related_data);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('testeriffic liked twitteruser\'s status update from 1 year ago', $result->headline);
        $this->assertEqual('Can you believe how fast time flies?', $result->text);
        $this->assertIsA($fav_posts, "array");
        $this->assertIsA($fav_posts["posts"][0], "Post");
        $this->assertEqual(count($fav_posts), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFavoriteFlashbackFacebook3YearsAgo() {
        // Get data ready that insight requires
        $builders = self::buildData('facebook', 3);
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = '7612345';
        $instance->network_username = 'testeriffic';
        $instance->network = 'facebook';
        $insight_plugin = new FavoriteFlashbackInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('favorites_year_ago_flashback', 10, $today);
        $fav_posts = unserialize($result->related_data);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('testeriffic liked twitteruser\'s status update from 3 years ago', $result->headline);
        $this->assertEqual('Can you believe how fast time flies?', $result->text);
        $this->assertIsA($fav_posts, "array");
        $this->assertIsA($fav_posts["posts"][0], "Post");
        $this->assertEqual(count($fav_posts), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    private function buildData($network, $years_ago) {
        $builders = array();

        $now = date('Y-m-d H:i:s');
        $yesterday = date('Y-m-d H:i:s', strtotime('yesterday'));
        $year_ago = date('Y-m-d', strtotime("-".$years_ago." years"));

        $builders[] = FixtureBuilder::build('posts', array('id'=>133, 'post_id'=>133, 'author_user_id'=>'7654321',
            'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'network'=>$network, 'post_text'=>'This is a simple post.', 'source'=>'web',
            'pub_date'=>$year_ago, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>134, 'post_id'=>134, 'author_user_id'=>'7654321',
            'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'network'=>$network, 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
            'pub_date'=>$year_ago, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>134, 'url'=>'http://t.co/B5LAotKMWY',
            'expanded_url' => 'https://pushover.net/'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135, 'author_user_id'=>'7654321',
            'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'network'=>$network, 'post_text'=>'This is an old post http://t.co/aMHh5XHGfS with a link.',
            'source'=>'web', 'pub_date'=>$year_ago, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>135, 'url'=>'http://t.co/aMHh5XHGfS',
            'expanded_url' => 'http://techcrunch.com/2013/04/28/disrupt-ny-hackathon-gets-hacked-man-'.
            'takes-stage-and-uses-his-60-seconds-to-disrupt-capitalism/'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>136, 'post_id'=>136, 'author_user_id'=>'7654322',
            'author_username'=>'fbuser', 'author_fullname'=>'Facebook User', 'author_avatar'=>'avatar.jpg',
            'network'=>'facebook', 'post_text'=>'This is a simple post.', 'source'=>'web',
            'pub_date'=>$year_ago, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>137, 'post_id'=>137, 'author_user_id'=>'7654322',
            'author_username'=>'fbuser', 'author_fullname'=>'Facebook User', 'author_avatar'=>'avatar.jpg',
            'network'=>'facebook', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
            'pub_date'=>$year_ago, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>137, 'url'=>'http://t.co/B5LAotKMW',
            'expanded_url' => ''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>138, 'post_id'=>138, 'author_user_id'=>'7654322',
            'author_username'=>'fbuser', 'author_fullname'=>'Facebook User', 'author_avatar'=>'avatar.jpg',
            'network'=>'facebook', 'post_text'=>'This is another post http://t.co/thtfuoy8 with a link.',
            'source'=>'web', 'pub_date'=>$year_ago, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>138, 'url'=>'http://t.co/thtfuoy8',
            'expanded_url' => ''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>139, 'post_id'=>139, 'author_user_id'=>'7654322',
            'author_username'=>'fbuser', 'author_fullname'=>'Facebook User', 'author_avatar'=>'avatar.jpg',
            'network'=>'facebook', 'post_text'=>'This is an old post http://t.co/aMHh5XHGfS with a link.',
            'source'=>'web', 'pub_date'=>$year_ago, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>139, 'url'=>'http://t.co/aMHh5XHGfS',
            'expanded_url' => 'http://techcrunch.com/2014/04/28/'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>140, 'post_id'=>140, 'author_user_id'=>'7654323',
            'author_username'=>'gplususer', 'author_fullname'=>'Google Plus User', 'author_avatar'=>'avatar.jpg',
            'network'=>'google+', 'post_text'=>'This is a simple post.', 'source'=>'web',
            'pub_date'=>$year_ago, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>141, 'post_id'=>141, 'author_user_id'=>'7654323',
            'author_username'=>'gplususer', 'author_fullname'=>'Google Plus User', 'author_avatar'=>'avatar.jpg',
            'network'=>'google+', 'post_text'=>'This is another simple post.', 'source'=>'web',
            'pub_date'=>$year_ago, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>142, 'post_id'=>142, 'author_user_id'=>'7654323',
            'author_username'=>'gplususer', 'author_fullname'=>'Google Plus User', 'author_avatar'=>'avatar.jpg',
            'network'=>'google+', 'post_text'=>'This is an old post http://t.co/aMHh5XHGfS with a link.',
            'source'=>'web', 'pub_date'=>$year_ago, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>142, 'url'=>'http://t.co/aMHh5XHGfS',
            'expanded_url' => 'http://techcrunch.com/2013/04/28/'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>133, 'author_user_id'=>'7654321',
            'fav_of_user_id'=>7612345, 'network'=>$network, 'fav_timestamp' => $year_ago));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>134, 'author_user_id'=>'7654321',
            'fav_of_user_id'=>7612345, 'network'=>$network, 'fav_timestamp' => $year_ago));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>135, 'author_user_id'=>'7654321',
            'fav_of_user_id'=>7612345, 'network'=>$network, 'fav_timestamp' => $year_ago));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>136, 'author_user_id'=>'7654322',
            'fav_of_user_id'=>7612345, 'network'=>'facebook', 'fav_timestamp' => $year_ago));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>137, 'author_user_id'=>'7654322',
            'fav_of_user_id'=>7612345, 'network'=>'facebook', 'fav_timestamp' => $year_ago));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>138, 'author_user_id'=>'7654322',
            'fav_of_user_id'=>7612345, 'network'=>'facebook', 'fav_timestamp' => $year_ago));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>139, 'author_user_id'=>'7654322',
            'fav_of_user_id'=>7612345, 'network'=>'facebook', 'fav_timestamp' => $yesterday));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>140, 'author_user_id'=>'7654323',
            'fav_of_user_id'=>7612345, 'network'=>'google+', 'fav_timestamp' => $year_ago));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>141, 'author_user_id'=>'7654323',
            'fav_of_user_id'=>7612345, 'network'=>'google+', 'fav_timestamp' => $year_ago));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>142, 'author_user_id'=>'7654323',
            'fav_of_user_id'=>7612345, 'network'=>'google+', 'fav_timestamp' => $yesterday));

        return $builders;
    }
}
