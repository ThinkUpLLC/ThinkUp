<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfInteractionGraphInsight.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * Test of InteractionGraphInsight
 *
 * Test for the InteractionGraphInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/interactiongraph.php';

class TestOfInteractionGraphInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testGetHashtagSearchURL() {
        $insight_plugin = new InteractionGraphInsight();

        $twitter_url = $insight_plugin->getHashtagSearchURL('#testHashtag', 'twitter');
        $facebook_url = $insight_plugin->getHashtagSearchURL('#testHashtag', 'facebook');
        $googleplus_url = $insight_plugin->getHashtagSearchURL('#testHashtag', 'google+');

        $this->assertEqual($twitter_url, 'https://twitter.com/search?q=%23testHashtag&src=hash');
        $this->assertEqual($facebook_url, 'https://www.facebook.com/hashtag/testHashtag');
        $this->assertEqual($googleplus_url, 'https://plus.google.com/u/0/s/%23testHashtag');
    }

    public function testInteractionGraphInsightText() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh #hashtagOne #hashtagTwo blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh #hashtagOne @mentionOne blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh #hashtagThree @mentionOne blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne @mentionTwo blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));

        $insight_plugin = new InteractionGraphInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("interaction_graph", 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic talked about /', $result->text);
        $this->assertPattern('/\#hashtagOne\<\/a\> \<strong\>2 times/', $result->text);
        $this->assertPattern('/\@mentionOne \<strong\>3 times/', $result->text);
        $this->assertPattern('/and/', $result->text);
    }

    public function testInteractionGraphInsightTextHashtagOnly() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh #hashtagOne #hashtagTwo blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh #hashtagOne blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh #hashtagThree blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));

        $insight_plugin = new InteractionGraphInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("interaction_graph", 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic talked about /', $result->text);
        $this->assertPattern('/\#hashtagOne\<\/a\> \<strong\>2 times/', $result->text);
        $this->assertNoPattern('/and/', $result->text);
    }

    public function testInteractionGraphInsightTextMentionOnly() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne @mentionTwo blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionThree blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));

        $insight_plugin = new InteractionGraphInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("interaction_graph", 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic mentioned /', $result->text);
        $this->assertPattern('/\@mentionOne \<strong\>2 times/', $result->text);
        $this->assertNoPattern('/and/', $result->text);
    }

    public function testInteractionGraphInsightTextRelatedData() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh #hashtagOne #hashtagTwo @mentionOne blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh #hashtagOne @mentionTwo blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh #hashtagThree @mentionOne blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne @mentionThree blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
        ));

        $insight_plugin = new InteractionGraphInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("interaction_graph", 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $dataset = unserialize($result->related_data);
        $this->assertEqual($dataset['hashtags'][0]['hashtag'], '#hashtagOne');
        $this->assertEqual($dataset['hashtags'][0]['count'], 2);
        $this->assertEqual($dataset['hashtags'][0]['url'], 'https://twitter.com/search?q=%23hashtagOne&src=hash');
        $this->assertEqual($dataset['hashtags'][0]['related_mentions'][0], '@mentionOne');
        $this->assertEqual($dataset['hashtags'][0]['related_mentions'][1], '@mentionTwo');
        $this->assertEqual($dataset['mentions'][0]['mention'], '@mentionOne');
        $this->assertEqual($dataset['mentions'][0]['count'], 3);
    }
}
