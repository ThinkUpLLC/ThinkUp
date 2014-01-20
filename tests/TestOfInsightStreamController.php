<?php
/**
 *
 * ThinkUp/tests/TestOfInsightStreamController.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInsightStreamController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    protected function buildPublicAndPrivateInsights() {
        $builders = array();

        //owner
        $salt = 'salt';
        $pwd1 = ThinkUpTestLoginHelper::hashPasswordUsingCurrentMethod('pwd3', $salt);
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'tuuser1@example.com', 'is_activated'=>1, 'pwd'=>$pwd1, 'pwd_salt'=>OwnerMySQLDAO::$default_salt));

        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'ThinkUp J. User',
        'email'=>'tuuser2@example.com', 'is_activated'=>1, 'pwd'=>$pwd1, 'pwd_salt'=>OwnerMySQLDAO::$default_salt));

        //public instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>'10',
        'network_username'=>'jack', 'network'=>'twitter', 'network_viewer_id'=>'10',
        'crawler_last_run'=>'1988-01-20 12:00:00', 'is_active'=>1, 'is_public'=>1, 'posts_per_day'=>11,
        'posts_per_week'=>77));
        //private instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>'11',
        'network_username'=>'jill', 'network'=>'twitter', 'network_viewer_id'=>10,
        'crawler_last_run'=>'1988-01-20 12:00:00', 'is_active'=>1, 'is_public'=>0));
        //another public instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>3, 'network_user_id'=>'12',
        'network_username'=>'mary', 'network'=>'twitter', 'network_viewer_id'=>'10',
        'crawler_last_run'=>'1988-01-20 12:00:00', 'is_active'=>1, 'is_public'=>1, 'posts_per_day'=>11,
        'posts_per_week'=>77));

        //owner instances
        $builders[] = FixtureBuilder::build('owner_instances', array('instance_id' => 1, 'owner_id'=>1) );
        $builders[] = FixtureBuilder::build('owner_instances', array('instance_id' => 2, 'owner_id'=>1) );
        $builders[] = FixtureBuilder::build('owner_instances', array('instance_id' => 3, 'owner_id'=>1) );

        $builders[] = FixtureBuilder::build('owner_instances', array('instance_id' => 1, 'owner_id'=>2) );

        //public insights
        $time_now = date("Y-m-d H:i:s");
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'1', 'prefix'=>'Booyah!', 'text'=>'Hey these are some local followers!',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'filename'=>'localfollowers', 'time_generated'=>$time_now,
        'related_data'=>self::getRelatedDataListOfUsers(), 'header_image'=>'http://example.com/header_image.gif'));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-06-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'1', 'prefix'=>'Booyah!', 'text'=>'This is a list of posts!',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'filename'=>'favoriteflashbacks', 'time_generated'=>$time_now,
        'related_data'=>self::getRelatedDataListOfPosts()));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'3', 'prefix'=>'Booyah!', 'text'=>'Retweet spike! Mary\'s post publicly got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'filename'=>'retweetspike', 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-06-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'3', 'prefix'=>'Booyah!', 'text'=>'Retweet spike! Mary\'s post publicly got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'filename'=>'retweetspike', 'time_generated'=>$time_now));

        //private insights
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'prefix'=>'Booyah!', 'text'=>'Retweet spike! Jill\'s post privately got retweeted 110 '.
        'times', 'emphasis'=>Insight::EMPHASIS_HIGH, 'filename'=>'retweetspike',
        'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-06-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'prefix'=>'Booyah!', 'text'=>'Retweet spike! Jill\'s post privately got retweeted 110 '.
        'times', 'emphasis'=>Insight::EMPHASIS_HIGH, 'filename'=>'retweetspike',
        'time_generated'=>$time_now));
        return $builders;
    }

    private function getRelatedDataListOfUsers() {
        $users = array();
        $i = 3;
        while ($i > 0) {
            $user_array = array('id'=>$i, 'user_id'=>$i, 'user_name'=>'ginatrapani'.$i, 'full_name'=>'Gina Trapani',
            'avatar'=>'http://example.com/avatar.jpg', 'location'=>'NYC', 'description'=>'Blogger',
            'url'=>'http://ginatrapani.org', 'is_verified'=>1, 'is_protected'=>0, 'follower_count'=>5000,
            'post_count'=>1000, 'joined'=>'2007-03-06 13:48:05', 'network'=>'twitter', 'last_post_id'=>'abc102');
            $user = new User($user_array, 'Test Insert');
            $users[] = $user;
            $i--;
        }
        $related_data = array();
        $related_data["people"] = $users;
        return serialize($related_data);
    }

    private function getRelatedDataListOfPosts() {
        $posts = array();
        $i = 3;
        while ($i > 0) {
            $post = new Post(array('id'=>1, 'author_user_id'=>'20', 'author_username'=>'no one',
            'author_fullname'=>"No One", 'author_avatar'=>'http://example.com/yo.jpg', 'source'=>'TweetDeck',
            'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'',
            'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'',
            'retweet_count_api' =>'', 'old_retweet_count_cache' => '', 'in_rt_of_user_id' =>'',
            'post_id'=>'9021481076', 'is_protected'=>1, 'place_id' => 'ece7b97d252718cc', 'favlike_count_cache'=>0,
            'post_text'=>'I like cookies', 'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'',
            'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0));
            $posts[] = $post;
            $i--;
        }
        $related_data = array();
        $related_data["posts"] = $posts;
        return serialize($related_data);
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testController() {
        $controller = new InsightStreamController(true);
        $this->assertIsA($controller, 'InsightStreamController');
    }

    public function testOfNotLoggedInNoInsights() {
        $controller = new InsightStreamController(true);
        $results = $controller->go();
        $this->assertPattern('/Log in/', $results);
        $this->assertPattern('/Email/', $results);
        $this->assertPattern('/Password/', $results);
    }

    public function testOfNotLoggedInInsights() {
        $builders = self::buildPublicAndPrivateInsights();

        $controller = new InsightStreamController(true);
        $results = $controller->go();

        //don't show login screen
        $this->assertNoPattern('/Email/', $results);
        $this->assertNoPattern('/Password/', $results);
        //do show public insights
        $this->assertPattern('/Hey these are some local followers!/', $results);
        //don't show private insights
        $this->assertNoPattern('/Retweet spike! Jill\'s post privately got retweeted 110 times/', $results);
    }

    public function testOfLoggedInInsightsOwnsPrivateInstance() {
        $builders = self::buildPublicAndPrivateInsights();
        $this->simulateLogin('tuuser1@example.com', false);

        $controller = new InsightStreamController(true);
        $results = $controller->go();

        //don't show login screen
        $this->assertNoPattern('/Email/', $results);
        $this->assertNoPattern('/Password/', $results);
        //do show public insights
        $this->assertPattern('/Hey these are some local followers!/', $results);
        //do show private insights that owner owns
        $this->assertPattern('/Retweet spike! Jill\'s post privately got retweeted 110 times/', $results);
    }

    public function testOfLoggedInInsightsDoesntOwnPrivateInstance() {
        $builders = self::buildPublicAndPrivateInsights();
        $this->simulateLogin('tuuser2@example.com', false);

        $controller = new InsightStreamController(true);
        $results = $controller->go();

        //don't show login screen
        $this->assertNoPattern('/Email/', $results);
        $this->assertNoPattern('/Password/', $results);
        //do show public insights
        $this->assertPattern('/Hey these are some local followers!/', $results);
        //don't show private insights owner doesn't own
        $this->assertNoPattern('/Retweet spike! Jill\'s post privately got retweeted 110 times/', $results);
    }

    public function testOfLoggedInNoServiceUsersNoInsights() {
        //set up owner
        $pwd1 = ThinkUpTestLoginHelper::hashPasswordUsingCurrentMethod('pwd3', 'salt');
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'tuuser1@example.com', 'is_activated'=>1, 'pwd'=>$pwd1, 'pwd_salt'=>OwnerMySQLDAO::$default_salt));

        $this->simulateLogin('tuuser1@example.com', false);

        $controller = new InsightStreamController(true);
        $results = $controller->go();

        $this->assertNoPattern('/Email/', $results);
        $this->assertNoPattern('/Password/', $results);
        //don't show insights
        $this->assertNoPattern('/Hey these are some local followers!/', $results);
        $this->assertNoPattern('/Retweet spike! Jill\'s post privately got retweeted 110 times/', $results);
        $this->assertPattern('/Welcome to ThinkUp/', $results);
        $this->assertPattern('/Set up a/', $results);
        $this->assertPattern('/Twitter/', $results);
        $this->assertPattern('/Foursquare/', $results);
        $this->assertPattern('/Facebook/', $results);
        $this->assertPattern('/Google/', $results);
        $this->assertPattern('/account/', $results);

        $cfg = Config::getInstance();
        $cfg->setValue('thinkupllc_endpoint', 'set to something');

        $controller = new InsightStreamController();
        $results = $controller->go();
        $this->assertPattern('/Welcome to ThinkUp/', $results);
        $this->assertPattern('/Set up a/', $results);
        $this->assertPattern('/Twitter/', $results);
        $this->assertNoPattern('/Foursquare/', $results);
        $this->assertPattern('/Facebook/', $results);
        $this->assertNoPattern('/Google/', $results);
        $this->assertPattern('/account/', $results);
    }

    public function testOfLoggedInServiceUsersNoInsights() {
        //set up owner
        $pwd1 = ThinkUpTestLoginHelper::hashPasswordUsingCurrentMethod('pwd3', 'salt');
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'tuuser1@example.com', 'is_activated'=>1, 'pwd'=>$pwd1, 'pwd_salt'=>OwnerMySQLDAO::$default_salt));
        //set up instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>'10',
        'network_username'=>'jack', 'network'=>'twitter', 'network_viewer_id'=>'10',
        'crawler_last_run'=>'1988-01-20 12:00:00', 'is_active'=>1, 'is_public'=>1, 'posts_per_day'=>11,
        'posts_per_week'=>77));

        //set up owner instances
        $builders[] = FixtureBuilder::build('owner_instances', array('instance_id' => 1, 'owner_id'=>1) );

        $this->simulateLogin('tuuser1@example.com', false);

        $controller = new InsightStreamController(true);
        $results = $controller->go();

        $this->assertNoPattern('/Email/', $results);
        $this->assertNoPattern('/Password/', $results);
        //don't show insights
        $this->assertNoPattern('/Hey these are some local followers!/', $results);
        $this->assertNoPattern('/Retweet spike! Jill\'s post privately got retweeted 110 times/', $results);
        $this->assertNoPattern('/Set up a/', $results);
        $this->assertPattern('/Check back later/', $results);
        $this->assertPattern('/update your ThinkUp data now/', $results);
        $this->debug($results);
    }

    public function testOfLoggedInIndividualInsightWithAccess() {
        $builders = self::buildPublicAndPrivateInsights();
        $this->simulateLogin('tuuser1@example.com', false);

        $_GET['u'] = 'jill';
        $_GET['n'] = 'twitter';
        $_GET['d'] = '2012-05-01';
        $_GET['s'] = 'avg_replies_per_week';
        $controller = new InsightStreamController(true);
        $results = $controller->go();

        //do show owned private insight
        $this->assertPattern('/Retweet spike! Jill\'s post privately got retweeted 110 times/', $results);
    }

    public function testOfLoggedInIndividualInsightWithoutAccess() {
        $builders = self::buildPublicAndPrivateInsights();
        $this->simulateLogin('tuuser2@example.com', false);

        $_GET['u'] = 'jill';
        $_GET['n'] = 'twitter';
        $_GET['d'] = '2012-05-01';
        $_GET['s'] = 'avg_replies_per_week';
        $controller = new InsightStreamController(true);
        $results = $controller->go();

        //don't show owned private insight
        $this->assertNoPattern('/Retweet spike! Jill\'s post privately got retweeted 110 times/', $results);
        //do show no access message
        $this->assertPattern('/You don&#39;t have rights to view this service user/', $results);
        $this->debug($results);
    }

    public function testOfNotLoggedInIndividualInsightWithoutAccess() {
        $builders = self::buildPublicAndPrivateInsights();

        $_GET['u'] = 'jill';
        $_GET['n'] = 'twitter';
        $_GET['d'] = '2012-05-01';
        $_GET['s'] = 'avg_replies_per_week';
        $controller = new InsightStreamController(true);
        $results = $controller->go();

        //don't show owned private insight
        $this->assertNoPattern('/Retweet spike! Jill\'s post privately got retweeted 110 times/', $results);
        //do show no access message
        $this->assertPattern('/You don&#39;t have rights to view this service user/', $results);
        $this->debug($results);
    }

    public function testOfNotLoggedInIndividualInsightWithAccess() {
        $builders = self::buildPublicAndPrivateInsights();

        $_GET['u'] = 'jack';
        $_GET['n'] = 'twitter';
        $_GET['d'] = '2012-05-01';
        $_GET['s'] = 'avg_replies_per_week';
        $controller = new InsightStreamController(true);
        $results = $controller->go();

        //do show public insight
        $this->assertPattern('/Hey these are some local followers!/', $results);
        //don't show no access message
        $this->assertNoPattern('/You don&#39;t have rights to view this service user/', $results);
        $this->debug($results);
    }

    public function testOfHTTPSWithInsecureContent() {
        $builders = self::buildPublicAndPrivateInsights();
        $this->simulateLogin('tuuser2@example.com', false);
        $controller = new InsightStreamController();
        $results = $controller->go();
        $this->debug($results);
        //Assert script/meta/link/img not using http
        $this->assertNoPattern('/(script|meta|link|img) (src|href)="http:/', $results);
        //Assert user avatars are not using http
        $this->assertNoPattern("/img src=\"http\:\/\/example.com\/avatar.jpg/", $results);
        //Assert post author_avatars not using http
        $this->assertNoPattern("/img src=\"http\:\/\/example.com\/yo.jpg/", $results);
        //Assert insight header image not using http
        $this->assertNoPattern("/img src=\"http:\/\/example.com\/header_image.gif/", $results);
    }
}
