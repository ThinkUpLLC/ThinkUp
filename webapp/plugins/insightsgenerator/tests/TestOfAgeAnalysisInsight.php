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

class TestOfAgeAnalysisInsight extends ThinkUpInsightUnitTestCase {
    public function setUp() {
        TimeHelper::setTime(2);
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
    	$instance->network_user_id = 1;
    	$instance->network_username = 'Teen Dude';
    	$instance->network = 'facebook';
    	$insight_plugin = new AgeAnalysisInsight();
    	$insight_plugin->generateInsight($instance, null, array(), 1);

    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('age_analysis', 100, $today);
    	$related_data = unserialize($result->related_data);
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Teens said it all');
        $this->assertEqual($result->text, "Teens — people less than 18 years old — had the most to say in response "
            . "to Teen Dude's posts on Facebook this week.");
    	$this->assertEqual($related_data['age_data']['18'], 2);
    	$this->assertEqual($related_data['age_data']['18_25'], 0);
    	$this->assertEqual($related_data['age_data']['25_35'], 0);
    	$this->assertEqual($related_data['age_data']['35_45'], 0);
    	$this->assertEqual($related_data['age_data']['45'], 0);

        $result->id = 1;
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAgeAnalysisForFacebookYoung() {
    	// Get data ready that insight requires
    	$builders = self::buildDataYoung();

    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 1;
    	$instance->network_username = 'Young Lady';
    	$instance->network = 'facebook';
    	$insight_plugin = new AgeAnalysisInsight();
    	$insight_plugin->generateInsight($instance, null, array(), 1);

    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('age_analysis', 100, $today);
    	$related_data = unserialize($result->related_data);
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Young Lady resonates with Generation Z-ers');
        $this->assertEqual($result->text, "Generation Z-ers — people 18-25 years old — had the most to say "
            . "in response to Young Lady's posts on Facebook this week.");
    	$this->assertEqual($related_data['age_data']['18'], 1);
    	$this->assertEqual($related_data['age_data']['18_25'], 2);
    	$this->assertEqual($related_data['age_data']['25_35'], 0);
    	$this->assertEqual($related_data['age_data']['35_45'], 0);
    	$this->assertEqual($related_data['age_data']['45'], 0);

        $result->id = 2;
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAgeAnalysisForFacebookAdults() {
    	// Get data ready that insight requires
    	$builders = self::buildDataAdults();

    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 1;
    	$instance->network_username = 'Adult Person';
    	$instance->network = 'facebook';
    	$insight_plugin = new AgeAnalysisInsight();
    	$insight_plugin->generateInsight($instance, $last_week_of_posts, 1);

    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('age_analysis', 100, $today);
    	$related_data = unserialize($result->related_data);
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Adult Person resonates with Millenials');
        $this->assertEqual($result->text, "Millenials — people 25-35 years old — had the most to say in response "
            . "to Adult Person's posts on Facebook this week.");
    	$this->assertEqual($related_data['age_data']['18'], 1);
    	$this->assertEqual($related_data['age_data']['18_25'], 0);
    	$this->assertEqual($related_data['age_data']['25_35'], 2);
    	$this->assertEqual($related_data['age_data']['35_45'], 0);
    	$this->assertEqual($related_data['age_data']['45'], 0);

        $result->id = 3;
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAgeAnalysisForFacebookMids() {
    	// Get data ready that insight requires
    	$builders = self::buildDataMids();

    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 1;
    	$instance->network_username = 'Mid-Life Gal';
    	$instance->network = 'facebook';
    	$insight_plugin = new AgeAnalysisInsight();
    	$insight_plugin->generateInsight($instance, null, array(), 1);

    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('age_analysis', 100, $today);
    	$related_data = unserialize($result->related_data);
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Mid-Life Gal resonates with Gen X-ers');
        $this->assertEqual($result->text, "Gen X-ers — people 35-45 years old — had the most to say in response "
            . "to Mid-Life Gal's posts on Facebook this week.");
    	$this->assertEqual($related_data['age_data']['18'], 1);
    	$this->assertEqual($related_data['age_data']['18_25'], 0);
    	$this->assertEqual($related_data['age_data']['25_35'], 0);
    	$this->assertEqual($related_data['age_data']['35_45'], 2);
    	$this->assertEqual($related_data['age_data']['45'], 0);

        $result->id = 4;
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAgeAnalysisForFacebookSeniors() {
    	// Get data ready that insight requires
    	$builders = self::buildDataSeniors();

    	$instance = new Instance();
    	$instance->id = 100;
    	$instance->network_user_id = 1;
    	$instance->network_username = 'Old Man';
    	$instance->network = 'facebook';
    	$insight_plugin = new AgeAnalysisInsight();
    	$insight_plugin->generateInsight($instance, null, array(), 1);

    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('age_analysis', 100, $today);
    	$related_data = unserialize($result->related_data);
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Old Man resonates with Baby Boomers');
        $this->assertEqual($result->text, "Baby Boomers — people 45+ years old — had the most to say in response "
            . "to Old Man's posts on Facebook this week.");
    	$this->assertEqual($related_data['age_data']['18'], 1);
    	$this->assertEqual($related_data['age_data']['18_25'], 0);
    	$this->assertEqual($related_data['age_data']['25_35'], 0);
    	$this->assertEqual($related_data['age_data']['35_45'], 0);
    	$this->assertEqual($related_data['age_data']['45'], 2);

        $result->id = 5;
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    private function buildDataTeens() {
    	$builders= array();

    	$now = date('Y-m-d H:i:s');

    	$builders[] = FixtureBuilder::build('posts', array('id'=>233, 'post_id'=>233, 'author_user_id'=>1,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>4, 'is_protected'=>0,'favlike_count_cache' => 4));

    	$builders[] = FixtureBuilder::build('posts', array('id'=>910, 'post_id'=>910, 'author_user_id'=>10,
    			'author_username'=>'user10', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 233));

    	$builders[] = FixtureBuilder::build('posts', array('id'=>919, 'post_id'=>919, 'author_user_id'=>19,
    			'author_username'=>'user19', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>10, 'user_name'=>'user',
            'full_name'=>'User', 'gender'=>'female', 'birthday'=> '-'.(365*10).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>19, 'user_name'=>'user2',
            'full_name'=>'User2', 'gender'=>'female', 'birthday'=> '-'.(365*14).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'user3',
            'full_name'=>'User3', 'gender'=>'male', 'birthday'=> '-'.(365*20).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	return $builders;
    }

    private function buildDataYoung() {
    	$builders= array();

    	$now = date('Y-m-d H:i:s');

    	$builders[] = FixtureBuilder::build('posts', array('id'=>233, 'post_id'=>233, 'author_user_id'=>1,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>4, 'is_protected'=>0,'favlike_count_cache' => 4));

    	$builders[] = FixtureBuilder::build('posts', array('id'=>910, 'post_id'=>910, 'author_user_id'=>10,
    			'author_username'=>'user10', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 233));

    	$builders[] = FixtureBuilder::build('posts', array('id'=>919, 'post_id'=>919, 'author_user_id'=>19,
    			'author_username'=>'user19', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));

    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>1,
    			'fav_of_user_id'=>20, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>10, 'user_name'=>'user',
            'full_name'=>'User', 'gender'=>'female', 'birthday'=> '-'.(365*10).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>19, 'user_name'=>'user2',
            'full_name'=>'User2', 'gender'=>'female', 'birthday'=> '-'.(365*19).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'user3',
            'full_name'=>'User3', 'gender'=>'male', 'birthday'=> '-'.(365*20).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	return $builders;
    }

    private function buildDataAdults() {
    	$builders= array();

    	$now = date('Y-m-d H:i:s');

    	$builders[] = FixtureBuilder::build('posts', array('id'=>233, 'post_id'=>233, 'author_user_id'=>1,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>4, 'is_protected'=>0,'favlike_count_cache' => 4));

    	$builders[] = FixtureBuilder::build('posts', array('id'=>910, 'post_id'=>910, 'author_user_id'=>10,
    			'author_username'=>'user10', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 233));

    	$builders[] = FixtureBuilder::build('posts', array('id'=>919, 'post_id'=>919, 'author_user_id'=>19,
    			'author_username'=>'user19', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));

    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>1,
    			'fav_of_user_id'=>20, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>10, 'user_name'=>'user',
            'full_name'=>'User', 'gender'=>'female', 'birthday'=> '-'.(365*10).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>19, 'user_name'=>'user2',
            'full_name'=>'User2', 'gender'=>'female', 'birthday'=> '-'.(365*26).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'user3',
            'full_name'=>'User3', 'gender'=>'male', 'birthday'=> '-'.(365*30).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	return $builders;
    }

    private function buildDataMids() {
    	$builders = array();

    	$now = date('Y-m-d H:i:s');

    	$builders[] = FixtureBuilder::build('posts', array('id'=>233, 'post_id'=>233, 'author_user_id'=>1,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>4, 'is_protected'=>0,'favlike_count_cache' => 4));

    	$builders[] = FixtureBuilder::build('posts', array('id'=>910, 'post_id'=>910, 'author_user_id'=>10,
    			'author_username'=>'user10', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 233));

    	$builders[] = FixtureBuilder::build('posts', array('id'=>919, 'post_id'=>919, 'author_user_id'=>19,
    			'author_username'=>'user19', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));

    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>1,
    			'fav_of_user_id'=>20, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>10, 'user_name'=>'user',
            'full_name'=>'User', 'gender'=>'female', 'birthday'=> '-'.(365*10).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>19, 'user_name'=>'user2',
            'full_name'=>'User2', 'gender'=>'female', 'birthday'=> '-'.(365*37).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'user3',
            'full_name'=>'User3', 'gender'=>'male', 'birthday'=> '-'.(365*40).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	return $builders;
    }

    private function buildDataSeniors() {
    	$builders = array();

    	$now = date('Y-m-d H:i:s');

    	$builders[] = FixtureBuilder::build('posts', array('id'=>233, 'post_id'=>233, 'author_user_id'=>1,
    			'author_username'=>'user1', 'author_fullname'=>'User1', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>4, 'is_protected'=>0,'favlike_count_cache' => 4));

    	$builders[] = FixtureBuilder::build('posts', array('id'=>910, 'post_id'=>910, 'author_user_id'=>10,
    			'author_username'=>'user10', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
    			'in_reply_to_post_id' => 233));

    	$builders[] = FixtureBuilder::build('posts', array('id'=>919, 'post_id'=>919, 'author_user_id'=>19,
    			'author_username'=>'user19', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment 1.',
    			'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,
    			'in_reply_to_post_id' => 233));

    	$builders[] = FixtureBuilder::build('favorites', array('post_id'=>233, 'author_user_id'=>1,
    			'fav_of_user_id'=>20, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>10, 'user_name'=>'user',
            'full_name'=>'User', 'gender'=>'female', 'birthday'=> '-'.(365*10).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>19, 'user_name'=>'user2',
            'full_name'=>'User2', 'gender'=>'female', 'birthday'=> '-'.(365*50).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	$builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'user3',
            'full_name'=>'User3', 'gender'=>'male', 'birthday'=> '-'.(365*55).'d',
            'avatar'=>'avatar.jpg', 'is_protected'=>0, 'network'=>'facebook'));

    	return $builders;
    }
}

