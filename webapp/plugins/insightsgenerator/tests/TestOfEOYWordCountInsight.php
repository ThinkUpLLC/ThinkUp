<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYWordCountInsight.php
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
 * Test of EOYWordCountInsight
 *
 * Test for the EOYWordCountInsight class.
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoywordcount.php';

class TestOfEOYWordCountInsight extends ThinkUpInsightUnitTestCase {

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

    public function testWordCount() {
        $insight_plugin = new EOYWordCountInsight();

        // five sentences with five words = 25 words
        $texts = array();
        for ($i=0; $i<5; $i++) {
            $texts[] =  'This post has five words';
        }
        foreach($texts as $post_text) {
            $word_count += $insight_plugin->countWords($post_text);
        }
        $this->assertEqual(25, $word_count);

        // sentence with eight words including link
        $text = "This is eight with a link to https://twitter.com/";
        $word_count = $insight_plugin->countWords($text);
        $this->assertEqual(8, $word_count);

        // sentence with punctuation
        $text = "This sentence has words. And there. Are. 10 of them!";
        $word_count = $insight_plugin->countWords($text);
        $this->assertEqual(10, $word_count);
    }

    public function testTwitterNormalCase() {
        // set up posts with exclamation
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        for ($i=1; $i<3; $i++) {
            for ($j=0; $j<4; $j++) {
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'This is a post that I did!',
                        'pub_date' => "$year-0$i-07",
                        'author_username' => $this->instance->network_username,
                        'author_user_id' => $this->instance->network_user_id,
                        'network' => $this->instance->network,
                    )
                );
            }
        }
        // one more post in February
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is a post that I did!',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id,
                'network' => $this->instance->network,
            )
        );

        $posts = array();
        $insight_plugin = new EOYWordCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_word_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("63 words at 140 characters or less", $result->headline);
        $this->assertEqual("In $year, @ev entered a total of <strong>63 words</strong> into the Twitter " .
            "data entry box, reaching peak wordage in February, with 35 words. " .
            "Here's the month-by-month breakdown.", $result->text);

        $this->dumpRenderedInsight($result, "Normal case, Twitter");
        // $this->dumpAllHTML();
    }

    public function testFacebookNormalCase() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        for ($i=1; $i<3; $i++) {
            for ($j=0; $j<4; $j++) {
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'This is a post that I did!',
                        'pub_date' => "$year-0$i-07",
                        'author_username' => $this->instance->network_username,
                        'author_user_id' => $this->instance->network_user_id,
                        'network' => $this->instance->network,
                    )
                );
            }
        }
        // one more post in February
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is a post that I did!',
                'pub_date' => "$year-02-07",
                'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id,
                'network' => $this->instance->network,
            )
        );

        $posts = array();
        $insight_plugin = new EOYWordCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_word_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Mark Zuckerberg has a word or two for Facebook", $result->headline);
        $this->assertEqual("In 2014, Mark Zuckerberg safely delivered <strong>63 words</strong> to " .
            "Facebook via the status update or comment box, topping out with 35 words " .
            "in February. Here's a breakdown by month.", $result->text);

        $this->dumpRenderedInsight($result, "Normal case, Facebook");
        // $this->dumpAllHTML();
    }

    private function dumpAllHTML() {
        $controller = new InsightStreamController();
        $_GET['u'] = $this->instance->network_username;
        $_GET['n'] = $this->instance->network;
        $_GET['d'] = date ('Y-m-d');
        $_GET['s'] = 'eoy_word_count';
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

