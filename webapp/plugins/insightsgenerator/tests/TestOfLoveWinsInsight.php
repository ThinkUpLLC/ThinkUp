<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfLoveWinsInsight.php
 *
 * Copyright (c) 2015 Gina Trapani
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
 * Test for the LoveWinsInsight class.
 *
 * Copyright (c) 2015 Gina Trapani
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2015 Gina Trapani
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/lovewins.php';

class TestOfLoveWinsInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testNoLoveWins() {
        $insight_plugin = new LoveWinsInsight();
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, date('Y-m-d'));
        $this->assertNull($result);
    }

    public function testYesLoveWinsTwitter() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'Luke';
        $instance->network_user_id = '18';
        $instance->network = 'twitter';
        $this->instance = $instance;

        $post_objects = array();
        $builders = array();

        $post_array = array(
            'pub_date' => '2015-05-04', 'post_id' => 1, 'author_username' => $this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
            'post_text' => "I can't believe SCOTUS ruled!",
        );
        $builders[] = FixtureBuilder::build('posts', $post_array);
        $posts[] = new Post($post_array);

        $post_array = array(
            'pub_date' => '2015-01-07', 'post_id' => 2, 'author_username' => $this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
            'post_text' => "omg #lovewins the day!",
        );
        $posts[] = new Post($post_array);
        $builders[] = FixtureBuilder::build('posts', $post_array);

        $post_array = array(
            'pub_date' => '2015-03-07', 'post_id' => 3, 'author_username' => $this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
            'post_text' => "What's up with gay marriage these days?",
        );
        $posts[] = new Post($post_array);
        $builders[] = FixtureBuilder::build('posts', $post_array );

        $post_array =  array(
            'pub_date' => '2014-05-04', 'post_id' => 4, 'author_username' => $this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
            'post_text' => "Happy #Pride yo",
        );
        $posts[] = new Post($post_array);
        $builders[] = FixtureBuilder::build('posts', $post_array);

        $post_array = array(
            'pub_date' => '2014-12-05', 'post_id' => 5, 'author_username' => $this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
            'post_text' => "Today's a good day for marriage equality",
        );
        $posts[] = new Post($post_array);
        $builders[] = FixtureBuilder::build('posts', $post_array);

        $insight_plugin = new LoveWinsInsight();
        $insight_plugin->generateInsight($this->instance, new User(), $posts, 3);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, date("Y-m-d"));
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "@Luke joined the #LoveWins celebration");
        $this->assertEqual($result->text,
            '@Luke was all about <a href="https://twitter.com/hashtag/LoveWins">marriage equality</a> this week.');

        $this->dumpRenderedInsight($result, $this->instance, "Love Wins on Twitter");
    }

    public function testYesLoveWinsFacebook() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'Leia';
        $instance->network_user_id = '18';
        $instance->network = 'facebook';
        $this->instance = $instance;

        $post_objects = array();
        $builders = array();

        $post_array = array(
            'pub_date' => '2015-05-04', 'post_id' => 1, 'author_username' => $this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
            'post_text' => "I can't believe SCOTUS ruled!",
        );
        $builders[] = FixtureBuilder::build('posts', $post_array);
        $posts[] = new Post($post_array);

        $post_array = array(
            'pub_date' => '2015-01-07', 'post_id' => 2, 'author_username' => $this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
            'post_text' => "omg #lovewins the day!",
        );
        $posts[] = new Post($post_array);
        $builders[] = FixtureBuilder::build('posts', $post_array);

        $post_array = array(
            'pub_date' => '2015-03-07', 'post_id' => 3, 'author_username' => $this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
            'post_text' => "What's up with gay marriage these days?",
        );
        $posts[] = new Post($post_array);
        $builders[] = FixtureBuilder::build('posts', $post_array );

        $post_array =  array(
            'pub_date' => '2014-05-04', 'post_id' => 4, 'author_username' => $this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
            'post_text' => "Happy #Pride yo",
        );
        $posts[] = new Post($post_array);
        $builders[] = FixtureBuilder::build('posts', $post_array);

        $post_array = array(
            'pub_date' => '2014-12-05', 'post_id' => 5, 'author_username' => $this->instance->network_username,
            'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
            'post_text' => "Today's a good day for marriage equality",
        );
        $posts[] = new Post($post_array);
        $builders[] = FixtureBuilder::build('posts', $post_array);

        $insight_plugin = new LoveWinsInsight();
        $insight_plugin->generateInsight($this->instance, new User(), $posts, 3);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, date("Y-m-d"));
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Leia had enough pride for all 50 states");
        $this->assertEqual($result->text,
            'Leia joined the <a href="https://facebook.com/celebratepride">marriage equality celebration</a> '
            .'this week!');

        $this->dumpRenderedInsight($result, $this->instance, "Love Wins on Facebook");
    }

    public function testWordInWord() {
        $year = date('Y');
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => $year.'-02-26', 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'Radarscotusthoughts is not a real word.',
            )
        );
        $post->author_username = $this->instance->network_username;
        $post->author_user_id = $this->instance->network_user_id;

        $insight_plugin = new LoveWinsInsight();
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array($post), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, date('Y-m-d'));
        $this->assertNull($result);
    }
}
