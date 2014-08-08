<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfAgeAnalysisInsight.php
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
 * AgeAnalysis (name of file)
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/ageanalysis.php';

class TestOfAgeAnalysisInsight extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }
    
    public function testAgeAnalysisForFacebookTeens() {
    	// Get data ready that insight requires
    	$builders = self::buildDataTeens();
    
    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 8654321;
    	$instance->network_username = 'user';
    	$instance->network = 'facebook';
    	$insight_plugin = new AgeAnalysisInsight();
    	$insight_plugin->generateInsight($instance, $last_week_of_posts, 1);
    
    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('age_analysis', 100, $today);
    	$age_data = unserialize($result->related_data);
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Teens!');
    	$this->assertIsA($age_data, "array");
    	$this->assertIsA($age_data[0], "Post");
    	$this->assertEqual(count($age_data), 2);
    	$this->assertEqual($age_data[1]['18'], 2);
    	$this->assertEqual($age_data[1]['18_25'], 0);
    	$this->assertEqual($age_data[1]['25_35'], 0);
    	$this->assertEqual($age_data[1]['35_45'], 0);
    	$this->assertEqual($age_data[1]['45'], 0);
    }
    
    public function testAgeAnalysisForFacebookYoung() {
    	// Get data ready that insight requires
    	$builders = self::buildDataYoung();
    
    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 8654321;
    	$instance->network_username = 'user';
    	$instance->network = 'facebook';
    	$insight_plugin = new AgeAnalysisInsight();
    	$insight_plugin->generateInsight($instance, $last_week_of_posts, 1);
    
    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('age_analysis', 100, $today);
    	$age_data = unserialize($result->related_data);
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'So young!');
    	$this->assertIsA($age_data, "array");
    	$this->assertIsA($age_data[0], "Post");
    	$this->assertEqual(count($age_data), 2);
    	$this->assertEqual($age_data[1]['18'], 1);
    	$this->assertEqual($age_data[1]['18_25'], 2);
    	$this->assertEqual($age_data[1]['25_35'], 0);
    	$this->assertEqual($age_data[1]['35_45'], 0);
    	$this->assertEqual($age_data[1]['45'], 0);
    }
    
    public function testAgeAnalysisForFacebookAdults() {
    	// Get data ready that insight requires
    	$builders = self::buildDataAdults();
    
    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 8654321;
    	$instance->network_username = 'user';
    	$instance->network = 'facebook';
    	$insight_plugin = new AgeAnalysisInsight();
    	$insight_plugin->generateInsight($instance, $last_week_of_posts, 1);
    
    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('age_analysis', 100, $today);
    	$age_data = unserialize($result->related_data);
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Oh, adults!');
    	$this->assertIsA($age_data, "array");
    	$this->assertIsA($age_data[0], "Post");
    	$this->assertEqual(count($age_data), 2);
    	$this->assertEqual($age_data[1]['18'], 1);
    	$this->assertEqual($age_data[1]['18_25'], 0);
    	$this->assertEqual($age_data[1]['25_35'], 2);
    	$this->assertEqual($age_data[1]['35_45'], 0);
    	$this->assertEqual($age_data[1]['45'], 0);
    }
    
    public function testAgeAnalysisForFacebookMids() {
    	// Get data ready that insight requires
    	$builders = self::buildDataMids();
    
    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 8654321;
    	$instance->network_username = 'user';
    	$instance->network = 'facebook';
    	$insight_plugin = new AgeAnalysisInsight();
    	$insight_plugin->generateInsight($instance, $last_week_of_posts, 1);
    
    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('age_analysis', 100, $today);
    	$age_data = unserialize($result->related_data);
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Middle-aged!');
    	$this->assertIsA($age_data, "array");
    	$this->assertIsA($age_data[0], "Post");
    	$this->assertEqual(count($age_data), 2);
    	$this->assertEqual($age_data[1]['18'], 1);
    	$this->assertEqual($age_data[1]['18_25'], 0);
    	$this->assertEqual($age_data[1]['25_35'], 0);
    	$this->assertEqual($age_data[1]['35_45'], 2);
    	$this->assertEqual($age_data[1]['45'], 0);
    }
    
    public function testAgeAnalysisForFacebookSeniors() {
    	// Get data ready that insight requires
    	$builders = self::buildDataSeniors();
    
    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 8654321;
    	$instance->network_username = 'user';
    	$instance->network = 'facebook';
    	$insight_plugin = new AgeAnalysisInsight();
    	$insight_plugin->generateInsight($instance, $last_week_of_posts, 1);
    
    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('age_analysis', 100, $today);
    	$age_data = unserialize($result->related_data);
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Seniors!');
    	$this->assertIsA($age_data, "array");
    	$this->assertIsA($age_data[0], "Post");
    	$this->assertEqual(count($age_data), 2);
    	$this->assertEqual($age_data[1]['18'], 1);
    	$this->assertEqual($age_data[1]['18_25'], 0);
    	$this->assertEqual($age_data[1]['25_35'], 0);
    	$this->assertEqual($age_data[1]['35_45'], 0);
    	$this->assertEqual($age_data[1]['45'], 2);
    }
    
    private function buildDataTeens() {
    	$builders_male = array();
    
    	$now = date('Y-m-d H:i:s');
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>233, 'post_id'=>233, 'author_user_id'=>8654321,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>2, 'is_protected'=>0,'favlike_count_cache' => 2));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>234, 'post_id'=>234, 'author_user_id'=>8654320,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 233));
    	
    	$builders[] = FixtureBuilder::build('posts', array('id'=>237, 'post_id'=>237, 'author_user_id'=>8654324,
    			'author_username'=>'user4', 'author_fullname'=>'User4', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 3.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));
    
    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>8654321,
    			'fav_of_user_id'=>8654320, 'network'=>'facebook'));
    	
    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>8654321,
    			'fav_of_user_id'=>8654324, 'network'=>'facebook'));
    	
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654320, 'user_name'=>'user',
    			'full_name'=>'User', 'gender'=>'female', 'birthday'=> '06/23/2002', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654321, 'user_name'=>'user1',
    			'full_name'=>'User1', 'gender'=>'male', 'birthday'=> '08/01/1994', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    	
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654322, 'user_name'=>'user2',
    			'full_name'=>'User2', 'gender'=>'female', 'birthday'=> '06/23/1985', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    	
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654323, 'user_name'=>'user3',
    			'full_name'=>'User3', 'gender'=>'male', 'birthday'=> '06/23/1975', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    	
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654324, 'user_name'=>'user4',
    			'full_name'=>'User4', 'gender'=>'female', 'birthday'=> '06/23', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	return $builders;    
    }
    
    private function buildDataYoung() {
    	$builders_male = array();
    
    	$now = date('Y-m-d H:i:s');
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>233, 'post_id'=>233, 'author_user_id'=>8654321,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>4, 'is_protected'=>0,'favlike_count_cache' => 4));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>234, 'post_id'=>234, 'author_user_id'=>8654320,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 233));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>235, 'post_id'=>235, 'author_user_id'=>8654321,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));
           
    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>8654321,
    			'fav_of_user_id'=>8654321, 'network'=>'facebook'));
    	 
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654320, 'user_name'=>'user',
    			'full_name'=>'User', 'gender'=>'female', 'birthday'=> '06/23/2010', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654321, 'user_name'=>'user1',
    			'full_name'=>'User1', 'gender'=>'male', 'birthday'=> '08/01/1994', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    	 
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654322, 'user_name'=>'user2',
    			'full_name'=>'User2', 'gender'=>'female', 'birthday'=> '06/23/1985', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    	 
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654323, 'user_name'=>'user3',
    			'full_name'=>'User3', 'gender'=>'male', 'birthday'=> '06/23/1975', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    	 
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654324, 'user_name'=>'user4',
    			'full_name'=>'User4', 'gender'=>'female', 'birthday'=> '06/23', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	return $builders;
    }
    
    private function buildDataAdults() {
    	$builders_male = array();
    
    	$now = date('Y-m-d H:i:s');
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>233, 'post_id'=>233, 'author_user_id'=>8654321,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>4, 'is_protected'=>0,'favlike_count_cache' => 4));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>234, 'post_id'=>234, 'author_user_id'=>8654320,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 233));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>235, 'post_id'=>235, 'author_user_id'=>8654322,
    			'author_username'=>'user2', 'author_fullname'=>'User2', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));
    	 
    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>8654321,
    			'fav_of_user_id'=>8654322, 'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654320, 'user_name'=>'user',
    			'full_name'=>'User', 'gender'=>'female', 'birthday'=> '06/23/2010', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654321, 'user_name'=>'user1',
    			'full_name'=>'User1', 'gender'=>'male', 'birthday'=> '08/01/1994', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654322, 'user_name'=>'user2',
    			'full_name'=>'User2', 'gender'=>'female', 'birthday'=> '06/23/1985', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654323, 'user_name'=>'user3',
    			'full_name'=>'User3', 'gender'=>'male', 'birthday'=> '06/23/1975', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654324, 'user_name'=>'user4',
    			'full_name'=>'User4', 'gender'=>'female', 'birthday'=> '06/23', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	return $builders;
    }
    
    private function buildDataMids() {
    	$builders_male = array();
    
    	$now = date('Y-m-d H:i:s');
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>233, 'post_id'=>233, 'author_user_id'=>8654321,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>4, 'is_protected'=>0,'favlike_count_cache' => 4));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>234, 'post_id'=>234, 'author_user_id'=>8654320,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 233));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>235, 'post_id'=>235, 'author_user_id'=>8654323,
    			'author_username'=>'user3', 'author_fullname'=>'User3', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));
    
    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>8654321,
    			'fav_of_user_id'=>8654323, 'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654320, 'user_name'=>'user',
    			'full_name'=>'User', 'gender'=>'female', 'birthday'=> '06/23/2010', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654321, 'user_name'=>'user1',
    			'full_name'=>'User1', 'gender'=>'male', 'birthday'=> '08/01/1994', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654322, 'user_name'=>'user2',
    			'full_name'=>'User2', 'gender'=>'female', 'birthday'=> '06/23/1985', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654323, 'user_name'=>'user3',
    			'full_name'=>'User3', 'gender'=>'male', 'birthday'=> '06/23/1975', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654324, 'user_name'=>'user4',
    			'full_name'=>'User4', 'gender'=>'female', 'birthday'=> '06/23', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	return $builders;
    }
    
    private function buildDataSeniors() {
    	$builders_male = array();
    
    	$now = date('Y-m-d H:i:s');
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>233, 'post_id'=>233, 'author_user_id'=>8654321,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>4, 'is_protected'=>0,'favlike_count_cache' => 4));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>234, 'post_id'=>234, 'author_user_id'=>8654320,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 233));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>235, 'post_id'=>235, 'author_user_id'=>8654324,
    			'author_username'=>'user4', 'author_fullname'=>'User4', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));
    
    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>8654321,
    			'fav_of_user_id'=>8654324, 'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654320, 'user_name'=>'user',
    			'full_name'=>'User', 'gender'=>'female', 'birthday'=> '06/23/2010', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654321, 'user_name'=>'user1',
    			'full_name'=>'User1', 'gender'=>'male', 'birthday'=> '08/01/1994', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654322, 'user_name'=>'user2',
    			'full_name'=>'User2', 'gender'=>'female', 'birthday'=> '06/23/1985', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654323, 'user_name'=>'user3',
    			'full_name'=>'User3', 'gender'=>'male', 'birthday'=> '06/23/1975', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>8654324, 'user_name'=>'user4',
    			'full_name'=>'User4', 'gender'=>'female', 'birthday'=> '06/23/1965', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	return $builders;
    }
}

