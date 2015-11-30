<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfWeeklyBestsInsight.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * Test of WeeklyBestsInsight
 *
 * Test for the WeeklyBestsInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/weeklybests.php';

class TestOfWeeklyBestsInsight extends ThinkUpInsightUnitTestCase {
    var $sample_hot_posts_data;

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testWeeklyBestsInsightForTwitterV1() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);
        TimeHelper::setTime(1);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 5,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 3,
            'post_text' => 'This is a really good post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 34
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 15,
            'post_text' => 'This is an even better post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 33
        $posts[] = new Post(array(
            'reply_count_cache' => 2,
            'retweet_count_cache' => 5,
            'favlike_count_cache' => 1,
            'post_text' => 'This is THE BEST post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 27

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s most popular tweet last week/', $result->headline);
        $this->assertPattern('/5 replies/', $result->text);
        $this->assertPattern('/1 retweet/', $result->text);
        $this->assertPattern('/3 likes/', $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'testeriffic';
        $_GET['n'] = 'twitter';
        $_GET['d'] = $today;
        $_GET['s'] = 'weekly_best';
        $results = $controller->go();
        //Uncomment this out to see web view of insight
        //$this->debug($results);
        $this->assertPattern('/This is a really good post/', $results);

        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);
    }

    public function testWeeklyBestsInsightForTwitterV2() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);
        TimeHelper::setTime(2);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 5,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 3,
            'post_text' => 'This is a really good post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 34
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 15,
            'post_text' => 'This is an even better post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 33
        $posts[] = new Post(array(
            'reply_count_cache' => 2,
            'retweet_count_cache' => 5,
            'favlike_count_cache' => 1,
            'post_text' => 'This is THE BEST post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 27

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s biggest tweet last week/', $result->headline);
        $this->assertPattern('/5 replies/', $result->text);
        $this->assertPattern('/1 retweet/', $result->text);
        $this->assertPattern('/3 likes/', $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'testeriffic';
        $_GET['n'] = 'twitter';
        $_GET['d'] = $today;
        $_GET['s'] = 'weekly_best';
        $results = $controller->go();
        //Uncomment this out to see web view of insight
        //$this->debug($results);
        $this->assertPattern('/This is a really good post/', $results);

        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);
    }

    public function testMonthlyBestInsightForTwitterV1() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);

        $builders[] = FixtureBuilder::build('posts', array(
            'reply_count_cache' => 5,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 3,
            'post_text' => 'This is a really good post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => '-1d'));

        $builders[] = FixtureBuilder::build('posts', array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 15,
            'post_text' => 'This is an even better post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => '-1d'));

        $builders[] = FixtureBuilder::build('posts', array(
            'reply_count_cache' => 2,
            'retweet_count_cache' => 5,
            'favlike_count_cache' => 1,
            'post_text' => 'This is THE BEST post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => '-1d'));

        TimeHelper::setTime(1);
        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('monthly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");

        $last_month_time = strtotime('first day of last month');
        $this->assertPattern('/@testeriffic\'s best of '.date('F', $last_month_time).'/', $result->headline);
        $this->assertPattern('/Take a quick look back at @testeriffic\'s most popular tweet of '
            .date('F', $last_month_time). ' '.date('Y', $last_month_time).'./', $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'testeriffic';
        $_GET['n'] = 'twitter';
        $_GET['d'] = $today;
        $_GET['s'] = 'monthly_best';
        $results = $controller->go();
        //Uncomment this out to see web view of insight
        //$this->debug($results);
        $this->assertPattern('/This is a really good post/', $results);

        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);

        //Test alternate headline/body
        TimeHelper::setTime(2);
        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('monthly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");

        $last_month_time = strtotime('first day of last month');
        $this->assertPattern('/@testeriffic\'s best of '.date('F', $last_month_time).'/', $result->headline);
        $this->assertPattern('/This was @testeriffic\'s most popular tweet of '.date('F', $last_month_time).
            ' '.date('Y', $last_month_time).'./', $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'testeriffic';
        $_GET['n'] = 'twitter';
        $_GET['d'] = $today;
        $_GET['s'] = 'monthly_best';
        $results = $controller->go();
        //Uncomment this out to see web view of insight
        //$this->debug($results);
        $this->assertPattern('/This is a really good post/', $results);

        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);
    }

    public function testMonthlyBestInsightForInstagramV1() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'instagram';
        $builders = self::setUpPublicInsight($instance);

        $builders[] = FixtureBuilder::build('posts', array(
            'id'=>1,
            'post_id'=>'pid1',
            'reply_count_cache' => 5,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 3,
            'post_text' => 'This is a really good post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => '-1d'));

        $builders[] = FixtureBuilder::build('photos', array(
            'post_key'=>1,
            'post_id'=>'pid1',
            'is_short_video'=>1 ));

        $builders[] = FixtureBuilder::build('posts', array(
            'id'=>2,
            'post_id'=>'pid2',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 15,
            'post_text' => 'This is an even better post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => '-1d'));

        $builders[] = FixtureBuilder::build('photos', array(
            'post_key'=>2,
            'post_id'=>'pid2',
            'is_short_video'=>1 ));

        $builders[] = FixtureBuilder::build('posts', array(
            'id'=>3,
            'post_id'=>'pid3',
            'reply_count_cache' => 2,
            'retweet_count_cache' => 5,
            'favlike_count_cache' => 1,
            'post_text' => 'This is THE BEST post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => '-1d'));

        $builders[] = FixtureBuilder::build('photos', array(
            'post_key'=>3,
            'post_id'=>'pid3',
            'is_short_video'=>1 ));

        TimeHelper::setTime(1);
        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('monthly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");

        $last_month_time = strtotime('first day of last month');
        $this->assertPattern('/testeriffic\'s best of '.date('F', $last_month_time). '/', $result->headline);
        $this->assertPattern('/his video was testeriffic\'s most popular Instagram post of '
            .date('F', $last_month_time). ' '.date('Y', $last_month_time).'./', $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'testeriffic';
        $_GET['n'] = 'instagram';
        $_GET['d'] = $today;
        $_GET['s'] = 'monthly_best';
        $results = $controller->go();
        //Uncomment this out to see web view of insight
        //$this->debug($results);
        $this->assertPattern('/This is a really good post/', $results);

        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);

        //Test alternate headline/body
        TimeHelper::setTime(2);
        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('monthly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");

        $last_month_time = strtotime('first day of last month');
        $this->assertPattern('/testeriffic\'s best of '.date('F', $last_month_time).'/', $result->headline);
        $this->assertPattern('/testeriffic\'s most popular Instagram post of '.date('F', $last_month_time).
            ' '.date('Y', $last_month_time).' was a video./', $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'testeriffic';
        $_GET['n'] = 'instagram';
        $_GET['d'] = $today;
        $_GET['s'] = 'monthly_best';
        $results = $controller->go();
        //Uncomment this out to see web view of insight
        //$this->debug($results);
        $this->assertPattern('/This is a really good post/', $results);

        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);
    }

    public function testMonthlyBestInsightForTwitterV2() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);

        $builders[] = FixtureBuilder::build('posts', array(
            'reply_count_cache' => 5,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 3,
            'post_text' => 'This is a really good post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => '-1d'));

        $builders[] = FixtureBuilder::build('posts', array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 15,
            'post_text' => 'This is an even better post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => '-1d'));

        $builders[] = FixtureBuilder::build('posts', array(
            'reply_count_cache' => 2,
            'retweet_count_cache' => 5,
            'favlike_count_cache' => 1,
            'post_text' => 'This is THE BEST post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => '-1d'));

        TimeHelper::setTime(2);
        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('monthly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");

        $last_month_time = strtotime('first day of last month');
        $this->assertPattern('/@testeriffic\'s best of '.date('F', $last_month_time).'/', $result->headline);
        $this->assertPattern('/This was @testeriffic\'s most popular tweet of '.date('F', $last_month_time).
            ' '.date('Y', $last_month_time).'./', $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'testeriffic';
        $_GET['n'] = 'twitter';
        $_GET['d'] = $today;
        $_GET['s'] = 'monthly_best';
        $results = $controller->go();
        //Uncomment this out to see web view of insight
        //$this->debug($results);
        $this->assertPattern('/This is a really good post/', $results);

        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);

        //Test alternate headline/body
        TimeHelper::setTime(2);
        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('monthly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");

        $last_month_time = strtotime('first day of last month');
        $this->assertPattern('/@testeriffic\'s best of '.date('F', $last_month_time).'/', $result->headline);
        $this->assertPattern('/This was @testeriffic\'s most popular tweet of '.date('F', $last_month_time).
            ' '.date('Y', $last_month_time).'./', $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'testeriffic';
        $_GET['n'] = 'twitter';
        $_GET['d'] = $today;
        $_GET['s'] = 'monthly_best';
        $results = $controller->go();
        //Uncomment this out to see web view of insight
        //$this->debug($results);
        $this->assertPattern('/This is a really good post/', $results);

        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);
    }

    public function testWeeklyBestsInsightForFacebook() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'tester_fb';
        $instance->network = 'facebook';
        $builders = self::setUpPublicInsight($instance);
        TimeHelper::setTime(1);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 8,
            'favlike_count_cache' => 3,
            'post_text' => 'This is a really good post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 46
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'favlike_count_cache' => 15,
            'post_text' => 'This is an even better post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 30
        $posts[] = new Post(array(
            'reply_count_cache' => 2,
            'favlike_count_cache' => 1,
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 12

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/tester_fb\'s most popular status update last week/', $result->headline);
        $this->assertPattern('/8 comments/', $result->text);
        $this->assertPattern('/3 likes/', $result->text);

        /**
         * Use this code to output the individual insight's fully-rendered email HTML to file.
         * Then, open the file in your browser to view.
         *
         * $ TEST_DEBUG=1 php webapp/plugins/insightsgenerator/tests/TestOfHelloThinkUpInsight.php
         * -t testHelloThinkUpInsight > webapp/insight_email.html
         */
        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);
    }

    public function testWeeklyBestsInsightForInstagram() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'instagram';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>10,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d' ));

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 5,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 34
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'favlike_count_cache' => 15,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 33
        $posts[] = new Post(array(
            'reply_count_cache' => 2,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 27

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/testeriffic earned \<strong\>5 comments<\/strong\> and \<strong\>3 likes\<\/strong\>./',
            $result->text);
        $this->assertPattern('/5 comments/', $result->text);
        $this->assertPattern('/3 likes/', $result->text);
        $this->dumpRenderedInsight($result, $instance);
    }

    public function testWeeklyBestsInsightWithOneReply() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 1,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'post_text' => 'This is a really good post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 5
        TimeHelper::setTime(1);

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s most popular tweet last week/', $result->headline);
        $this->assertPattern('/1 reply/', $result->text);
        $this->assertNoPattern('/and/', $result->text);

        /**
         * Use this code to output the individual insight's fully-rendered email HTML to file.
         * Then, open the file in your browser to view.
         *
         * $ TEST_DEBUG=1 php webapp/plugins/insightsgenerator/tests/TestOfHelloThinkUpInsight.php
         * -t testHelloThinkUpInsight > webapp/insight_email.html
         */
        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);
    }

    public function testWeeklyBestsInsightWithFavorites() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 3,
            'post_text' => 'This is a really good post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 6
        TimeHelper::setTime(1);

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s most popular tweet last week/', $result->headline);
        $this->assertPattern('/3 likes/', $result->text);
        $this->assertNoPattern('/reply/', $result->text);
        $this->assertNoPattern('/retweet/', $result->text);
        $this->assertNoPattern('/and/', $result->text);

        /**
         * Use this code to output the individual insight's fully-rendered email HTML to file.
         * Then, open the file in your browser to view.
         *
         * $ TEST_DEBUG=1 php webapp/plugins/insightsgenerator/tests/TestOfHelloThinkUpInsight.php
         * -t testHelloThinkUpInsight > webapp/insight_email.html
         */
        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);
    }

    public function testWeeklyBestsInsightWithRepliesAndFavorites() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 4,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 5,
            'post_text' => 'This is a really good post',
            'author_username' => $instance->network_username,
            'author_user_id' => 'abc',
            'author_avatar' => 'http://example.com/example.jpg',
            'network' => $instance->network,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 30
        TimeHelper::setTime(1);

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s most popular tweet last week/', $result->headline);
        $this->assertPattern('/4 replies/', $result->text);
        $this->assertPattern('/5 likes/', $result->text);
        $this->assertPattern('/and/', $result->text);

        /**
         * Use this code to output the individual insight's fully-rendered email HTML to file.
         * Then, open the file in your browser to view.
         *
         * $ TEST_DEBUG=1 php webapp/plugins/insightsgenerator/tests/TestOfHelloThinkUpInsight.php
         * -t testHelloThinkUpInsight > webapp/insight_email.html
         */
        $result->related_data = unserialize($result->related_data);
        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        $this->debug($email_insight);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->assertPattern('/This is a really good post/', $email_insight);
    }
}
