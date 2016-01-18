<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfLlamasInsight.php
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
 * Test for the LlamasInsight class.
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/llamas.php';

class TestOfLlamasInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'rihanna';
        $instance->author_id = '18';
        $instance->network_user_id = '18';
        $instance->network = 'twitter';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testNoLlamas() {
        $insight_plugin = new LlamasInsight();
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@rihanna managed to avoid llamageddon!");
        $this->assertEqual($result->text, "It seems like half the internet was " .
            "<a href='http://www.theverge.com/2015/2/26/8116693/live-the-internet-is-going-bananas-for-this-llama-".
            "chase'>talking about runaway llamas</a> ".
            "yesterday. Kudos to @rihanna for showing a llama restraint.");
        $data = unserialize($result->related_data);
        $this->assertNull($data['posts']);

        $this->dumpRenderedInsight($result, $this->instance, "No Llamas");
    }

    public function testYesLlamas() {
        $year = date('Y');
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => $year.'-02-26', 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'This Llama stuff is crazy!',
            )
        );
        $post = new Post();
        $post->pub_date = '2015-02-23';
        $post->post_text = 'This Llama stuff is crazy!';
        $post->author_username = $this->instance->network_username;
        $post->author_user_id = $this->instance->network_user_id;

        $insight_plugin = new LlamasInsight();
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array($post), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "@rihanna showed a whole llama love");
        $this->assertEqual($result->text, "Two runaway llamas <a href='http://www.theverge.com/2015/2/26/8116693"
            ."/live-the-internet-is-going-bananas-for-this-llama-chase'>took over Twitter yesterday</a>,"
            ." and like a llama people, @rihanna couldn't resist.");

        $this->dumpRenderedInsight($result, $this->instance, "Got Llamas");
    }

    public function testWordInWord() {
        $year = date('Y');
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => $year.'-02-26', 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'Bollamas Lanches is a diner in Brazil.',
            )
        );
        $post = new Post();
        $post->pub_date = '2015-02-26';
        $post->post_text = 'Bollamas Lanches is a diner in Brazil.';
        $post->author_username = $this->instance->network_username;
        $post->author_user_id = $this->instance->network_user_id;

        $insight_plugin = new LlamasInsight();
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array($post), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);

        $this->assertNotEqual($result->headline, "@rihanna showed a whole llama love");
        $this->assertNotEqual($result->text, "Two runaway llamas took over Twitter yesterday, and @rihanna was "
            . "no exception.");
        $data = unserialize($result->related_data);
        $this->assertNotEqual(1, count($data['posts']));

        $this->dumpRenderedInsight($result, $this->instance, "No Llamas, just bollamas");
    }
}
