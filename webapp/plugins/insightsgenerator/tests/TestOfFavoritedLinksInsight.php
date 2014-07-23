<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFavoritedLinksInsight.php
 *
 * Copyright (c) 2012-2013 Nilaksh Das, Gina Trapani
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
 * @copyright 2012-2013 Nilaksh Das, Gina Trapani
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
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('@testeriffic favorited <strong>1 tweet</strong> with a link in it.', $result->headline);
        $this->assertNoPattern('/tweets/', $result->text);
        $this->assertIsA($fav_posts, "array");
        $this->assertIsA($fav_posts["posts"][0], "Post");
        $this->assertEqual(count($fav_posts), 1);
    }

    public function testFavoritedLinksInsightForFacebook() {
        // Get data ready that insight requires
        $builders = self::buildData();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'facebook';
        $insight_plugin = new FavoritedLinksInsight();
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $fav_posts = unserialize($result->related_data);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/testeriffic liked \<strong\>2 status updates\<\/strong\>/', $result->headline);
        $this->assertIsA($fav_posts, "array");
        $this->assertIsA($fav_posts["posts"][0], "Post");
        $this->assertEqual(count($fav_posts["posts"]), 2);
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
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>134, 'url'=>'http://t.co/mcr7QsU7Ki',
            'expanded_url' => 'https://twitter.com/TaylorBiglerDC/status/425346150397247489/photo/1'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>134, 'author_user_id'=>7654321,
        'fav_of_user_id'=>7612345, 'network'=>'twitter'));

        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $this->assertNull($result);

        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>135, 'url'=>'http://pic.twitter.com/vx4YL7Yz',
            'expanded_url' => ''));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>135, 'author_user_id'=>7654321,
        'fav_of_user_id'=>7612345, 'network'=>'twitter'));
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $this->assertNull($result);
    }

    public function testMultipleLinks() {
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
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>134, 'url'=>'http://t.co/mcr7QsU7Ki',
            'expanded_url' => 'http://google.com/'));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>134, 'url'=>'http://inarow.net',
            'expanded_url' => ''));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>134, 'author_user_id'=>7654321,
        'fav_of_user_id'=>7612345, 'network'=>'twitter'));

        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('@testeriffic favorited <strong>1 tweet</strong> with 2 links in it.', $result->headline);

        $email = $this->getRenderedInsightInEmail($result);
        $count = substr_count($email, '2 links:');
        $this->assertEqual(1, $count);
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
        'network'=>'twitter', 'post_text'=>'2 links: http://t.co/mcr7QsU7Ki and http://inarow.net', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>134, 'url'=>'http://t.co/mcr7QsU7Ki',
            'expanded_url' => 'http://google.com/'));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>134, 'url'=>'http://inarow.net',
            'expanded_url' => ''));
        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'2 links: http://t.co/mcr7QsU7Ki and http://inarow.net', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>135, 'url'=>'http://t.co/mcr7QsU7Ki',
            'expanded_url' => 'http://google.com/moogley'));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>135, 'url'=>'http://inarow.net/fish',
            'expanded_url' => ''));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>134, 'author_user_id'=>7654321,
        'fav_of_user_id'=>7612345, 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>135, 'author_user_id'=>7654321,
        'fav_of_user_id'=>7612345, 'network'=>'twitter'));

        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $result = $insight_dao->getInsight('favorited_links', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual('@testeriffic favorited <strong>2 tweets</strong> with 4 links in them.', $result->headline);
        $fav_posts = unserialize($result->related_data);
        $this->assertEqual(2, count($fav_posts['posts']));
        $this->assertEqual(2, count($fav_posts['posts'][0]->links));
        $this->assertEqual(2, count($fav_posts['posts'][1]->links));
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
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>134, 'url'=>'http://t.co/B5LAotKMWY',
            'expanded_url' => 'https://pushover.net/'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>135, 'post_id'=>135, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is an old post http://t.co/aMHh5XHGfS with a link.', 'source'=>'web',
        'pub_date'=>$yesterday, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>135, 'url'=>'http://t.co/aMHh5XHGfS',
            'expanded_url' => 'http://techcrunch.com/2013/04/28/disrupt-ny-hackathon-gets-hacked-man-'.
                              'takes-stage-and-uses-his-60-seconds-to-disrupt-capitalism/'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>136, 'post_id'=>136, 'author_user_id'=>7654322,
        'author_username'=>'fbuser', 'author_fullname'=>'Facebook User', 'author_avatar'=>'avatar.jpg',
        'network'=>'facebook', 'post_text'=>'This is a simple post.', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>137, 'post_id'=>137, 'author_user_id'=>7654322,
        'author_username'=>'fbuser', 'author_fullname'=>'Facebook User', 'author_avatar'=>'avatar.jpg',
        'network'=>'facebook', 'post_text'=>'This is a post http://t.co/B5LAotKMWY with a link.', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>137, 'url'=>'http://t.co/B5LAotKMW',
            'expanded_url' => ''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>138, 'post_id'=>138, 'author_user_id'=>7654322,
        'author_username'=>'fbuser', 'author_fullname'=>'Facebook User', 'author_avatar'=>'avatar.jpg',
        'network'=>'facebook', 'post_text'=>'This is another post http://t.co/thtfuoy8 with a link.', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>138, 'url'=>'http://t.co/thtfuoy8',
            'expanded_url' => ''));

        $builders[] = FixtureBuilder::build('posts', array('id'=>139, 'post_id'=>139, 'author_user_id'=>7654322,
        'author_username'=>'fbuser', 'author_fullname'=>'Facebook User', 'author_avatar'=>'avatar.jpg',
        'network'=>'facebook', 'post_text'=>'This is an old post http://t.co/aMHh5XHGfS with a link.', 'source'=>'web',
        'pub_date'=>$yesterday, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>139, 'url'=>'http://t.co/aMHh5XHGfS',
            'expanded_url' => 'http://techcrunch.com/2013/04/28/'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>140, 'post_id'=>140, 'author_user_id'=>7654323,
        'author_username'=>'gplususer', 'author_fullname'=>'Google Plus User', 'author_avatar'=>'avatar.jpg',
        'network'=>'google+', 'post_text'=>'This is a simple post.', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>141, 'post_id'=>141, 'author_user_id'=>7654323,
        'author_username'=>'gplususer', 'author_fullname'=>'Google Plus User', 'author_avatar'=>'avatar.jpg',
        'network'=>'google+', 'post_text'=>'This is another simple post.', 'source'=>'web',
        'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>142, 'post_id'=>142, 'author_user_id'=>7654323,
        'author_username'=>'gplususer', 'author_fullname'=>'Google Plus User', 'author_avatar'=>'avatar.jpg',
        'network'=>'google+', 'post_text'=>'This is an old post http://t.co/aMHh5XHGfS with a link.', 'source'=>'web',
        'pub_date'=>$yesterday, 'reply_count_cache'=>0, 'is_protected'=>0));
        $builders[] = FixtureBuilder::Build('links', array('post_key'=>142, 'url'=>'http://t.co/aMHh5XHGfS',
            'expanded_url' => 'http://techcrunch.com/2013/04/28/'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>133, 'author_user_id'=>7654321,
        'fav_of_user_id'=>7612345, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>134, 'author_user_id'=>7654321,
        'fav_of_user_id'=>7612345, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>135, 'author_user_id'=>7654321,
        'fav_of_user_id'=>7612345, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>136, 'author_user_id'=>7654322,
        'fav_of_user_id'=>7612345, 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>137, 'author_user_id'=>7654322,
        'fav_of_user_id'=>7612345, 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>138, 'author_user_id'=>7654322,
        'fav_of_user_id'=>7612345, 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>139, 'author_user_id'=>7654322,
        'fav_of_user_id'=>7612345, 'network'=>'facebook'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>140, 'author_user_id'=>7654323,
        'fav_of_user_id'=>7612345, 'network'=>'google+'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>141, 'author_user_id'=>7654323,
        'fav_of_user_id'=>7612345, 'network'=>'google+'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>142, 'author_user_id'=>7654323,
        'fav_of_user_id'=>7612345, 'network'=>'google+'));

        return $builders;
    }
}
