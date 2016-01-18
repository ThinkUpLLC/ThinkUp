<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfEOYWhoYouFavedInsight.php
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
 *
 * EOYMostFavlikedPostInsight
 *
 * Copyright (c) 2014-2016 Chris Moyer
 *
 * @author Chris Moyer chris@inarow.net
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Chris Moyer
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoywhoyoufaved.php';

class TestOfEOYWhoYouFavedInsight extends ThinkUpInsightUnitTestCase {

    public function setUp() {
        parent::setUp();

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_username = 'georgemichael';
        $this->instance->network_user_id = 11;
        $this->instance->network = 'twitter';
        $this->users = 0;
        $this->posts = 0;

    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new EOYWhoYouFavedInsight();
        $this->assertIsA($insight_plugin, 'EOYWhoYouFavedInsight' );
    }

    public function testNoFaves() {
        $insight_plugin = new EOYWhoYouFavedInsight();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date('Y').'-'.$insight_plugin->run_date;
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNull($result);
    }

    public function testOneFavee() {
        $builders = $this->buildFaver(10, 'Michael','http://upload.wikimedia.org/wikipedia/en/9/90/Michael_Bluth.jpg',
            '"Beloved Son", Brother, Father, keeps the family together.', 123);

        $this->instance->last_post_id = '99999';
        $builders[] = FixtureBuilder::build('posts',
            array( 'post_id' => '99999', 'post_text' => 'This is least liked',
                'pub_date' => date('Y-m-d', strtotime('January 1')),
                'author_username' => $this->instance->network_username, 'network' => $this->instance->network,
                'favlike_count_cache' => 25
            )
        );
        $this->instance->last_post_id = '99999';

        $insight_plugin = new EOYWhoYouFavedInsight();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date('Y').'-'.$insight_plugin->run_date;
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "@georgemichael's most-liked person on Twitter, 2015");
        $this->assertEqual($result->text, "Every time you like a tweet, a little red heart lights up. @georgemichael "
            ."gave the most hearts to @Michael in 2015.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);
        $this->assertEqual($data['people'][0]->username, 'Michael');

        $this->dumpRenderedInsight($result, $this->instance, "One Favee");
    }

    public function testMaxFaveesQualified() {
        $a = $this->buildFaver(1,'Gob','https://upload.wikimedia.org/wikipedia/en/8/8d/GOBwithaJOB.JPG',
            'Founder of the Magician\'s Alliance.', 2);
        $b = $this->buildFaver(9, 'Michael','http://upload.wikimedia.org/wikipedia/en/9/90/Michael_Bluth.jpg',
            '"Beloved Son", Brother, Father, keeps the family together.', 123);
        $c = $this->buildFaver(99, 'Maeby','https://media3.giphy.com/media/wOfmYO0pFGpDa/200_s.gif',
            'Hollywood Mogul', 9123213);

        $this->instance->last_post_id = '99999';
        $d = FixtureBuilder::build('posts',
            array( 'post_id' => '99999', 'post_text' => 'This is least liked',
                'pub_date' => date('Y-m-d', strtotime('March 1')),
                'author_username' => $this->instance->network_username, 'network' => $this->instance->network,
                'favlike_count_cache' => 25
            )
        );
        $this->instance->last_post_id = '99999';

        $insight_plugin = new EOYWhoYouFavedInsight();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date('Y').'-'.$insight_plugin->run_date;
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "@georgemichael's most-liked people on Twitter, 2015");
        $this->assertEqual($result->text, "Every time you like a tweet, a little red heart lights up. ".
            "@georgemichael gave the most hearts to these fine folks in 2015 (at least since March).");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 3);
        $this->assertEqual($data['people'][0]->username, 'Maeby');
        $this->assertEqual($data['people'][1]->username, 'Michael');
        $this->assertEqual($data['people'][2]->username, 'Gob');

        $this->dumpRenderedInsight($result, $this->instance, "One Favee");
    }

    public function testOverMax() {
        $a = $this->buildFaver(1,'Gob','https://upload.wikimedia.org/wikipedia/en/8/8d/GOBwithaJOB.JPG',
            'Founder of the Magician\'s Alliance.', 2);
        $b = $this->buildFaver(9, 'Michael','http://upload.wikimedia.org/wikipedia/en/9/90/Michael_Bluth.jpg',
            '"Beloved Son", Brother, Father, keeps the family together.', 123);
        $c = $this->buildFaver(10, 'Maeby','https://media3.giphy.com/media/wOfmYO0pFGpDa/200_s.gif',
            'Hollywood Mogul', 9123213);
        $d = $this->buildFaver(12, 'George','https://c2.staticflickr.com/6/5344/8774057605_ea4550d9b6_z.jpg',
            'Pop-Pop, Bluth Company Founder, Guru', 8123);


        $this->instance->last_post_id = '99999';
        $e = FixtureBuilder::build('posts',
            array( 'post_id' => '99999', 'post_text' => 'This is least liked',
                'pub_date' => date('Y-m-d', strtotime('March 1')),
                'author_username' => $this->instance->network_username, 'network' => $this->instance->network,
                'favlike_count_cache' => 25
            )
        );
        $this->instance->last_post_id = '99999';

        $insight_plugin = new EOYWhoYouFavedInsight();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date('Y').'-'.$insight_plugin->run_date;
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "@georgemichael's most-liked people on Twitter, 2015");
        $this->assertEqual($result->text, "Every time you like a tweet, a little red heart lights up. @georgemichael "
            ."gave the most hearts to these fine folks in 2015 (at least since March).");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 3);
        $this->assertEqual($data['people'][0]->username, 'George');
        $this->assertEqual($data['people'][1]->username, 'Maeby');
        $this->assertEqual($data['people'][2]->username, 'Michael');

        $this->dumpRenderedInsight($result, $this->instance, "One Favee");
    }


    private function buildFaver($faves, $name, $avatar, $desc, $followers, $date = 'January 4') {
        $this->users++;
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>$this->users, 'user_name'=>$name,
            'full_name'=>$name, 'avatar'=>$avatar, 'follower_count'=>$followers, 'is_protected'=>0,
            'network'=>'twitter', 'description'=>$desc));
        for ($i=0; $i<$faves; $i++) {
            $this->posts++;
            $builders[] = FixtureBuilder::build('posts', array('id'=>$this->posts, 'post_id'=>$this->posts,
                'author_user_id'=> $this->users,
                'author_username'=>$name, 'author_fullname'=>$name, 'author_avatar'=>$avatar,
                'network'=>'twitter', 'post_text'=>'This is a simple post.', 'source'=>'web',
                'pub_date'=>date('Y-m-d', strtotime($date)), 'reply_count_cache'=>0, 'is_protected'=>0));
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>$this->posts,
                'author_user_id'=>$this->users,'fav_of_user_id'=>$this->instance->network_user_id, 'network'=>'twitter',
                'fav_timestamp' => date('Y-m-d', strtotime($date))));
        }
        return $builders;
    }
}
