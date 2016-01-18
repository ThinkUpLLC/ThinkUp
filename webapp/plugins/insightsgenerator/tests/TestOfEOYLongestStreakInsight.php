<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYLongestStreakInsight.php
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
 * Test of EOYLongestStreakInsight
 *
 * Test for the EOYLongestStreakInsight class.
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoylongeststreak.php';

class TestOfEOYLongestStreakInsight extends ThinkUpInsightUnitTestCase {

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

    public function testStreakCounting() {
        $insight_plugin = new EOYLongestStreakInsight();
        $year = date('Y');

        $counter = 0;
        // Set up a twenty day streak
        for ($i=1; $i<21; $i++) {
            if ($i > 9) {
                $day = $i."";
            } else {
                $day = "0$i";
            }

            for ($j=0; $j<$i; $j++) {
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'This is a post!',
                        'pub_date' => "$year-01-$day",
                        'post_id' => $counter + 100,
                        'author_username' => $this->instance->network_username,
                        'author_user_id' => $this->instance->network_user_id,
                        'network' => $this->instance->network,
                    )
                );
                $counter++;
            }
        }

        // Set up a ten day streak
        for ($i=1; $i<11; $i++) {
            if ($i > 9) {
                $day = $i."";
            } else {
                $day = "0$i";
            }

            for ($j=0; $j<$i; $j++) {
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'This is a post!',
                        'pub_date' => "$year-02-$day",
                        'post_id' => $counter + 200,
                        'author_username' => $this->instance->network_username,
                        'author_user_id' => $this->instance->network_user_id,
                        'network' => $this->instance->network,
                    )
                );
                $counter++;
            }
        }

        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getThisYearOfPostsIterator(
            $author_id = $this->instance->network_user_id,
            $network = $this->instance->network
        );

        $streaks = $insight_plugin->getStreaks($posts);

        // $this->debug(Utils::varDumpToString($streaks));
        $this->assertEqual(sizeof($streaks), 30);

        $longest_streak = $insight_plugin->getLongestStreak($streaks);
        // $this->debug(Utils::varDumpToString($longest_streak));
        $this->assertEqual($longest_streak['start_day'], 0);
        $this->assertEqual($longest_streak['end_day'], 19);
        $this->assertEqual($longest_streak['length'], 20);
        $this->assertEqual($longest_streak['counts'][0], 1);
        $this->assertEqual($longest_streak['counts'][19], 20);
    }

    public function testGetDateFromDay() {
        $insight_plugin = new EOYLongestStreakInsight();
        $start_day = 0;
        $start_date = $insight_plugin->getDateFromDay($start_day);
        $this->assertEqual($start_date, '01-01-2016');

        $start_day = 300;
        $start_date = $insight_plugin->getDateFromDay($start_day, 'F j');
        $this->assertEqual($start_date, 'October 28');
    }

    public function testTwitterNormalCase() {
        // set up posts with exclamation
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        $counter = 0;
        // Set up a twenty day streak
        for ($i=2; $i<21; $i++) {
            if ($i > 9) {
                $day = $i."";
            } else {
                $day = "0$i";
            }

            for ($j=0; $j<$i; $j++) {
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'This is a post!',
                        'pub_date' => "$year-03-$day",
                        'post_id' => $counter + 100,
                        'author_username' => $this->instance->network_username,
                        'author_user_id' => $this->instance->network_user_id,
                        'network' => $this->instance->network,
                    )
                );
                $counter++;
            }
        }
        $instance->last_post_id = 100;
        $posts = array();
        $insight_plugin = new EOYLongestStreakInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();

        $result = $insight_dao->getInsight('eoy_longest_streak', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("@ev's longest tweet-streak of $year", $result->headline);
        $this->assertEqual("Sometimes the tweets flow like water and you just don't " .
            "need a day off. In $year, @ev's longest tweeting streak lasted for <strong>19 days</strong>, " .
            "from March 2nd to March 20th.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Twitter");
    }

    public function testTwitterEveryDay() {
        // set up posts with exclamation
        $insight_plugin = new EOYLongestStreakInsight();
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        $counter = 0;
        $days = date('z');
        // Set up an everyday streak
        for ($i=0; $i<=$days; $i++) {
            $date = $insight_plugin->getDateFromDay($i, 'Y-m-d');
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post!',
                    'pub_date' => $date,
                    'post_id' => $counter + 100,
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
            $counter++;
        }

        $posts = array();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_longest_streak', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $total_days = $days+1;
        $end_date = $insight_plugin->getDateFromDay($days, 'F jS');
        $this->assertEqual("@ev has tweeted every single day in $year!", $result->headline);
        $this->assertEqual("Sometimes the tweets flow like water and you just don't " .
            "need a day off. So far in $year, @ev hasn't taken off a single day, with " .
            "a streak that has so far lasted for <strong>$total_days days</strong>, " .
            "from January 1st to $end_date.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Everyday case, Twitter");
    }

    public function testFacebookNormalCase() {
        // set up posts with exclamation
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');

        $counter = 0;
        // Set up a twenty day streak
        for ($i=1; $i<21; $i++) {
            if ($i > 9) {
                $day = $i."";
            } else {
                $day = "0$i";
            }

            for ($j=0; $j<$i; $j++) {
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'This is a post!',
                        'pub_date' => "$year-05-$day",
                        'post_id' => $counter + 100,
                        'author_username' => $this->instance->network_username,
                        'author_user_id' => $this->instance->network_user_id,
                        'network' => $this->instance->network,
                    )
                );
                $counter++;
            }
        }

        $posts = array();
        $insight_plugin = new EOYLongestStreakInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_longest_streak', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Mark Zuckerberg's longest update streak of $year",
            $result->headline);
        $this->assertEqual("Facebook is great for sharing what we're up to, and " .
            "sometimes we're up to a lot. In $year (at least since May), Mark Zuckerberg posted at least " .
            "one status update or comment to Facebook for <strong>20 days</strong> in a row, from " .
            "May 1st to May 20th.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Facebook");
    }

    public function testFacebookEveryday() {
        // set up posts with exclamation
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $insight_plugin = new EOYLongestStreakInsight();
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        $counter = 0;
        $days = date('z');
        // Set up an everyday streak
        for ($i=0; $i<=$days; $i++) {
            $date = $insight_plugin->getDateFromDay($i, 'Y-m-d');
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post!',
                    'pub_date' => $date,
                    'post_id' => $counter + 100,
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
            $counter++;
        }

        $posts = array();
        $insight_plugin = new EOYLongestStreakInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_longest_streak', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $total_days = $days+1;
        $end_date = $insight_plugin->getDateFromDay($days, 'F jS');
        $this->assertEqual("Mark Zuckerberg has posted to Facebook every single day in " .
            "$year!", $result->headline);
        $this->assertEqual("Facebook is great for sharing what we're up to, and in $year, " .
            "Mark Zuckerberg was up to a lot &mdash; posting at least one time every day " .
            "so far in 2015 for a streak of <strong>$total_days days</strong>, from January 1st through $end_date.",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Everyday case, Facebook");
    }
}

