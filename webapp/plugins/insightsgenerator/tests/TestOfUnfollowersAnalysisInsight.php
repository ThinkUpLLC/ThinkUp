<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfUnfollowersAnalysisInsight.php
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
 * UnfollowersAnalysis (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Anna Shkerina
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/unfollowersanalysis.php';

class TestOfUnfollowersAnalysisInsight extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }
    
    public function testUnfollowersAnalysisInsight() {
    	// Get data ready that insight requires
    	$instance = new Instance();
    	$instance->id = 19;
    	$instance->network_user_id = 9954000768;
    	$instance->network_username = 'testuser';
    	$instance->network = 'twitter';
        
    	$builders = array();
    	 
    	$now = date('Y-m-d H:i:s');
    	$yesterday = date('Y-m-d H:i:s', strtotime('yesterday'));
    	// Users
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>'9954000768', 'user_name'=>'testuser',
    			'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>1,
    			'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));
    	 
    	// Followers
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>'9954000769', 'user_name'=>'testfollower1',
    			'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
    			'network'=>'twitter'));
    	 
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>'9954000770', 'user_name'=>'testfollower2',
    			'full_name'=>'Twitter Follower Two', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
    			'network'=>'twitter'));
    	 
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>'9954000771', 'user_name'=>'testfollower3',
    			'full_name'=>'Twitter Follower Three', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
    			'network'=>'twitter'));
    	 
    	// Follows
    	$builders[] = FixtureBuilder::build('follows', array('user_id'=>'9954000768', 'follower_id'=>'9954000769',
    			'last_seen'=>$now, 'first_seen'=>$now, 'unfollowed'=>0, 'network'=>'twitter'));
    	 
    	$builders[] = FixtureBuilder::build('follows', array('user_id'=>'9954000768', 'follower_id'=>'9954000770',
    			'last_seen'=>$now, 'first_seen'=>$now, 'unfollowed'=>0, 'network'=>'twitter'));
    	 
    	$builders[] = FixtureBuilder::build('follows', array('user_id'=>'9954000768', 'follower_id'=>'9954000771',
    			'last_seen'=>$now, 'first_seen'=>$yesterday, 'unfollowed'=>1,'network'=>'twitter'));
    	 
    	
    	// Initialize and run the insight
    	$insight_plugin = new UnfollowersAnalysisInsight();
    	$insight_plugin->generateInsight($instance, $posts=array(), 5);
    
    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('unfollowers_analysis', 19, $today);
    	$users = unserialize($result->related_data);
    	$this->debug(Utils::varDumpToString($result));
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Them left you');
    	$this->assertIsA($users[0], "User");
    	$this->assertEqual(count($users), 1);
    }
    
}

