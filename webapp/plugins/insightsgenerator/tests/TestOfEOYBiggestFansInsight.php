<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYBiggestFansInsight.php
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
 * Test of EOYBiggestFansInsight
 *
 * Test for the EOYBiggestFansInsight class.
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoybiggestfans.php';

class TestOfEOYBiggestFansInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 10;
        $instance->is_public = true;
        $instance->network_username = 'ev';
        $instance->network_user_id = 100;
        $instance->author_id = '18';
        $instance->network = 'twitter';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testTwitterNormalCase() {
        // set up posts
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        $this->instance->total_posts_in_system = 1500;
        $builders[] = FixtureBuilder::build('instances', array('id'=>11, 'network'=>$this->instance->network,
            'network_username'=>'angel', 'network_user_id'=>100)) ;

        // Users
        $builders[] = FixtureBuilder::build('users', array('user_id'=>101, 'network'=>$this->instance->network,
            'user_name'=>'cordelia', 'avatar' => 'http://www.virginmedia.com/images/cordelia-buffy-then.jpg',
            'full_name' => 'Cordelia Chase'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>102, 'network'=>$this->instance->network,
            'user_name'=>'wesley', 'full_name' => 'Wesley Wyndam-Pryce'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>103, 'network'=>$this->instance->network,
            'user_name'=>'fred', 'avatar' => 'http://38.media.tumblr.com/tumblr_m847r5Q62E1ram4jpo1_500.jpg',
            'full_name' => 'Winifred "Fred" Burkle'));

        // Posts by instance
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'aabbccdd', 'author_user_id'=>100,
            'network'=>$this->instance->network, 'post_text'=>'You gonna like this', 'author_username'=>'angel', 'pub_date'=>"-1d" ));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abcde', 'author_user_id'=>100,
            'network'=>$this->instance->network, 'post_text'=>"Puppy", 'author_username'=>'angel', 'pub_date'=>"-2d" ));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abcd', 'author_user_id'=>100,
            'network'=>$this->instance->network, 'post_text'=>"I'm a champion", 'author_username'=>'angel', 'pub_date'=>"-2d" ));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abcdef', 'author_user_id'=>100,
            'network'=>$this->instance->network, 'post_text'=>"I'm a champion", 'author_username'=>'angel', 'pub_date'=>"-2d" ));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abcdefg', 'author_user_id'=>100,
            'network'=>$this->instance->network, 'post_text'=>"I'm a champion", 'author_username'=>'angel', 'pub_date'=>"-2d" ));

        // Favorites, in order of most to least: cordelia, wesley, fred
        for ($i=1; $i<4; $i++) {
            $fav_of_user_id = 100 + $i;
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'aabbccdd', 'author_user_id'=>100,
                'fav_of_user_id'=>$i, 'network'=>$this->instance->network));
        }
        for ($i=1; $i<4; $i++) {
            $fav_of_user_id = 100 + $i;
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcd', 'author_user_id'=>100,
                'fav_of_user_id'=>$fav_of_user_id, 'network'=>$this->instance->network));
        }
        for ($i=1; $i<4; $i++) {
            $fav_of_user_id = 100 + $i;
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcde', 'author_user_id'=>100,
                'fav_of_user_id'=>$fav_of_user_id, 'network'=>$this->instance->network));
        }

        for ($i=1; $i<3; $i++) {
            $fav_of_user_id = 100 + $i;
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcdef', 'author_user_id'=>100,
                'fav_of_user_id'=>$fav_of_user_id, 'network'=>$this->instance->network));
        }
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcdefg', 'author_user_id'=>100,
            'fav_of_user_id'=>101, 'network'=>$this->instance->network));

        $posts = array();
        $insight_plugin = new EOYBiggestFansInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_biggest_fans', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@ev's biggest Twitter fans of $year", $result->headline);
        $this->assertEqual("It's good to have fans. @cordelia, @wesley, and @fred faved " .
            "@ev's tweets the most this year.", $result->text);

        $this->dumpRenderedInsight($result, "Normal case, Twitter");
        // $this->dumpAllHTML();
    }

    public function testFacebookNormalCase() {
        // set up posts
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        $this->instance->total_posts_in_system = 1500;
        $builders[] = FixtureBuilder::build('instances', array('id'=>11, 'network'=>$this->instance->network,
            'network_username'=>'angel', 'network_user_id'=>100)) ;

        // Users
        $builders[] = FixtureBuilder::build('users', array('user_id'=>101, 'network'=>$this->instance->network,
            'user_name'=>'cordelia', 'avatar' => 'http://www.virginmedia.com/images/cordelia-buffy-then.jpg',
            'full_name' => 'Cordelia Chase'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>102, 'network'=>$this->instance->network,
            'user_name'=>'wesley', 'full_name' => 'Wesley Wyndam-Pryce'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>103, 'network'=>$this->instance->network,
            'user_name'=>'fred', 'avatar' => 'http://38.media.tumblr.com/tumblr_m847r5Q62E1ram4jpo1_500.jpg',
            'full_name' => 'Winifred &ldquo;Fred&rdquo; Burkle'));

        // Posts by instance
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'aabbccdd', 'author_user_id'=>100,
            'network'=>$this->instance->network, 'post_text'=>'You gonna like this', 'author_username'=>'angel', 'pub_date'=>"-1d" ));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abcde', 'author_user_id'=>100,
            'network'=>$this->instance->network, 'post_text'=>"Puppy", 'author_username'=>'angel', 'pub_date'=>"-2d" ));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abcd', 'author_user_id'=>100,
            'network'=>$this->instance->network, 'post_text'=>"I'm a champion", 'author_username'=>'angel', 'pub_date'=>"-2d" ));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abcdef', 'author_user_id'=>100,
            'network'=>$this->instance->network, 'post_text'=>"I'm a champion", 'author_username'=>'angel', 'pub_date'=>"-2d" ));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abcdefg', 'author_user_id'=>100,
            'network'=>$this->instance->network, 'post_text'=>"I'm a champion", 'author_username'=>'angel', 'pub_date'=>"-2d" ));

        // Favorites, in order of most to least: cordelia, wesley, fred
        for ($i=1; $i<4; $i++) {
            $fav_of_user_id = 100 + $i;
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'aabbccdd', 'author_user_id'=>100,
                'fav_of_user_id'=>$i, 'network'=>$this->instance->network));
        }
        for ($i=1; $i<4; $i++) {
            $fav_of_user_id = 100 + $i;
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcd', 'author_user_id'=>100,
                'fav_of_user_id'=>$fav_of_user_id, 'network'=>$this->instance->network));
        }
        for ($i=1; $i<4; $i++) {
            $fav_of_user_id = 100 + $i;
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcde', 'author_user_id'=>100,
                'fav_of_user_id'=>$fav_of_user_id, 'network'=>$this->instance->network));
        }

        for ($i=1; $i<3; $i++) {
            $fav_of_user_id = 100 + $i;
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcdef', 'author_user_id'=>100,
                'fav_of_user_id'=>$fav_of_user_id, 'network'=>$this->instance->network));
        }
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abcdefg', 'author_user_id'=>100,
            'fav_of_user_id'=>101, 'network'=>$this->instance->network));

        $posts = array();
        $insight_plugin = new EOYBiggestFansInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_biggest_fans', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Mark Zuckerberg's biggest Facebook fans of $year", $result->headline);
        $this->assertEqual("It feels great to have friends who support you. " .
            "Cordelia Chase, Wesley Wyndam-Pryce, and Winifred &ldquo;Fred&rdquo; " .
            "Burkle liked Mark Zuckerberg's status updates the most this year.", $result->text);

        $this->dumpRenderedInsight($result, "Normal case, Facebook");
        // $this->dumpAllHTML();
    }

    private function dumpAllHTML() {
        $controller = new InsightStreamController();
        $_GET['u'] = $this->instance->network_username;
        $_GET['n'] = $this->instance->network;
        $_GET['d'] = date ('Y-m-d');
        $_GET['s'] = 'eoy_biggest_fans';
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

