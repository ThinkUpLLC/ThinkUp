<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfTwitterRatiosInsight.php
 *
 * Copyright (c) Gareth Brady
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
 * Test of Twitter Ratios Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gareth Brady
 * @author Gareth Brady <gareth.brady92[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/twitterratios.php';

class TestOfTwitterRatiosInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'janesmith';
        $instance->network = 'twitter';
        $instance->crawler_last_run = '2014-05-27 15:33:07';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new TwitterRatiosInsight();
        $this->assertIsA($insight_plugin, 'TwitterRatiosInsight' );
    }

    public function testNotEnoughTweets() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('pub_date' => date('Y-m-d', strtotime('-1 day')),
        'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
        'post_text' => "This is my first tweet."));

        $insight_plugin = new TwitterRatiosInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight("twitter_ratios", 10, $today);
        $this->assertNull($result);
    }

    public function testTweet() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>28, 'post_id'=>'28',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>29, 'post_id'=>'29',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>30, 'post_id'=>'30',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>31, 'post_id'=>'31',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>32, 'post_id'=>'32',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $insight_plugin = new TwitterRatiosInsight();
        $post_dao = new PostMySQLDAO();
        $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy('janesmith', 'twitter', $count=0,
        $order_by="pub_date", $in_last_x_days = 7, $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $last_week_of_posts, 3);
        $today = date ('Y-m-d');

        $result = $insight_dao->getInsight("twitter_ratios_weekly", $this->instance->id, $today);
        $text = "@janesmith only tweeted last week. "; 
        $text .= "Why not get the coversation flowing ";
        $text .= "by replying to other users this week ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_monthly", $this->instance->id, $today);
        $text = "@janesmith only tweeted last month. "; 
        $text .= "Why not get the coversation flowing ";
        $text .= "by replying to other users this month ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_annually", $this->instance->id, $today);
        $text = "@janesmith only tweeted last year. "; 
        $text .= "Why not get the coversation flowing ";
        $text .= "by replying to other users this year ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

    }

    public function testReply() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>28, 'post_id'=>'28',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>5,'in_reply_to_user_id'=>5, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>29, 'post_id'=>'29',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>6,'in_reply_to_user_id'=>5, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>30, 'post_id'=>'30',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>7,'in_reply_to_user_id'=>5, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>31, 'post_id'=>'31',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>8,'in_reply_to_user_id'=>5, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>32, 'post_id'=>'32',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>9,'in_reply_to_user_id'=>5, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $insight_plugin = new TwitterRatiosInsight();
        $post_dao = new PostMySQLDAO();
        $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy('janesmith', 'twitter', $count=0,
        $order_by="pub_date", $in_last_x_days = 7, $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $last_week_of_posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("twitter_ratios_weekly", $this->instance->id, $today);
        $text = "@janesmith only replied to other users last week. ";
        $text .= "Why not share the best tweets with a retweet this week ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_monthly", $this->instance->id, $today);
        $text = "@janesmith only replied to other users last month. ";
        $text .= "Why not share the best tweets with a retweet this month ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_annually", $this->instance->id, $today);
        $text = "@janesmith only replied to other users last year. ";
        $text .= "Why not share the best tweets with a retweet this year ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testRetweet() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>28, 'post_id'=>'28',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>11, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>29, 'post_id'=>'29',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>22, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>30, 'post_id'=>'30',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>23, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>31, 'post_id'=>'31',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>26, 'is_geo_encoded'=>0));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>32, 'post_id'=>'32',
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>30, 'is_geo_encoded'=>0));
        $insight_plugin = new TwitterRatiosInsight();
        $post_dao = new PostMySQLDAO();
        $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy('janesmith', 'twitter', $count=0,
        $order_by="pub_date", $in_last_x_days = 7, $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $last_week_of_posts, 3);

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("twitter_ratios_weekly", $this->instance->id, $today);
        $text = "@janesmith only retweeted other user's tweets last week. ";
        $text .="Why not give other users something to retweet with some ";
        $text .= "new tweets this week ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_monthly", $this->instance->id, $today);
        $text = "@janesmith only retweeted other user's tweets last month. ";
        $text .="Why not give other users something to retweet with some ";
        $text .= "new tweets this month ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_annually", $this->instance->id, $today);
        $text = "@janesmith only retweeted other user's tweets last year. ";
        $text .="Why not give other users something to retweet with some ";
        $text .= "new tweets this year ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testOneFavorite() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $builders[] = FixtureBuilder::build('users', array('user_id'=>42, 'user_name'=>'janesmith',
        'full_name'=>'Jane Smith', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>80,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>43, 'user_name'=>'joesmith',
        'full_name'=>'Joe Smith', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>80,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'28',
        'author_user_id'=>'43', 'author_username'=>'joesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $builders[] = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'29',
        'author_user_id'=>'43', 'author_username'=>'joeesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $builders[] = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'30',
        'author_user_id'=>'43', 'author_username'=>'joesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $builders[] = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'31',
        'author_user_id'=>'43', 'author_username'=>'joesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $builders[] = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'32',
        'author_user_id'=>'43', 'author_username'=>'joesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'28', 'author_user_id'=>'43',
               'fav_of_user_id'=>'42', 'network'=>'twitter','fav_timestamp'=>'-1d'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'29', 'author_user_id'=>'43',
                'fav_of_user_id'=>'42', 'network'=>'twitter','fav_timestamp'=>'-1d'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'30', 'author_user_id'=>'43',
               'fav_of_user_id'=>'42', 'network'=>'twitter','fav_timestamp'=>'-1d'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'31', 'author_user_id'=>'43',
                'fav_of_user_id'=>'42', 'network'=>'twitter','fav_timestamp'=>'-1d'));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'32', 'author_user_id'=>'43',
                'fav_of_user_id'=>'42', 'network'=>'twitter','fav_timestamp'=>'-1d'));
        $insight_plugin = new TwitterRatiosInsight();
        $fav_dao = new FavoritePostMySQLDAO();
        $last_week_of_favs = $fav_dao->getAllFavoritePostsByUsernameWithinRange("janesmith", 'twitter',0,7);
        $insight_plugin->generateInsight($this->instance, null, $last_week_of_favs, 3);
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("twitter_ratios_weekly", $this->instance->id, $today);
        $text = "@janesmith only favorited other user's tweets last week. ";
        $text .= "Why not share the best Twitter has to offer with a retweet this ";
        $text .= "week ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_monthly", $this->instance->id, $today);
        $text = "@janesmith only favorited other user's tweets last month. ";
        $text .= "Why not share the best Twitter has to offer with a retweet this ";
        $text .= "month ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_annually", $this->instance->id, $today);
        $text = "@janesmith only favorited other user's tweets last year. ";
        $text .= "Why not share the best Twitter has to offer with a retweet this ";
        $text .= "year ?";
        $this->assertEqual($result->text, $text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

    }
    public function testFirstCrawl() {
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'janesmith';
        $instance->network = 'twitter';
        $instance->crawler_last_run = null;


        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $i = 0;
        while($i < 6) {
            $post_builders[] = FixtureBuilder::build('posts', array('id'=>20 + $i, 'post_id'=> 20 + $i,
            'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
            'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>11, 'is_geo_encoded'=>0));

            $post_builders[] = FixtureBuilder::build('posts', array('id'=>30 + $i, 'post_id'=>30 + $i,
            'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
            'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>5,'in_reply_to_user_id'=>5, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

            $post_builders[] = FixtureBuilder::build('posts', array('id'=>40 + $i, 'post_id'=>40 + $i,
            'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
            'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null,'in_retweet_of_post_id'=>null,'is_geo_encoded'=>0));
            $i++;
        }
        $builders[] = FixtureBuilder::build('users', array('user_id'=>42, 'user_name'=>'janesmith',
        'full_name'=>'Jane Smith', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>80,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>43, 'user_name'=>'joesmith',
        'full_name'=>'Joe Smith', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>80,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'28',
        'author_user_id'=>'43', 'author_username'=>'joesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'28', 'author_user_id'=>'43',
        'fav_of_user_id'=>'42', 'network'=>'twitter','fav_timestamp'=>'-1d'));

        $insight_plugin = new TwitterRatiosInsight();
        $post_dao = new PostMySQLDAO();
        $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy('janesmith', 'twitter', $count=0,
        $order_by="pub_date", $in_last_x_days = 7, $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($instance, null, $last_week_of_posts, 3);
        $tweet_count = '{"c":[{"v":"Tweets"},{"v":6}';
        $retweet_count = '{"c":[{"v":"Retweets"},{"v":6}';
        $reply_count = '{"c":[{"v":"Replies"},{"v":6}';
        $favorite_count = '{"c":[{"v":"Favorites"},{"v":0}';

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("twitter_ratios_weekly", $instance->id, $today);
        $this->assertNotEqual(false, strpos($result->related_data,$tweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$retweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$reply_count));
        $this->assertNotEqual(false, strpos($result->related_data,$favorite_count));
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_monthly", $instance->id, $today);
        $this->assertNotEqual(false, strpos($result->related_data,$tweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$retweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$reply_count));
        $this->assertNotEqual(false, strpos($result->related_data,$favorite_count));
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_annually", $instance->id, $today);
        $this->assertNotEqual(false, strpos($result->related_data,$tweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$retweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$reply_count));
        $this->assertNotEqual(false, strpos($result->related_data,$favorite_count));

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

    }

    public function testActivityCounts() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $i = 0;
        while($i < 6) {
            $post_builders[] = FixtureBuilder::build('posts', array('id'=>20 + $i, 'post_id'=> 20 + $i,
            'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
            'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>11, 'is_geo_encoded'=>0));

            $post_builders[] = FixtureBuilder::build('posts', array('id'=>30 + $i, 'post_id'=>30 + $i,
            'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
            'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>5,'in_reply_to_user_id'=>5, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

            $post_builders[] = FixtureBuilder::build('posts', array('id'=>40 + $i, 'post_id'=>40 + $i,
            'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
            'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null,'in_retweet_of_post_id'=>null,'is_geo_encoded'=>0));
            $i++;
        }
        $builders[] = FixtureBuilder::build('users', array('user_id'=>42, 'user_name'=>'janesmith',
        'full_name'=>'Jane Smith', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>80,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>43, 'user_name'=>'joesmith',
        'full_name'=>'Joe Smith', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>80,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'28',
        'author_user_id'=>'43', 'author_username'=>'joesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'28', 'author_user_id'=>'43',
        'fav_of_user_id'=>'42', 'network'=>'twitter','fav_timestamp'=>'-1d'));
        $post_builders[] = FixtureBuilder::build('posts', array('id'=>60, 'post_id'=> 60,
        'author_user_id'=>'42', 'author_username'=>'janesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-20d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>11, 'is_geo_encoded'=>0));
        $builders[] = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'61',
        'author_user_id'=>'43', 'author_username'=>'joesmith', 'author_fullname'=>'Jane Smith',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post 42',
        'source'=>'web', 'pub_date'=>'-20d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null,'in_reply_to_user_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'61', 'author_user_id'=>'43',
        'fav_of_user_id'=>'42', 'network'=>'twitter','fav_timestamp'=>'-20d'));

        $insight_plugin = new TwitterRatiosInsight();
        $post_dao = new PostMySQLDAO();
        $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy('janesmith', 'twitter', $count=0,
        $order_by="pub_date", $in_last_x_days = 7, $iterator = false, $is_public = false);
        $insight_plugin->generateInsight($this->instance, null, $last_week_of_posts, 3);
        $tweet_count = '{"c":[{"v":"Tweets"},{"v":6}';
        $retweet_count = '{"c":[{"v":"Retweets"},{"v":6}';
        $reply_count = '{"c":[{"v":"Replies"},{"v":6}';
        $favorite_count = '{"c":[{"v":"Favorites"},{"v":1}';

        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("twitter_ratios_weekly", $this->instance->id, $today);
        $this->assertNotEqual(false, strpos($result->related_data,$tweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$retweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$reply_count));
        $this->assertNotEqual(false, strpos($result->related_data,$favorite_count));
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_monthly", $this->instance->id, $today);
        $retweet_count = '{"c":[{"v":"Retweets"},{"v":7}';
        $favorite_count = '{"c":[{"v":"Favorites"},{"v":2}';
        $this->assertNotEqual(false, strpos($result->related_data,$tweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$retweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$reply_count));
        $this->assertNotEqual(false, strpos($result->related_data,$favorite_count));
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight("twitter_ratios_monthly", $this->instance->id, $today);
        $this->assertNotEqual(false, strpos($result->related_data,$tweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$retweet_count));
        $this->assertNotEqual(false, strpos($result->related_data,$reply_count));
        $this->assertNotEqual(false, strpos($result->related_data,$favorite_count));
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}
