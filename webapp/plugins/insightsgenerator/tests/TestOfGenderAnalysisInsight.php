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
    
    public function testGenderAnalysisForFaceBook() {
    	//$this->debug(Utils::varDumpToString($result));
    	echo "start\n";
/*     	$posts = array();
    	$posts[] = new Post(array(
    			'post_id' => 5,
    			'author_user_id' => 1,
    			'favlike_count_cache' => 3,
    			'post_text' => "post1",
    			'pub_date' => "2014-06-06 10:38:20",
    			'in_reply_to_post_id' => "NULL",
    			'reply_count_cache' => 1,
    			'favlike_count_cache' => 1
    	));
    	$posts[] = new Post(array(
    			'post_id' => 6,
    			'author_user_id' => 1,
    			'favlike_count_cache' => 0,
    			'post_text' => "comm",
    			'pub_date' => "2014-06-06 10:38:20",
    			'in_reply_to_post_id' => "5",
    			'reply_count_cache' => 0,
    			'favlike_count_cache' => 0
    	));
    	 $fpost_dao = DAOFactory::getDAO('PostDAO'); */
    	 
    	$builders = self::buildData();
    	$instance = new Instance();
    	$instance->id = 10;
    	$instance->network_user_id = 7654321;
    	$instance->network_username = 'user';
    	$instance->network = 'facebook';
    	$insight_plugin = new GenderAnalysisInsight();
    	$insight_plugin->generateInsight($instance, $last_week_of_posts, 3);
    	echo "end\n";
    	$this->assertFalse(true,"sss");
    }
    private function buildData() {
    	$builders = array();
    
    	$now = date('Y-m-d H:i:s');
    	$yesterday = date('Y-m-d H:i:s', strtotime('yesterday'));
    
    	$builders[] = FixtureBuilder::build('posts', array('id'=>133, 'post_id'=>133, 'author_user_id'=>7654321,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=>1, 'is_protected'=>0,'favlike_count_cache' => 1));
    
    $builders[] = FixtureBuilder::build('posts', array('id'=>134, 'post_id'=>134, 'author_user_id'=>7654321,
    		'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    		'network'=>'facebook', 'post_text'=>'This is a simple cooment.',
    		'pub_date'=>$now, 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' => 0,'in_reply_to_post_id' => 133));
    }
    
    
}

