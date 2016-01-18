<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfStarWarsInsight.php
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
 * Test for the StarWarsInsight class.
 *
 * Copyright (c) 2014-2016 Chris Moyer
 *
 * @author Chris Moyer chris@inarow.net
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Adam Pash
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/maythefourth.php';

class TestOfStarWarsInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'Luke';
        $instance->network_user_id = '18';
        $instance->network = 'twitter';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testNoStarWars() {
        $insight_plugin = new StarWarsInsight();
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, date('Y-m-d'));
        $this->assertNull($result);
    }

    public function testYesStarWars() {
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2015-05-04', 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => "I can't believe BB-8 is real!",
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2015-01-07', 'post_id' => 2, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => "omg #StarWars trailer!",
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2015-03-07', 'post_id' => 3, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => "What's up with Darth these days?",
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-05-04', 'post_id' => 4, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => "Happy #StarWarsDay yo",
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => '2014-12-05', 'post_id' => 5, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => "Poor Darth Vader",
            )
        );
        $insight_plugin = new StarWarsInsight();
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), null, 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $insight_plugin->run_date);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "The Force is strong with @Luke on #StarWarsDay");
        $this->assertEqual($result->text, "@Luke was ready for Star Wars Day. May the fourth be with you... always.");

        $this->dumpRenderedInsight($result, $this->instance, "Got Star Wars");
    }

    public function testWordInWord() {
        $year = date('Y');
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => $year.'-02-26', 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'Radarthoughts is not a real word.',
            )
        );
        $post->author_username = $this->instance->network_username;
        $post->author_user_id = $this->instance->network_user_id;

        $insight_plugin = new StarWarsInsight();
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array($post), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, date('Y-m-d'));
        $this->assertNull($result);
    }
}
