<?php
/**
 *
 * ThinkUp/tests/TestOfInsightMySQLDAO.php
 *
 * Copyright (c) 2012 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInsightMySQLDAO extends ThinkUpUnitTestCase {
    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    protected function buildData() {
        $builders = array();
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH));

        //Set up array of owner objects
        $o1["id"] = 10;
        $o1["full_name"] = "Jack Black";
        $o1['email'] = "jackblack@example.com";
        $o1['last_login'] = '2012-05-01';
        $o1["is_admin"] = 0;
        $o1["is_activated"] = 1;
        $o1["account_status"] = "Valid";
        $o1["failed_logins"] = 0;

        $o2["id"] = 10;
        $o2["full_name"] = "Jill White";
        $o2['email'] = "jillwhite@example.com";
        $o2['last_login'] = '2012-05-01';
        $o2["is_admin"] = 0;
        $o2["is_activated"] = 1;
        $o2["account_status"] = "Valid";
        $o2["failed_logins"] = 0;

        $o3["id"] = 10;
        $o3["full_name"] = "Joe Schmoe";
        $o3['email'] = "joeschmoek@example.com";
        $o3['last_login'] = '2012-05-01';
        $o3["is_admin"] = 0;
        $o3["is_activated"] = 1;
        $o3["account_status"] = "Valid";
        $o3["failed_logins"] = 0;

        $owner_1 = new Owner($o1);
        $owner_2 = new Owner($o2);
        $owner_3 = new Owner($o3);

        $owners = array();
        $owners[] = $owner_1;
        $owners[] = $owner_2;
        $owners[] = $owner_3;

        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-06-15', 'slug'=>'a_bunch_of_owners',
        'instance_id'=>'1', 'text'=>'Here are owners', 'related_data'=>serialize($owners),
        'emphasis'=>Insight::EMPHASIS_HIGH));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }
    public function testGetInsight() {
        $dao = new InsightMySQLDAO();
        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-01');
        $this->assertIsA($result, 'Insight');
        $this->assertEqual($result->slug, 'avg_replies_per_week');
        $this->assertEqual($result->date, '2012-05-01');
        $this->assertEqual($result->instance_id, 1);
        $this->assertEqual($result->text, 'Retweet spike! Your post got retweeted 110 times');
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);

        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-02');
        $this->assertNull($result);
    }

    public function testGetPreCachedInsightData() {
        $dao = new InsightMySQLDAO();
        $results = $dao->getPreCachedInsightData('avg_replies_per_week', 1, '2012-05-01');
        $this->assertNull($result);

        $result = $dao->getPreCachedInsightData('avg_replies_per_week', 1, '2012-05-02');
        $this->assertNull($result);

        $result = $dao->getPreCachedInsightData('a_bunch_of_owners', 1, '2012-06-15');
        $this->assertNotNull($result);
        $this->assertEqual(sizeof($result), 3);
        $this->assertIsA($result[0], 'Owner');
        $this->assertEqual($result[0]->full_name, 'Jack Black');
        $this->assertEqual($result[1]->full_name, 'Jill White');
        $this->assertEqual($result[2]->full_name, 'Joe Schmoe');
    }

    public function testInsertInsight() {
        $dao = new InsightMySQLDAO();
        //date specified
        $result = $dao->insertInsight('avg_replies_per_week', 1, '2012-05-05', 'Oh hai! You rock');
        $this->assertTrue($result);

        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-05');
        $this->assertEqual($result->text, 'Oh hai! You rock');
        $this->assertNull($result->related_data);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_LOW);

        //inserting existing insight should update
        $result = $dao->insertInsight('avg_replies_per_week', 1, '2012-05-05',  'Oh hai! Updated: You rock',
        Insight::EMPHASIS_HIGH);
        $this->assertTrue($result);

        //assert update was successful
        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-05');
        $this->assertEqual($result->text, 'Oh hai! Updated: You rock');
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);
    }

    public function testUpdateInsight() {
        $dao = new InsightMySQLDAO();

        //update existing baseline
        $result = $dao->updateInsight('avg_replies_per_week', 1, '2012-05-01', 'LOLlerskates', Insight::EMPHASIS_MED);
        $this->assertTrue($result);
        //check that value was updated
        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-01');
        $this->assertEqual($result->text, 'LOLlerskates');
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_MED);

        //update nonexistent baseline
        $result = $dao->updateInsight('avg_replies_per_week', 1, '2012-05-10', 'ooooh burn');
        $this->assertFalse($result);
    }

    public function testDeleteInsight() {
        $dao = new InsightMySQLDAO();

        //delete existing baseline
        $result = $dao->deleteInsight('avg_replies_per_week', 1, '2012-05-01', 'LOLlerskates', Insight::EMPHASIS_MED);
        $this->assertTrue($result);
        //check that insight was deleted
        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-01');
        $this->assertNull($result);

        //delete nonexistent baseline
        $result = $dao->deleteInsight('avg_replies_per_week', 1, '2012-05-10', 'ooooh burn');
        $this->assertFalse($result);
    }
}