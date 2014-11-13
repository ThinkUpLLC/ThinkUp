<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYExclamationCountInsight.php
 *
 * Copyright (c) 2012-2014 Gina Trapani
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
 * Test of EOYExclamationCountInsight
 *
 * Test for the EOYExclamationCountInsight class.
 *
 * Copyright (c) 2014 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Adam Pash
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoyexclamationcount.php';

class TestOfEOYExclamationCountInsight extends ThinkUpInsightUnitTestCase {

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

    public function testExclamationCount() {
        $insight_plugin = new EOYExclamationCountInsight();

        // posts with !
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post!',
                    'pub_date' => '2014-02-07',
                    'post_id' => $i + 100,
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
        }

        // posts without !
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post',
                    'pub_date' => '2014-02-07',
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
        }

        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getThisYearOfPostsIterator(
            $author_id = $this->instance->network_user_id,
            $network = $this->instance->network
        );

        $count = 0;
        foreach($posts as $key => $value) {
            $count += $insight_plugin->hasExclamationPoint($value->post_text) ? 1 : 0;
        }

        $this->assertEqual(5, $count);
    }

    public function testTwitterNormalCase() {
        // set up posts with exclamation
        $builders = self::setUpPublicInsight($this->instance);
        $counter = 0;
        $max_month = Date('n');
        $year = Date('Y');
        for ($i=1; $i<=$max_month; $i++) {
            $month = "".$i;
            if ($i < 10) {
                $month = "0$month";
            }
            for ($j=0; $j<$i; $j++) {
                $counter++;
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'This is a post that I did!',
                        'pub_date' => "$year-$month-07",
                        'post_id' => $counter + 100,
                        'author_username' => $this->instance->network_username,
                        'author_user_id' => $this->instance->network_user_id,
                        'network' => $this->instance->network,
                    )
                );
            }
        }

        $posts = array();
        $insight_plugin = new EOYExclamationCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_exclamation_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("The !!!'s of Twitter, $year", $result->headline);
        $this->assertEqual("OMG! In $year, @ev used exclamation points in <strong>$counter " .
            "tweets</strong>. That's 100% of @username's total tweets this year!",
            $result->text);

        $this->dumpRenderedInsight($result, "Normal case, Twitter");
        // $this->dumpAllHTML();
    }

    public function testTwitterNoMatches() {
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        // set up posts with no exclamation
        for ($i=1; $i<13; $i++) {
            $month = "".$i;
            if ($i < 10) {
                $month = "0$month";
            }
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post that I did',
                    'pub_date' => "$year-$month-07",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYExclamationCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_exclamation_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("@ev is unimpressed with $year", $result->headline);
        $this->assertEqual("In $year, @ev didn't use one exclamation point on " .
            "Twitter. Must be holding out for something really exciting!", $result->text);

        $this->dumpRenderedInsight($result, "No matches, Twitter");
        // $this->dumpAllHTML();
    }


    public function testFacebookNormalCase() {
        // set up posts with exclamation
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        $max_month = Date('n');
        $counter = 0;
        for ($i=1; $i<=$max_month; $i++) {
            $month = "".$i;
            if ($i < 10) {
                $month = "0$month";
            }
            for ($j=0; $j<$i; $j++) {
                $counter++;
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'This is a post that I did!',
                        'pub_date' => "$year-$month-07",
                        'post_id' => $counter + 100,
                        'author_username' => $this->instance->network_username,
                        'author_user_id' => $this->instance->network_user_id,
                        'network' => $this->instance->network,
                    )
                );
            }
        }

        $posts = array();
        $insight_plugin = new EOYExclamationCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_exclamation_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Mark Zuckerberg's emphatic $year on Facebook!",
            $result->headline);
        $this->assertEqual("Enthusiasm is contagious, and in $year, Mark Zuckerberg " .
            "spread the excitement in a total of <strong>$counter posts</strong> ".
            "containing exclamation points. That's 100% of Mark Zuckerberg's total " .
            "Facebook posts this year!", $result->text);

        $this->dumpRenderedInsight($result, "Normal case, Facebook");
        // $this->dumpAllHTML();
    }

    public function testFacebookNoMatches() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        // set up posts with no exclamation
        for ($i=1; $i<13; $i++) {
            $month = "".$i;
            if ($i < 10) {
                $month = "0$month";
            }
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post that I did',
                    'pub_date' => "$year-$month-07",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYExclamationCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_exclamation_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Mark Zuckerberg is unimpressed with $year", $result->headline);
        $this->assertEqual("In $year, Mark Zuckerberg didn't use one exclamation point on " .
            "Facebook. Must be holding out for something really exciting!", $result->text);

        // $this->dumpRenderedInsight($result, "No matches, Facebook");
        $this->dumpAllHTML();
    }

    private function dumpAllHTML() {
        $controller = new InsightStreamController();
        $_GET['u'] = $this->instance->network_username;
        $_GET['n'] = $this->instance->network;
        $_GET['d'] = date ('Y-m-d');
        $_GET['s'] = 'eoy_exclamation_count';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }

    private function dumpRenderedInsight($result, $message) {
        // return false;
        if (isset($message)) {
            $this->debug("<h4 style=\"text-align: center; margin-top: 20px;\">$message</h4>");
        }
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}

