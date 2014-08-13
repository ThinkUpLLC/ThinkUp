<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfGeoAnalysisTwitterInsight.php
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
 * GeoAnalysisTwitter (name of file)
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/geoanalysistwitter.php';

class TestOfGeoAnalysisTwitterInsight extends ThinkUpUnitTestCase {

 public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }
    
    public function testGeoAnalysisTwitter() {
    	// Get data ready that insight requires
    	$builders = self::buildData();
    	
    	$instance = new Instance();
    	$instance->id = 1234;
    	$instance->network_user_id = 29654321;
    	$instance->network_username = 'testeriffic';
    	$instance->network = 'twitter';

    	$insight_plugin = new GeoAnalysisTwitterInsight();
    	$insight_plugin->generateInsight($instance, $builders, 4);
    
    	// Assert that insight got inserted
    	$insight_dao = new InsightMySQLDAO();
    	$today = date ('Y-m-d');
    	$result = $insight_dao->getInsight('geo_analysis_twitter', 1234, $today);
    	$geo_data = unserialize($result->related_data);
    	$this->debug(Utils::varDumpToString($result));
    	$this->assertNotNull($result);
    	$this->assertIsA($result, "Insight");
    	$this->assertEqual($result->headline, 'Here am I!');
    	$this->assertIsA($geo_data, "array");
    	$this->assertEqual(count($geo_data[0]), 2);
    	$this->assertEqual($geo_data[0][0]['lat'], '34.7412');
    	$this->assertEqual($geo_data[0][0]['long'], '51.6578');
    	$this->assertEqual($geo_data[0][0]['place'], 'USA');
    	$this->assertEqual($geo_data[0][1]['lat'], '51.5164');
    	$this->assertEqual($geo_data[0][1]['long'], '31.3122');
    	$this->assertEqual($geo_data[0][1]['place'], 'Ukraine');
    }
    
    private function buildData() {
    	$builders = array();
    
    	$builders[] = FixtureBuilder::build('posts', array('author_user_id'=>29654321,
    			'network'=>'twitter', 'post_text'=>'This is a simple post.',
    			'pub_date' => date('Y-m-d H:i:s', strtotime('-3 day')),	'geo'=>'34.7412563,51.6578233',
    			'place'=>'USA'));
    
    	$builders[] = FixtureBuilder::build('posts', array('author_user_id'=>29654321,
    			'network'=>'twitter', 'post_text'=>'This is a simple post 1.',
    			'pub_date' => date('Y-m-d H:i:s', strtotime('-2 day')), 'geo'=>'51.5164641,31.3122106',
    			'place'=>'Ukraine'));
    
    	return $builders;
    
    }
}

