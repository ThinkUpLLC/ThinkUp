<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfEOYMostTalkativeDayInsight.php
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
 * EOYMostTalkativeDay (name of file)
 *
 * Description of what this class does
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoymosttalkativeday.php';

class TestOfEOYMostTalkativeDayInsight extends ThinkUpInsightUnitTestCase {

    public function setUp() {
        parent::setUp();

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_username = 'buffy';
        $this->instance->network = 'twitter';

    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new EOYMostTalkativeDayInsight();
        $this->assertIsA($insight_plugin, 'EOYMostTalkativeDayInsight' );
    }

    public function testMostTalkativeDay() {
        $insight_plugin = new EOYMostTalkativeDayInsight();

        // A bunch of posts on 02-07
        for ($i=0; $i<3; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                'post_text' => 'This is very shared',
                'pub_date' => date('Y').'-02-07',
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 100
                )
            );
        }

        // One post on 08-07
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is pretty well shared',
            'pub_date' => date('Y').'-08-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'retweet_count_cache' => 50
            )
        );

        // Larger group of posts, but in the wrong year
        for ($i=0; $i<6; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                'post_text' => 'This is least shared',
                'pub_date' => '2013-03-09',
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 25
                )
            );
        }
        $most_talkative_days = $insight_plugin->getMostTalkativeDays($this->instance);

        // // test that query returns 1 result sorted by retweets descending
        $this->assertEqual(1, sizeof($most_talkative_days));
        // $this->debug(Utils::varDumpToString($posts));
        $this->assertEqual(3, $most_talkative_days[0]['post_count']);

        // Test multiple matches for most talkative day
        for ($i=0; $i<3; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is pretty well shared',
                    'pub_date' => date('Y').'-09-07',
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                    'retweet_count_cache' => 50
                )
            );
        }

        $most_talkative_days = $insight_plugin->getMostTalkativeDays($this->instance);
        // // test that query returns 2 results sorted by retweets descending
        $this->assertEqual(2, sizeof($most_talkative_days));
        // $this->debug(Utils::varDumpToString($most_talkative_days));
        $this->assertEqual(3, $most_talkative_days[0]['post_count']);
        $this->assertEqual(3, $most_talkative_days[1]['post_count']);
    }

    public function testTwitterNormalCase() {
        // Set up and test normal twitter case
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is very shared',
                    'pub_date' => "$year-02-07",
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                )
            );
        }
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is very popular',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 100,
                'favlike_count_cache' => 100,
                'reply_count_cache' => 100
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is less popular',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 10,
                'favlike_count_cache' => 10,
                'reply_count_cache' => 10
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is least popular',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 1,
                'favlike_count_cache' => 1,
                'reply_count_cache' => 1
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostTalkativeDayInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@buffy's most talkative day on Twitter in $year", $result->headline);
        $this->assertEqual("@buffy tweeted <strong>8 times on February 7th</strong>, more than " .
            "any other day this year. (Strange — the forecast didn't say anything " .
            "about a tweetstorm.) These are @buffy's most popular tweets from that day.",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Twitter");
    }

    public function testInstagramNormalCase() {
        // Set up and test normal insta case
        $this->instance->network = 'instagram';

        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        for ($i=0; $i<5; $i++) {
            $post_builder = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is very shared',
                    'pub_date' => "$year-02-07",
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                )
            );
            $builders[] = $post_builder;
            $builders[] = FixtureBuilder::build('photos', array(
                'post_key'=>$post_builder->columns['last_insert_id'],
                'post_id'=>$post_builder->columns['post_id'],
                'standard_resolution_url'=>'/example.jpg',
                'is_short_video'=>0 ));
        }

        $post_builder = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is very popular',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 100,
                'favlike_count_cache' => 100,
                'reply_count_cache' => 100
            )
        );
        $builders[] = $post_builder;
        $builders[] = FixtureBuilder::build('photos', array(
            'post_key'=>$post_builder->columns['last_insert_id'],
            'post_id'=>$post_builder->columns['post_id'],
            'standard_resolution_url'=>'/example.jpg',
            'is_short_video'=>0 ));

        $post_builder = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is less popular',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 10,
                'favlike_count_cache' => 10,
                'reply_count_cache' => 10
            )
        );
        $builders[] = $post_builder;
        $builders[] = FixtureBuilder::build('photos', array(
            'post_key'=>$post_builder->columns['last_insert_id'],
            'post_id'=>$post_builder->columns['post_id'],
            'standard_resolution_url'=>'/example.jpg',
            'is_short_video'=>0 ));

        $post_builder = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is least popular',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 1,
                'favlike_count_cache' => 1,
                'reply_count_cache' => 1
            )
        );

        $builders[] = $post_builder;
        $builders[] = FixtureBuilder::build('photos', array(
            'post_key'=>$post_builder->columns['last_insert_id'],
            'post_id'=>$post_builder->columns['post_id'],
            'standard_resolution_url'=>'/example.jpg',
            'is_short_video'=>0 ));

        $posts = array();
        $insight_plugin = new EOYMostTalkativeDayInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("buffy's most Instagrammed day in $year", $result->headline);
        $this->assertEqual("buffy posted on Instagram <strong>8 times on February 7th</strong>, more than " .
            "any other day this year (at least since February). These are ".
            "buffy's most popular photos and videos from that day.",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Instagram");
    }

    public function testFacebookNormalCase() {
        // Set up and test normal facebook case
        $this->instance->network_username = 'Buffy Summers';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is very shared',
                    'pub_date' => "$year-02-07",
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                )
            );
        }
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is very popular',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 100,
                'favlike_count_cache' => 100,
                'reply_count_cache' => 100
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is less popular',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 10,
                'favlike_count_cache' => 10,
                'reply_count_cache' => 10
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is least popular',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 1,
                'favlike_count_cache' => 1,
                'reply_count_cache' => 1
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostTalkativeDayInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Buffy Summers's most talkative day on Facebook in $year", $result->headline);
        $this->assertEqual("Buffy Summers posted to Facebook <strong>8 times on February 7th</strong>" .
            ", more than any other day this year (at least since February). These are Buffy Summers's most " .
            "popular status updates from that day.",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Facebook");
    }

    public function testTwitterMultipleMatches() {
        // Set up and test twitter mutliple case
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is very shared',
                    'pub_date' => "$year-02-07",
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                    'post_id'=> $i + 1000
                )
            );
        }
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is very shared',
                    'pub_date' => "$year-03-07",
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                    'post_id'=> $i + 2000
                )
            );
        }
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is very shared',
                    'pub_date' => "$year-04-07",
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                    'post_id'=> $i + 3000
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYMostTalkativeDayInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@buffy's most talkative day on Twitter in $year", $result->headline);
        $this->assertEqual("In the running for @buffy's most talkative day on " .
            "Twitter, $year, we've got a tie: @buffy tweeted <strong>5 times on February 7th, " .
            "March 7th, and April 7th</strong> — more than on any other days this year. These are " .
            "@buffy's most popular tweets from each day.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Multiple days, Twitter");
    }

    public function testFacebookMultipleMatches() {
        // Set up and test facebook multiple case
        $this->instance->network_username = 'Buffy Summers';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is very shared',
                    'pub_date' => "$year-02-07",
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                    'post_id'=> $i + 1000
                )
            );
        }
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is very shared',
                    'pub_date' => "$year-03-07",
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                    'post_id'=> $i + 2000
                )
            );
        }
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is very shared',
                    'pub_date' => "$year-04-07",
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                    'post_id'=> $i + 3000
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYMostTalkativeDayInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Buffy Summers's most talkative day on Facebook in $year", $result->headline);
        $this->assertEqual("In the running for Buffy Summers's most talkative day on " .
            "Facebook, $year, we've got a tie: Buffy Summers posted <strong>5 times on February " .
            "7th, March 7th, and April 7th</strong> — more than on any other days this year (at least since February).".
            " These are Buffy Summers's most popular status updates from each day."
            , $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Multiple days, Facebook");
    }

    public function testMostPopularPosts() {
        // Set up several posts along with popular posts
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is very shared',
                    'pub_date' => "$year-02-07",
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                )
            );
        }
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is the most popular post on a diff day!',
                'pub_date' => "$year-02-06",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 20,
                'reply_count_cache' => 10,
                'favlike_count_cache' => 30
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is the most popular post on a diff day!',
                'pub_date' => "$year-02-08",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 20,
                'reply_count_cache' => 10,
                'favlike_count_cache' => 30
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is the most popular post!',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 20,
                'reply_count_cache' => 10,
                'favlike_count_cache' => 30
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is the 2nd most popular post!',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 10,
                'reply_count_cache' => 10,
                'favlike_count_cache' => 20
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is the 3rd most popular post!',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
                'retweet_count_cache' => 10,
                'reply_count_cache' => 10,
                'favlike_count_cache' => 10
            )
        );

        $insight_plugin = new EOYMostTalkativeDayInsight();
        $posts = $insight_plugin->mostPopularPosts(
            $this->instance,
            $date = Date("$year-02-07")
        );

        $this->assertEqual(3, sizeof($posts));
        // $this->debug(Utils::varDumpToString($posts));
        $this->assertEqual('This is the most popular post!', $posts[0]->post_text);
        $this->assertEqual('This is the 2nd most popular post!', $posts[1]->post_text);
        $this->assertEqual('This is the 3rd most popular post!', $posts[2]->post_text);

        // test a limit of 1
        $posts = $insight_plugin->mostPopularPosts(
            $this->instance,
            $date = Date("$year-02-07"),
            $limit = 1
        );

        $this->assertEqual(1, sizeof($posts));
        // $this->debug(Utils::varDumpToString($posts));
        $this->assertEqual('This is the most popular post!', $posts[0]->post_text);
    }
}

