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
 * Test of FavoritedLinksInsight
 *
 * Test for the FavoritedLinksInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2016 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/favoritedlinks.php';

class TestOfFavoritedLinksInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        TimeHelper::setTime(2); // Force one headline
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testFavoritedLinksInsightForTwitter() {
        // Get data ready that insight requires
        $builders = self::buildData();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FavoritedLinksInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $fav_posts = unserialize($result->related_data);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('The latest link @testeriffic liked', $result->headline);
        $this->assertPattern('|^@testeriffic liked 1 tweet with a link in it.$|',
            $result->text);
        $this->assertIsA($fav_posts, "array");
        $this->assertIsA($fav_posts["posts"][0], "Post");
        $this->assertEqual(count($fav_posts), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFavoritedLinksInsightForGooglePlusNoFavoritedLinks() {
        // Get data ready that insight requires
        $builders = self::buildData();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'google+';
        $insight_plugin = new FavoritedLinksInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $this->assertNull($result);
    }

    public function testExcludingPhotoLinks() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $now = date('Y-m-d H:i:s');
        $today = date ('Y-m-d');
        $insight_dao  = DAOFactory::getDAO('InsightDAO');
        $insight_plugin = new FavoritedLinksInsight();
        $builders[] = FixtureBuilder::build('posts', array('id'=>134, 'post_id'=>134, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>134, 'url'=>'http://t.co/mcr7QsU7Ki',
            'expanded_url' => 'https://twitter.com/TaylorBiglerDC/status/425346150397247489/photo/1',
            'fav_timestamp' => $now));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>134, 'author_user_id'=>7654321,
        'fav_of_user_id'=>7612345, 'network'=>'twitter'));

        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $this->assertNull($result);

        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>135, 'url'=>'http://pic.twitter.com/vx4YL7Yz',
            'expanded_url' => ''));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>135, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $now));
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $this->assertNull($result);
    }

    public function testMultipleLinksInASinglePost() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $now = date('Y-m-d H:i:s');
        $today = date ('Y-m-d');
        $insight_dao  = DAOFactory::getDAO('InsightDAO');
        $insight_plugin = new FavoritedLinksInsight();
        $builders[] = FixtureBuilder::build('posts', array('id'=>134, 'post_id'=>134, 'author_user_id'=>7654321,
            'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'network'=>'twitter', 'post_text'=>'2 links: http://t.co/mcr7QsU7Ki and http://inarow.net', 'source'=>'web',
            'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>134, 'url'=>'http://t.co/mcr7QsU7Ki',
            'expanded_url' => 'http://google.com/'));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>134, 'url'=>'http://inarow.net',
            'expanded_url' => ''));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>134, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $now));

        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('The latest links @testeriffic liked', $result->headline);
        $this->assertPattern('|@testeriffic liked 1 tweet with <strong>2 links</strong> in it.|', $result->text);

        $email = $this->getRenderedInsightInEmail($result);
        $count = substr_count($email, '2 links:');
        $this->assertEqual(1, $count);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($email);
    }

    public function testMultipleLinksAndPosts() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $now = date('Y-m-d H:i:s');
        $today = date ('Y-m-d');
        $insight_dao  = DAOFactory::getDAO('InsightDAO');
        $insight_plugin = new FavoritedLinksInsight();
        $builders[] = FixtureBuilder::build('posts', array('id'=>134, 'post_id'=>134, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'2 links: http://t.co/mcr7QsU7Ki2 and http://inarow.net', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>134, 'url'=>'http://t.co/mcr7QsU7Ki2',
            'expanded_url' => 'http://google.com/2'));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>134, 'url'=>'http://inarow.net',
            'expanded_url' => ''));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>134, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $now));

        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'2 links: http://t.co/mcr7QsU7Ki3 and http://inarow.net/fish', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>135, 'url'=>'http://t.co/mcr7QsU7Ki3',
            'expanded_url' => 'http://google.com/moogley'));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>135, 'url'=>'http://inarow.net/fish',
            'expanded_url' => ''));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>135, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $now));

        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('The latest links @testeriffic liked', $result->headline);
        $this->assertPattern('|@testeriffic liked 2 tweets with <strong>4 links</strong> in them.|', $result->text);
        $fav_posts = unserialize($result->related_data);
        $this->assertEqual(2, count($fav_posts['posts']));
        $this->assertEqual(2, count($fav_posts['posts'][0]->links));
        $this->assertEqual(2, count($fav_posts['posts'][1]->links));
    }

    public function testMaxPostsReturnedTwitter() {
        $builders = self::buildDataExceedingMax('twitter');

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $now = date('Y-m-d H:i:s');
        $today = date ('Y-m-d');
        $insight_dao  = DAOFactory::getDAO('InsightDAO');
        $insight_plugin = new FavoritedLinksInsight();

        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('The latest links @testeriffic liked', $result->headline);
        $this->assertPattern('|Here are the latest links from tweets @testeriffic liked.|', $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testForLinkDedupification() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'refavey';
        $instance->network = 'twitter';

        $now = date('Y-m-d H:i:s');
        $abitago = date('Y-m-d H:i:s', time() - (60*21));
        $awhileago = date('Y-m-d H:i:s', time() - (60*60*25));
        $this->assertNull($result);
        $today = date ('Y-m-d');
        $insight_dao  = DAOFactory::getDAO('InsightDAO');
        $insight_plugin = new FavoritedLinksInsight();
        $builders[] = FixtureBuilder::build('posts', array('id'=>134, 'post_id'=>134, 'author_user_id'=>7654321,
        'author_username'=>'first_link_poster', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
        'pub_date'=>$abitago, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>134, 'url'=>'http://t.co/B5LAotKMWY',
            'expanded_url' => 'https://link.com/', 'title' => 'Some Link'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>134, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $now));

        $builders[] = FixtureBuilder::build('posts', array('id'=>136, 'post_id'=>136, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'A different post with the same link:  http://t.co/B5LAotKMWY.', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>136, 'url'=>'http://t.co/B5LAotKMWY',
            'expanded_url' => 'http://link.com/', 'title' => 'Some Link'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>136, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $now));

        $builders[] = FixtureBuilder::build('posts', array('id'=>138, 'post_id'=>138, 'author_user_id'=>7654321,
        'author_username'=>'first_google_lover', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'http://t.co/A is a link', 'source'=>'web',
        'pub_date'=>$abitago, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>138, 'url'=>'http://t.co/A',
            'expanded_url' => 'http://google.com/','title'=>'Google'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>138, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $now));

        $builders[] = FixtureBuilder::build('posts', array('id'=>140, 'post_id'=>140, 'author_user_id'=>7654321,
        'author_username'=>'google lover', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'http://t.co/B resolves to the same thing!', 'source'=>'web',
        'pub_date'=>$awhileago, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>140, 'url'=>'http://t.co/B',
            'expanded_url' => 'http://google.com/','title'=>'Google'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>140, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $now));


        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $this->assertEqual('@refavey liked 4 tweets with <strong>2 links</strong> in them.', $result>title);
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['posts']), 2);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateHeadline() {
        TimeHelper::setTime(3);
        // Get data ready that insight requires
        $builders = self::buildData();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new FavoritedLinksInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $fav_posts = unserialize($result->related_data);
        $this->assertNotNull($result);
        $this->assertEqual('1 link @testeriffic liked', $result->headline);
        $this->assertEqual(count($fav_posts), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $builders[] = FixtureBuilder::build('posts', array('id'=>999, 'post_id'=>999, 'author_user_id'=>7654321,
            'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'network'=>'twitter', 'post_text'=>'This is an old post http://t.co/aMHh5XHGfS with a link.', 'source'=>'web',
            'pub_date'=>$yesterday, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>999, 'url'=>'http://t.co/aMHh5XHGfS',
            'expanded_url' => 'http://techcrunch.com/2013/04/28/disrupt-ny-hackathon-gets-hacked-man-'.
                              'takes-stage-and-uses-his-60-seconds-to-disrupt-capitalism/'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>999, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $today));
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $fav_posts = unserialize($result->related_data);
        $this->assertNotNull($result);
        $this->assertEqual('2 links @testeriffic liked', $result->headline);
        $this->assertEqual(count($fav_posts), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

    }

    private function buildData() {
        $builders = array();

        $now = date('Y-m-d H:i:s');
        $yesterday = date('Y-m-d H:i:s', strtotime('yesterday'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>133, 'post_id'=>133, 'author_user_id'=>7654321,
            'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'network'=>'twitter', 'post_text'=>'This is a simple post.', 'source'=>'web',
            'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>134, 'post_id'=>134, 'author_user_id'=>7654321,
            'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'network'=>'twitter', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
            'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>134, 'url'=>'http://t.co/B5LAotKMWY',
            'expanded_url' => 'https://pushover.net/'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135, 'author_user_id'=>7654321,
            'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
            'network'=>'twitter', 'post_text'=>'This is an old post http://t.co/aMHh5XHGfS with a link.', 'source'=>'web',
            'pub_date'=>$yesterday, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>135, 'url'=>'http://t.co/aMHh5XHGfS',
            'expanded_url' => 'http://techcrunch.com/2013/04/28/disrupt-ny-hackathon-gets-hacked-man-'.
                              'takes-stage-and-uses-his-60-seconds-to-disrupt-capitalism/'));

        $builders[] = FixtureBuilder::build('links', array('post_key'=>142, 'url'=>'http://t.co/aMHh5XHGfS',
            'expanded_url' => 'http://techcrunch.com/2013/04/28/'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>133, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $now));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>134, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $now));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>135, 'author_user_id'=>7654321,
            'fav_of_user_id'=>7612345, 'network'=>'twitter', 'fav_timestamp' => $yesterday));

        return $builders;
    }

    private function buildDataExceedingMax($network) {
        $builders = array();

        $now = date('Y-m-d H:i:s');
        $yesterday = date('Y-m-d H:i:s', strtotime('yesterday'));

        $i = FavoritedLinksInsight::MAX_POSTS + 2;
        while ($i > 0) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>(134+$i), 'post_id'=>(134+$i),
                'author_user_id'=>7654321, 'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User',
                'author_avatar'=>'avatar.jpg', 'network'=>$network,
                'post_text'=>'This is a post http://t.co/B5LAotKMWY'.$i.' with a link.', 'source'=>'web',
                'pub_date'=>'-'.$i.'d', 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('links', array('post_key'=>(134+$i), 'url'=>'http://t.co/B5LAotKMWY'.$i,
                'expanded_url' => 'https://pushover.net/'.$i));
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>(134+$i), 'author_user_id'=>7654321,
                'fav_of_user_id'=>7612345, 'network'=>$network, 'fav_timestamp' => $now));
            $i--;
        }
        return $builders;
    }
}
