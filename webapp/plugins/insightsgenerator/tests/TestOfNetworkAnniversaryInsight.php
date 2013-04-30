<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfNetworkAnniversaryInsight.php
 *
 * Copyright (c) 2013 Cassio Melo
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
 * Test of AllAboutYouInsight
 *
 * Test for the AllAboutYouInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Cassio Melo
 * @author Cassio Melo <melo.cassio[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/networkanniversary.php';

class TestOfNetworkAnniversaryInsight extends ThinkUpUnitTestCase {

    /**
     * Set up a new test case
     */
     public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }
    
    public function tearDown() {
        parent::tearDown();
    }

    
    /**
     * Add sample data for the test case
     * @return FixtureBuilder array containing the objects to be created
     */
    protected function buildData() {
        
        // simulate sign up 5 years ago
        $years_ago = date('Y-m-d', strtotime('-5 years'));
        
        $builders[] = FixtureBuilder::build('users', array('user_id'=>12, 'user_name'=>'jack',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'location'=>'San Francisco', 'post_count' => 3,
        'network'=>'twitter', 'joined' => $years_ago));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'zuck',
        'full_name'=>'Mark Zuckerberg', 'avatar'=>'avatar.jpg', 'location'=>'San Francisco', 'post_count' => 3,
        'network'=>'twitter', 'joined' => $years_ago));
        
        $builders[] = FixtureBuilder::build('posts', array('author_user_id'=>13, 'author_username'=>'zuck',
                'post_text'=>'this is a test post', 'author_avatar'=>'avatar.jpg', 'author_follower_count'=>11,
                'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('posts', array('author_user_id'=>13, 'author_username'=>'zuck',
                'post_text'=>'is this a question post?', 'author_avatar'=>'avatar.jpg', 'author_follower_count'=>11,
                'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('posts', array('author_user_id'=>13, 'author_username'=>'zuck',
                'post_text'=>'another post with a :) smiley', 'author_avatar'=>'avatar.jpg', 'author_follower_count'=>11,
                'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('posts', array('author_user_id'=>13, 'author_username'=>'zuck',
                'post_text'=>'cool =] ) :)', 'author_avatar'=>'avatar.jpg', 'author_follower_count'=>11,
                'network'=>'twitter'));

        $this->logger = Logger::getInstance();
        return $builders;
    }
    
   /**
    * Tests the insight generation
    */
    public function testAllAboutYouInsightNoPriorBaseline() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 13;
        $instance->network_username = 'zuck';
        $instance->network = 'twitter';
        $insight_plugin = new NetworkAnniversaryInsight();
        $insight_plugin->generateInsight($instance, null, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('network_anniversary', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/Happy Twitter birthday/', $result->text);         
        $this->assertPattern('/84 characters/', $result->text);

    }
    
    /**
     * Tests if the number of smileys in a post is correct
     */
    public function testCountSmileys() {
        $count = NetworkAnniversaryInsight::countSmileys("I don't know, really? I thought so.");
        $this->assertEqual($count, 0);
    
        $count = NetworkAnniversaryInsight::countSmileys("another post with a :) smiley");
        $this->assertEqual($count, 1);
    
        $count = NetworkAnniversaryInsight::countSmileys("something =)");
        $this->assertEqual($count, 1);
    
        $count = NetworkAnniversaryInsight::countSmileys("Tis the season for adorable cards w/ photos of my ".
                "friends ;) that remind me what I'd do for the holidays )): if I had =) my act together. :(");
        $this->assertEqual($count, 2);
    
        $count = NetworkAnniversaryInsight::countSmileys("Took 1 firearms safety class to realize my ".
                "fantasy of stopping an attacker was just that: http://bit.ly/mybH2j  Slate: http://slate.me/T6vwde :)");
        $this->assertEqual($count, 1);
    
    }
    
    
    /**
     * Tests if the post has a question
     */
    public function testIsQuestion() {
        $isQuestion = NetworkAnniversaryInsight::isQuestion("I don't know, really? I thought so.");
        $this->assertEqual($isQuestion, true);
    
        $isQuestion = NetworkAnniversaryInsight::isQuestion("another post with a :) smiley");
        $this->assertEqual($isQuestion, false);
    
        $isQuestion = NetworkAnniversaryInsight::isQuestion("who else???");
        $this->assertEqual($isQuestion, true);
    
        $isQuestion = NetworkAnniversaryInsight::isQuestion("Tis the season for adorable cards w/ photos of my ".
                "friends ;) that remind me what I'd do for the holidays )): if I had =) my act together. :(");
        $this->assertEqual($isQuestion, false);
    
        $isQuestion = NetworkAnniversaryInsight::isQuestion("Took 1 firearms safety class to realize my ".
                "fantasy of stopping an attacker was just that: http://bit.ly/mybH2j  Slate: http://slate.me/T6vwde :)");
        $this->assertEqual($isQuestion, false);
    
    }

  
}
