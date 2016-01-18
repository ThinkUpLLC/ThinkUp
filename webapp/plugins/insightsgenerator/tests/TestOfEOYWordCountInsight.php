<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYWordCountInsight.php
 *
 * Copyright (c) 2012-2016 Gina Trapani
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
        // set up posts
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        for ($i=1; $i<3; $i++) {
            for ($j=0; $j<49; $j++) {
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'This is a post that I did! This is a post that I did! '.
                            'This is a post that I did! This is a post that I did! This is a post that I did!',
                        'pub_date' => "$year-0$i-07",
                        'author_username' => $this->instance->network_username,
                        'author_user_id' => $this->instance->network_user_id,
                        'network' => $this->instance->network,
                        'in_retweet_of_post_id' => null,
                        'post_id' => $i + '-' . $j
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
                'in_retweet_of_post_id' => null
            )
        );

        $posts = array();
        $insight_plugin = new EOYWordCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight('eoy_word_count', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@ev tweeted 3,437 words in 2015", $result->headline);
        $this->assertEqual("In 2015, @ev entered a grand total of <strong>3,437 words</strong> into the Twitter ".
            "data entry box, reaching peak wordage in February, with 1,722 words. If @ev were writing a book, that ".
            "would be about 12 pages. Here's the month-by-month breakdown.",
            $result->text);
        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Twitter");
    }

    public function testFacebookNormalCase() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = date('Y');
        for ($i=3; $i<3; $i++) {
            for ($j=0; $j<4; $j++) {
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'This is a post that I did!',
                        'pub_date' => "$year-0$i-07",
                        'author_username' => $this->instance->network_username,
                        'author_user_id' => $this->instance->network_user_id,
                        'network' => $this->instance->network,
                        'in_retweet_of_post_id' => null
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
                'in_retweet_of_post_id' => null
            )
        );

        $posts = array();
        $insight_plugin = new EOYWordCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight('eoy_word_count', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Mark Zuckerberg had a word or two (or 7) for Facebook in 2015", $result->headline);
        $this->assertEqual("In 2015, Mark Zuckerberg typed and tapped <strong>7 words</strong> into Facebook's ".
            "status update or comment box (at least since February), topping out with 7 words in February. ".
            "Here's a breakdown by month.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Facebook");
    }
}
