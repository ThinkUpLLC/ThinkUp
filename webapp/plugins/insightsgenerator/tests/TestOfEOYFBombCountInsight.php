<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYFBombCountInsight.php
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
 * Test of EOYFBombCountInsight
 *
 * Test for the EOYFBombCountInsight class.
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoyfbombcount.php';

class TestOfEOYFBombCountInsight extends ThinkUpInsightUnitTestCase {

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

    public function testFBombCount() {
        $insight_plugin = new EOYFBombCountInsight();

        // posts with fucks
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'Fuck yeah, this is a post!',
                    'pub_date' => '2014-02-01',
                    'post_id' => $i,
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
        }

        // posts without fucks
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post',
                    'author_user_id' => $this->instance->network_user_id,
                    'pub_date' => '2014-02-01',
                    'post_id' => $i+10,
                    'author_username' => $this->instance->network_username,
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
            $count += $insight_plugin->hasFBomb($value) ? 1 : 0;
        }

        $this->assertEqual(5, $count);
    }

    public function testTwitterNormalCase() {
        // set up posts with exclamation
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        $counter = 0;
        $this_month = Date('n');
        $this_month_str = Date('F');
        for ($i=1; $i<=$this_month; $i++) {
            $month = "".$i;
            if ($i < 10) {
                $month = "0$month";
            }
            for ($j=0; $j<=$i; $j++) {
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'Fuck yeah, this is a post that I did!',
                        'pub_date' => "$year-$month-01",
                        'post_id' => $counter,
                        'author_user_id' => $this->instance->network_user_id,
                        'author_username' => $this->instance->network_username,
                        'network' => $this->instance->network,
                    )
                );
                $counter++;
            }
        }

        $posts = array();
        $insight_plugin = new EOYFBombCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight('eoy_fbomb_count', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@ev gave $counter fucks on Twitter in $year", $result->headline);
        $this->assertEqual("@ev said &ldquo;fuck&rdquo; <strong>$counter times</strong> on Twitter this " .
            "year, with $this_month_str eliciting the most fucks. Overall: Great fucking year.",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Twitter");
    }

    public function testTwitterOneMatch() {
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
                    'author_user_id' => $this->instance->network_user_id,
                    'pub_date' => "$year-$month-01",
                    'post_id' => $i,
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                )
            );
        }
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is a fucking post that I did',
                'pub_date' => "$year-01-01",
                'post_id' => $i+10,
                'author_user_id' => $this->instance->network_user_id,
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network,
            )
        );

        $posts = array();
        $insight_plugin = new EOYFBombCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight('eoy_fbomb_count', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@ev gave 1 fuck on Twitter in $year", $result->headline);
        $this->assertEqual("@ev said &ldquo;fuck&rdquo; <strong>once</strong> on Twitter this " .
            "year, in January. Overall: Great fucking year.",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "One match, Twitter");
    }


    public function testFacebookNormalCase() {
        // set up posts with exclamation
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        $counter = 0;
        $this_month = Date('n');
        $this_month_str = Date('F');
        for ($i=1; $i<=$this_month; $i++) {
            $month = "".$i;
            if ($i < 10) {
                $month = "0$month";
            }
            for ($j=0; $j<=$i; $j++) {
                $builders[] = FixtureBuilder::build('posts',
                    array(
                        'post_text' => 'Fuck yeah, this is a post that I did!',
                        'pub_date' => "$year-$month-01",
                        'post_id' => $counter,
                        'author_user_id' => $this->instance->network_user_id,
                        'author_username' => $this->instance->network_username,
                        'network' => $this->instance->network,
                    )
                );
                $counter++;
            }
        }

        $posts = array();
        $insight_plugin = new EOYFBombCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight('eoy_fbomb_count', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Mark Zuckerberg is redefining the &ldquo;f&rdquo; in &ldquo;Facebook&rdquo;",
            $result->headline);
        $this->assertEqual("Mark Zuckerberg dropped $counter f-bombs on Facebook in $year, with " .
            "$this_month_str on the receiving end of the most fucks. Fuck yeah.",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Facebook");
    }

    public function testFacebookOneMatch() {
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
                    'pub_date' => "$year-$month-01",
                    'post_id' => $i,
                    'author_user_id' => $this->instance->network_user_id,
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                )
            );
        }
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'Fuck yeah, this is a post that I did',
                'pub_date' => "$year-01-01",
                'post_id' => $i,
                'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id,
                'network' => $this->instance->network,
            )
        );

        $posts = array();
        $insight_plugin = new EOYFBombCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight('eoy_fbomb_count', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Mark Zuckerberg is redefining the &ldquo;f&rdquo; in &ldquo;Facebook&rdquo;",
            $result->headline);
        $this->assertEqual("Mark Zuckerberg dropped 1 f-bomb on Facebook in $year, in " .
            "January. Fuck yeah.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "One match, Facebook");
    }
}
