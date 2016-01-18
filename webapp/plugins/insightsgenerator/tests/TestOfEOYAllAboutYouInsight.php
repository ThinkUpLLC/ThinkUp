<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYAllAboutYouInsight.php
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
 * Test of EOYAllAboutYouInsight
 *
 * Test for the EOYAllAboutYouInsight class.
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoyallaboutyou.php';

class TestOfEOYAllAboutYouInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testYearOfPostsIterator() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'ev';
        $instance->author_id = '18';
        $instance->network_user_id = '18';
        $instance->network = 'twitter';
        $this->instance = $instance;
        $insight_plugin = new EOYAllAboutYouInsight();

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

        $posts = $insight_plugin->getYearOfPosts($this->instance);
        $this->assertIsA($posts,'PostIterator');
        foreach($posts as $key => $value) {
            $this->assertEqual($value->post_text, "This is a post");
        }
    }

    public function testTwitterNormalCase() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'ev';
        $instance->author_id = '18';
        $instance->network_user_id = '18';
        $instance->network = 'twitter';
        $this->instance = $instance;
        // set up all-about-me posts
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post that I did!',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
        }
        // set up normal non-me posts
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
        }
        $posts = array();
        $insight_plugin = new EOYAllAboutYouInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_all_about_you', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("A year's worth of @ev", $result->headline);
        $this->assertEqual("In $year, <strong>50%</strong> of @ev's tweets " .
            "&mdash; a grand total of 5 &mdash; contained the words " .
            "&ldquo;I&rdquo;, &ldquo;me&rdquo;, &ldquo;my&rdquo;, " .
            "&ldquo;mine&rdquo;, or &ldquo;myself&rdquo;. Sometimes, you've " .
            "just got to get personal.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Twitter");
    }

    public function testFacebookNormalCase() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'The Zuck';
        $instance->author_id = '18';
        $instance->network_user_id = '18';
        $instance->network = 'facebook';
        $this->instance = $instance;
        // set up all-about-me posts
        $year = date('Y');
        $builders = self::setUpPublicInsight($this->instance);
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post that I did!',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
        }
        // set up normal non-me posts
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
        }
        $posts = array();
        $insight_plugin = new EOYAllAboutYouInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('eoy_all_about_you', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("A year about The Zuck", $result->headline);
        $this->assertEqual("In $year, <strong>50%</strong> of The Zuck's status updates " .
            "&mdash; a grand total of 5 &mdash; contained the words " .
            "&ldquo;I&rdquo;, &ldquo;me&rdquo;, &ldquo;my&rdquo;, " .
            "&ldquo;mine&rdquo;, or &ldquo;myself&rdquo;. Go ahead: tell your story.",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Facebook");
    }

    public function testTwitterNoMatches() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'ev';
        $instance->author_id = '18';
        $instance->network_user_id = '18';
        $instance->network = 'twitter';
        $this->instance = $instance;
        // set up all-about-me posts
        $builders = self::setUpPublicInsight($this->instance);
        // set up normal non-me posts
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
        $posts = array();
        $insight_plugin = new EOYAllAboutYouInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight('eoy_all_about_you', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("A year's worth of @ev", $result->headline);
        $this->assertEqual("In $year, none of @ev's tweets contained the words " .
            "&ldquo;I&rdquo;, &ldquo;me&rdquo;, &ldquo;my&rdquo;, " .
            "&ldquo;mine&rdquo;, or &ldquo;myself&rdquo;. Sometimes, you've " .
            "just got to get personal &mdash; unless you're @ev, apparently!", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "No matches, Twitter");
    }
}

