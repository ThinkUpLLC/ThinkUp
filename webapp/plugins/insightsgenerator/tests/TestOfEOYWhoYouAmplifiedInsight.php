<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfEOYWhoYouAmplifiedInsight.php
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
 * EOYWhoYouAmplifiedInsight
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoywhoyouamplified.php';

class TestOfEOYWhoYouAmplifiedInsight extends ThinkUpInsightUnitTestCase {

    public function setUp() {
        parent::setUp();

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_username = 'jerryseinfeld';
        $this->instance->network_user_id = 11;
        $this->instance->network = 'twitter';
        $this->users = 0;
        $this->posts = 0;

    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new EOYWhoYouAmplifiedInsight();
        $this->assertIsA($insight_plugin, 'EOYWhoYouAmplifiedInsight' );
    }

    public function testNoRetweets() {
        $insight_plugin = new EOYWhoYouAmplifiedInsight();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date('Y').'-'.$insight_plugin->run_date;
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNull($result);
    }

    public function testOneAmpee() {
        $builders = $this->buildAmpee(10, 'Kramer','http://upload.wikimedia.org/wikipedia/en/b/b7/Cosmo_Kramer.jpg',
            'Entrepeneur, Shower Chef', 9999);

        $this->instance->last_post_id = '99999';
        $builders[] = FixtureBuilder::build('posts',
            array( 'post_id' => '99999', 'post_text' => 'Old Post',
                'pub_date' => date('Y-m-d', strtotime('January 1')),
                'author_username' => $this->instance->network_username, 'network' => $this->instance->network,
                'favlike_count_cache' => 25
            )
        );
        $this->instance->last_post_id = '99999';

        $insight_plugin = new EOYWhoYouAmplifiedInsight();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date('Y').'-'.$insight_plugin->run_date;
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Who @jerryseinfeld amplified most on Twitter, 2015");
        $this->assertEqual($result->text, "Let's turn this tweet up to 11! In 2015, @jerryseinfeld retweeted this "
            . "user more than any others.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);
        $this->assertEqual($data['people'][0]->username, 'Kramer');

        $this->dumpRenderedInsight($result, $this->instance, "One Retweetee");
    }

    public function testThreeAmpees() {
        $a = $this->buildAmpee(10, 'Elaine','http://deecrowseer.files.wordpress.com/2013/06/seinfeld5-jld01.jpg',
            'J. Peterman Catalog editor', 12, 'March 1');
        $b = $this->buildAmpee(8, 'Kramer','http://upload.wikimedia.org/wikipedia/en/b/b7/Cosmo_Kramer.jpg',
            'Entrepeneur, Shower Chef', 9999, 'June 1');
        $c = $this->buildAmpee(7, 'George','http://upload.wikimedia.org/wikipedia/en/7/70/George_Costanza.jpg',
            'Shrimp Lover', 4, 'July 1');

        $this->instance->last_post_id = '99999';
        $d = FixtureBuilder::build('posts',
            array( 'post_id' => '99999', 'post_text' => 'This is least liked',
                'pub_date' => date('Y-m-d', strtotime('March 1')),
                'author_username' => $this->instance->network_username, 'network' => $this->instance->network,
                'favlike_count_cache' => 25
            )
        );
        $this->instance->last_post_id = '99999';

        $insight_plugin = new EOYWhoYouAmplifiedInsight();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date('Y').'-'.$insight_plugin->run_date;
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Who @jerryseinfeld amplified most on Twitter, 2015");
        $this->assertEqual($result->text, "Let's turn this tweet up to 11! In 2015, @jerryseinfeld retweeted "
            . "these users more than any others (at least since March).");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 3);
        $this->assertEqual($data['people'][0]->username, 'Elaine');
        $this->assertEqual($data['people'][1]->username, 'Kramer');
        $this->assertEqual($data['people'][2]->username, 'George');

        $this->dumpRenderedInsight($result, $this->instance, "Three Retweetees");
    }

    public function testOverMax() {
        $a = $this->buildAmpee(10, 'Elaine','http://deecrowseer.files.wordpress.com/2013/06/seinfeld5-jld01.jpg',
            'J. Peterman Catalog editor', 12, 'March 1');
        $b = $this->buildAmpee(8, 'Kramer','http://upload.wikimedia.org/wikipedia/en/b/b7/Cosmo_Kramer.jpg',
            'Entrepeneur, Shower Chef', 9999, 'June 1');
        $c = $this->buildAmpee(7, 'George','http://upload.wikimedia.org/wikipedia/en/7/70/George_Costanza.jpg',
            'Shrimp Lover', 4, 'July 1');
        $d = $this->buildAmpee(1, 'Newman','...', 'Postal Worked', 4, 'July 1');

        $this->instance->last_post_id = '99999';
        $e = FixtureBuilder::build('posts',
            array( 'post_id' => '99999', 'post_text' => 'This is least liked',
                'pub_date' => date('Y-m-d', strtotime('January 1, 2011')),
                'author_username' => $this->instance->network_username, 'network' => $this->instance->network,
                'favlike_count_cache' => 25
            )
        );
        $this->instance->last_post_id = '99999';

        $insight_plugin = new EOYWhoYouAmplifiedInsight();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date('Y').'-'.$insight_plugin->run_date;
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $today);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Who @jerryseinfeld amplified most on Twitter, 2015");
        $this->assertEqual($result->text, "Let's turn this tweet up to 11! In 2015, @jerryseinfeld retweeted "
            . "these users more than any others.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 3);
        $this->assertEqual($data['people'][0]->username, 'Elaine');
        $this->assertEqual($data['people'][1]->username, 'Kramer');
        $this->assertEqual($data['people'][2]->username, 'George');

        $this->dumpRenderedInsight($result, $this->instance, "Four Retweetees, Three Shown");
    }


    private function buildAmpee($retweets, $name, $avatar, $desc, $followers, $date = 'January 4') {
        $this->users++;
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>$this->users, 'user_name'=>$name,
            'full_name'=>$name, 'avatar'=>$avatar, 'follower_count'=>$followers, 'is_protected'=>0,
            'network'=>'twitter', 'description'=>$desc));
        for ($i=0; $i<$retweets; $i++) {
            $this->posts++;
            $builders[] = FixtureBuilder::build('posts', array('id'=>$this->posts, 'post_id'=>$this->posts,
                'author_user_id'=> $this->users,
                'author_username'=>$name, 'author_fullname'=>$name, 'author_avatar'=>$avatar,
                'network'=>'twitter', 'post_text'=>'This is a simple post.', 'source'=>'web',
                'pub_date'=>date('Y-m-d', strtotime($date)), 'reply_count_cache'=>0, 'is_protected'=>0));
            $this->posts++;
            $builders[] = FixtureBuilder::build('posts', array('id'=>$this->posts, 'post_id'=>$this->posts,
                'author_user_id'=> $this->instance->network_user_id,
                'in_rt_of_user_id' => $this->users, 'in_retweet_of_post_id' => $this->posts-1,
                'author_username'=>$name, 'author_fullname'=>$name, 'author_avatar'=>$avatar,
                'network'=>'twitter', 'post_text'=>'This is a simple post.', 'source'=>'web',
                'pub_date'=>date('Y-m-d', strtotime($date)), 'reply_count_cache'=>0, 'is_protected'=>0));
        }
        return $builders;
    }
}
