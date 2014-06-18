<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfGenderAnalysisInsight.php
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
 * GenderAnalysis (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2013 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Anna Shkerina
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/genderanalysis.php';

class TestOfGenderAnalysisInsight extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }
    
    public function testGenderAnalysisForFaceBookWomenFavotire() {
    	// Get data ready that insight requires
    	$builders = self::buildDataForFemale();
    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 9654321;
    	$instance->network_username = 'user';
    	$instance->network = 'facebook';
    	$insight_plugin = new GenderAnalysisInsight();
    	$insight_plugin->generateInsight($instance, $last_week_of_posts, 1);

    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('gender_analysis', 100, $today);
        $gender_data = unserialize($result->related_data);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual($result->headline, 'Women favorite!');
        $this->assertIsA($gender_data, "array");
        $this->assertIsA($gender_data[0], "Post");
        $this->assertEqual(count($gender_data), 2);
        $this->assertEqual($gender_data[1]['female'], 3);
        $this->assertEqual($gender_data[1]['male'], 2);
    }
    
    public function testGenderAnalysisForFaceBookMenFavotire() {
    	// Get data ready that insight requires
    	$builders = self::buildDataForMale();
    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 8654321;
    	$instance->network_username = 'user';
    	$instance->network = 'facebook';
    	$insight_plugin = new GenderAnalysisInsight();
    	$insight_plugin->generateInsight($instance, $last_week_of_posts, 1);
    
    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('gender_analysis', 100, $today);
    	$gender_data = unserialize($result->related_data);
    	$this->debug(Utils::varDumpToString($result));
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Men favorite!');
    	$this->assertIsA($gender_data, "array");
    	$this->assertIsA($gender_data[0], "Post");
    	$this->assertEqual(count($gender_data), 2);
    	$this->assertEqual($gender_data[1]['female'], 2);
    	$this->assertEqual($gender_data[1]['male'], 3);
    }
    
    public function testGenderAnalysisForFaceBookAllFavotire() {
    	// Get data ready that insight requires
    	$builders = self::buildDataForAll();
    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 5654321;
    	$instance->network_username = 'user';
    	$instance->network = 'facebook';
    	$insight_plugin = new GenderAnalysisInsight();
    	$insight_plugin->generateInsight($instance, $last_week_of_posts, 1);
    
    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('gender_analysis', 100, $today);
    	$gender_data = unserialize($result->related_data);
    	$this->debug(Utils::varDumpToString($result));
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Loved by all!');
    	$this->assertIsA($gender_data, "array");
    	$this->assertIsA($gender_data[0], "Post");
    	$this->assertEqual(count($gender_data), 2);
    	$this->assertEqual($gender_data[1]['female'], 2);
    	$this->assertEqual($gender_data[1]['male'], 2);
    }
    
    private function buildDataForFemale() {
    	$builders_female = array();
    
    	$now = date('Y-m-d H:i:s');
    	$yesterday = date('Y-m-d H:i:s', strtotime('yesterday'));
    
    	$builders_female[] = FixtureBuilder::build('posts', array('id'=>333, 'post_id'=>333, 'author_user_id'=>9654321,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>3, 'is_protected'=>0,'favlike_count_cache' => 2));
    
        $builders_female[] = FixtureBuilder::build('posts', array('id'=>334, 'post_id'=>334, 'author_user_id'=>9654321,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
        		'in_reply_to_post_id' => 333));
        
        $builders_female[] = FixtureBuilder::build('posts', array('id'=>335, 'post_id'=>335, 'author_user_id'=>9654320,
        		'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
        		'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
        		'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
        		'in_reply_to_post_id' => 333));
        
        $builders_female[] = FixtureBuilder::build('posts', array('id'=>336, 'post_id'=>336, 'author_user_id'=>9654320,
        		'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
        		'network'=>'facebook', 'post_text'=>'This is a simple comment 2.',
        		'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
        		'in_reply_to_post_id' => 333));
        
        $builders_female[] = FixtureBuilder::build('favorites', array('post_id'=>333, 'author_user_id'=>9654321,
        		'fav_of_user_id'=>9654321, 'network'=>'facebook'));
        
        $builders_female[] = FixtureBuilder::build('favorites', array('post_id'=>333, 'author_user_id'=>9654321,
        		'fav_of_user_id'=>9654320, 'network'=>'facebook'));
        
        $builders_female[] = FixtureBuilder::build('users', array('user_id'=>9654321, 'user_name'=>'user',
        		'full_name'=>'User', 'gender'=>'male', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 
        		'network'=>'facebook'));
        
        $builders_female[] = FixtureBuilder::build('users', array('user_id'=>9654320, 'user_name'=>'user1',
        		'full_name'=>'User1', 'gender'=>'female', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
        		 'network'=>'facebook'));
        
        return $builders_female;
        
    }    
    
    private function buildDataForMale() {
    	$builders_male = array();
    
    	$now = date('Y-m-d H:i:s');
    	$yesterday = date('Y-m-d H:i:s', strtotime('yesterday'));
    
    	$builders_male[] = FixtureBuilder::build('posts', array('id'=>233, 'post_id'=>233, 'author_user_id'=>8654321,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>3, 'is_protected'=>0,'favlike_count_cache' => 2));
    
    	$builders_male[] = FixtureBuilder::build('posts', array('id'=>234, 'post_id'=>234, 'author_user_id'=>8654321,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 233));
    
    	$builders_male[] = FixtureBuilder::build('posts', array('id'=>235, 'post_id'=>235, 'author_user_id'=>8654320,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));
    
    	$builders_male[] = FixtureBuilder::build('posts', array('id'=>236, 'post_id'=>236, 'author_user_id'=>8654320,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 2.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));
    
    	$builders_male[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>8654321,
    			'fav_of_user_id'=>8654321, 'network'=>'facebook'));
    
    	$builders_male[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>8654321,
    			'fav_of_user_id'=>8654320, 'network'=>'facebook'));
    
    	$builders_male[] = FixtureBuilder::build('users', array('user_id'=>8654321, 'user_name'=>'user',
    			'full_name'=>'User', 'gender'=>'female', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders_male[] = FixtureBuilder::build('users', array('user_id'=>8654320, 'user_name'=>'user1',
    			'full_name'=>'User1', 'gender'=>'male', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	return $builders_male;
    
    }
    
    private function buildDataForAll() {
    	$builders= array();
    
    	$now = date('Y-m-d H:i:s');
    	$yesterday = date('Y-m-d H:i:s', strtotime('yesterday'));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>433, 'post_id'=>433, 'author_user_id'=>5654321,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>3, 'is_protected'=>0,'favlike_count_cache' => 2));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>434, 'post_id'=>434, 'author_user_id'=>5654321,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 433));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>435, 'post_id'=>435, 'author_user_id'=>5654320,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 433));
    
    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>433, 'author_user_id'=>5654321,
    			'fav_of_user_id'=>5654321, 'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>433, 'author_user_id'=>5654321,
    			'fav_of_user_id'=>5654320, 'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>5654321, 'user_name'=>'user',
    			'full_name'=>'User', 'gender'=>'female', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	$builders[] = FixtureBuilder::build('users', array('user_id'=>5654320, 'user_name'=>'user1',
    			'full_name'=>'User1', 'gender'=>'male', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
    			'network'=>'facebook'));
    
    	return $builders;
    
    }
}

