<?php
/**
 *
 * ThinkUp/tests/TestOfInsightMySQLDAO.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
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
        $time_now = date("Y-m-d H:i:s");
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'1', 'headline'=>'Booyah!', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));

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

        $time_now = date("Y-m-d H:i:s");
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-06-15', 'slug'=>'a_bunch_of_owners',
        'instance_id'=>'1', 'headline'=>'Hooray!', 'text'=>'Here are owners', 'related_data'=>serialize($owners),
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));

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
        $this->assertEqual($result->headline, 'Booyah!');
        $this->assertEqual($result->text, 'Retweet spike! Your post got retweeted 110 times');
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);

        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-02');
        $this->assertNull($result);
    }

    public function testGetInsightByUsername() {
        $builders = self::buildData();
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'jo',
        'network'=>'twitter'));

        $dao = new InsightMySQLDAO();
        $result = $dao->getInsightByUsername('jo', 'twitter', 'avg_replies_per_week', '2012-05-01');

        $this->assertIsA($result, 'Insight');
        $this->assertEqual($result->slug, 'avg_replies_per_week');
        $this->assertEqual(date('Y-m-d', strtotime($result->date)), '2012-05-01');
        $this->assertEqual($result->instance_id, 1);
        $this->assertEqual($result->headline, 'Booyah!');
        $this->assertEqual($result->text, 'Retweet spike! Your post got retweeted 110 times');
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);

        $result = $dao->getInsightByUsername('jo', 'twitter', 'avg_replies_per_week', '2012-05-02');
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

    public function testInsertInsightDeprecated() {
        $dao = new InsightMySQLDAO();
        //date specified
        $result = $dao->insertInsightDeprecated($slug='avg_replies_per_week', $instance_id=1, $date='2012-05-05',
        $prefix='Oh hai!', $text='You rock', $filename="test_insight");
        $this->assertTrue($result);

        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-05');
        $this->assertEqual($result->headline, 'Oh hai!');
        $this->assertEqual($result->text, 'You rock');
        $this->assertEqual($result->filename, 'test_insight');
        $this->assertNull($result->related_data);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_LOW);

        //inserting existing insight should update
        $result = $dao->insertInsightDeprecated('avg_replies_per_week', 1, '2012-05-05', 'Ohai!', 'Updated: You rock',
        'tester_insight', Insight::EMPHASIS_HIGH);
        $this->assertTrue($result);

        //assert update was successful
        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-05');
        $this->assertEqual($result->headline, 'Ohai!' );
        $this->assertEqual($result->text, 'Updated: You rock');
        //Filename shouldn't change on update
        $this->assertEqual($result->filename, 'test_insight');
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_HIGH);
    }

    public function testInsertInsight() {
        $dao = new InsightMySQLDAO();
        $insight = new Insight();
        $e = null;
        //test exception when fields are not set
        try {
            $result = $dao->insertInsight($insight);
        } catch (InsightFieldNotSetException $e) {
            //do assertions outside of the catch block to make sure they run every time
        }
        $this->assertNotNull($e);
        $this->assertEqual($e->getMessage(), 'Insight instance_id is not set.');
        $e = null;

        $insight->instance_id = 1;
        $insight->slug = 'avg_replies_per_week';
        $insight->date = '2012-05-05';
        $insight->headline = 'Oh hai!';
        $insight->text = "You rock";
        $insight->emphasis = Insight::EMPHASIS_MED;
        $insight->filename = "test_filename";

        $result = $dao->insertInsight($insight);
        $this->assertTrue($result);

        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-05');
        $this->assertEqual($result->headline, 'Oh hai!');
        $this->assertEqual($result->text, 'You rock');
        $this->assertEqual($result->filename, 'test_filename');
        $this->assertNull($result->related_data);
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_MED);

        //inserting existing insight should update
        $insight->headline = "Ohai updated headline";
        $insight->text = 'Updated: You rock';
        $result = $dao->insertInsight($insight);
        $this->assertTrue($result);

        //assert update was successful
        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-05');
        $this->assertEqual($result->headline, 'Ohai updated headline' );
        $this->assertEqual($result->text, 'Updated: You rock');
        //Filename shouldn't change on update
        $this->assertEqual($result->filename, 'test_filename');
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_MED);
    }

    public function testupdateInsightDeprecated() {
        $dao = new InsightMySQLDAO();

        //update existing baseline
        $result = $dao->updateInsightDeprecated('avg_replies_per_week', 1, '2012-05-01', "Yay", 'LOLlerskates',
        Insight::EMPHASIS_MED);
        $this->assertTrue($result);
        //check that value was updated
        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-01');
        $this->assertEqual($result->headline, 'Yay');
        $this->assertEqual($result->text, 'LOLlerskates');
        $this->assertEqual($result->emphasis, Insight::EMPHASIS_MED);

        //update nonexistent baseline
        $result = $dao->updateInsightDeprecated('avg_replies_per_week', 1, '2012-05-10', 'ooooh burn');
        $this->assertFalse($result);
    }

    public function testDeleteInsight() {
        $dao = new InsightMySQLDAO();

        //delete existing insight
        $result = $dao->deleteInsight('avg_replies_per_week', 1, '2012-05-01');
        $this->assertTrue($result);
        //check that insight was deleted
        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-01');
        $this->assertNull($result);

        //delete nonexistent insight
        $result = $dao->deleteInsight('avg_replies_per_week', 1, '2012-05-10');
        $this->assertFalse($result);
    }

    public function testDeleteInsightsBySlug() {
        $builders = array();
        $time_now = date("Y-m-d H:i:s");
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-02', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'another_slug',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));

        $dao = new InsightMySQLDAO();

        //delete all insights for slug/instance
        $result = $dao->deleteInsightsBySlug('avg_replies_per_week', 1);
        $this->assertTrue($result);
        //check that insights for that slug and instance were deleted
        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-01');
        $this->assertNull($result);
        $result = $dao->getInsight('avg_replies_per_week', 1, '2012-05-02');
        $this->assertNull($result);
        //check that insight with that slug but not for another instance were NOT deleted
        $result = $dao->getInsight('avg_replies_per_week', 2, '2012-05-01');
        $this->assertNotNull($result);

        //delete nonexistent slug
        $result = $dao->deleteInsightsBySlug('avg_replies_per_week_another_slug', 1);
        $this->assertFalse($result);
    }

    public function testGetPublicInsights() {
        $builders = array();
        //insert a public instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>10,
        'network_username'=>'jack', 'network'=>'twitter', 'network_viewer_id'=>10,
        'crawler_last_run'=>'1988-01-20 12:00:00', 'is_active'=>1, 'is_public'=>0));
        //insert a private instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>12,
        'network_username'=>'jill', 'network'=>'twitter', 'network_viewer_id'=>12,
        'crawler_last_run'=>'2010-01-20 12:00:00', 'is_active'=>1, 'is_public'=>1));

        //insert 2 insights for a private instance and 3 for a public instance
        $time_now = date("Y-m-d H:i:s");
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-02', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-02', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-03', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'another_slug',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));

        //assert that page of insights is only 3 long for public instane
        $dao = new InsightMySQLDAO();
        $results = $dao->getPublicInsights($page_count=10, $page_number=1);
        $this->assertEqual(sizeof($results), 3);
        foreach ($results as $result) {
            $this->assertTrue(isset($result->instance));
        }
    }

    public function testGetAllInsights() {
        $builders = array();
        //insert a public instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>10,
        'network_username'=>'jack', 'network'=>'twitter', 'network_viewer_id'=>10,
        'crawler_last_run'=>'1988-01-20 12:00:00', 'is_active'=>1, 'is_public'=>0));
        //insert a private instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>12,
        'network_username'=>'jill', 'network'=>'twitter', 'network_viewer_id'=>12,
        'crawler_last_run'=>'2010-01-20 12:00:00', 'is_active'=>1, 'is_public'=>1));
        //insert a non-active instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>3, 'network_user_id'=>12,
        'network_username'=>'jane', 'network'=>'twitter', 'network_viewer_id'=>12,
        'crawler_last_run'=>'2010-01-20 12:00:00', 'is_active'=>0, 'is_public'=>1));

        //insert 2 insights for a private instance and 3 for a public instance
        $time_now = date("Y-m-d H:i:s");
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-02', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-02', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-03', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'another_slug',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));

        //assert that page of insights includes from both private and public
        $dao = new InsightMySQLDAO();
        $results = $dao->getAllInstanceInsights($page_count=10, $page_number=1);
        $this->assertEqual(sizeof($results), 7);
        foreach ($results as $result) {
            $this->assertTrue(isset($result->instance));
        }
    }

    public function testGetAllOwnerInstanceInsightsSince() {
        $builders = array();
        //insert a public instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>10,
        'network_username'=>'jack', 'network'=>'twitter', 'network_viewer_id'=>10,
        'crawler_last_run'=>'1988-01-20 12:00:00', 'is_active'=>1, 'is_public'=>0));
        //insert a private instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>12,
        'network_username'=>'jill', 'network'=>'twitter', 'network_viewer_id'=>12,
        'crawler_last_run'=>'2010-01-20 12:00:00', 'is_active'=>1, 'is_public'=>1));
        //insert a non-active instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>3, 'network_user_id'=>12,
        'network_username'=>'jane', 'network'=>'twitter', 'network_viewer_id'=>12,
        'crawler_last_run'=>'2010-01-20 12:00:00', 'is_active'=>0, 'is_public'=>1));

        //insert instance owner
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'Owner 1',
        'email'=>'owner@example.com'));
        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'Owner 2',
        'email'=>'owner2@example.com'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>2));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>3));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>2));

        //insert 2 insights for a private instance and 3 for a public instance
        $time_now = date("Y-m-d H:i:s");
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-02', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-02', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-03', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'another_slug',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        //insight with no text shouldn't be returned
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'another_slug',
        'instance_id'=>'1', 'text'=>'',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));

        //assert that page of insights includes from both private and public
        $dao = new InsightMySQLDAO();
        $from = 0;
        $results = $dao->getAllOwnerInstanceInsightsSince(1, $from);
        $this->assertEqual(count($results), 7);
        foreach ($results as $result) {
            $this->assertIsA($result, 'Insight');
        }
        $results = $dao->getAllOwnerInstanceInsightsSince(2, $from);
        $this->assertEqual(count($results), 3);
        foreach ($results as $result) {
            $this->assertIsA($result, 'Insight');
        }
    }

    public function testGetAllOwnerInstanceInsights() {
        $builders = array();
        //insert a public instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>10,
        'network_username'=>'jack', 'network'=>'twitter', 'network_viewer_id'=>10,
        'crawler_last_run'=>'1988-01-20 12:00:00', 'is_active'=>1, 'is_public'=>0));
        //insert a private instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>12,
        'network_username'=>'jill', 'network'=>'twitter', 'network_viewer_id'=>12,
        'crawler_last_run'=>'2010-01-20 12:00:00', 'is_active'=>1, 'is_public'=>1));
        //insert a non-active instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>3, 'network_user_id'=>12,
        'network_username'=>'jane', 'network'=>'twitter', 'network_viewer_id'=>12,
        'crawler_last_run'=>'2010-01-20 12:00:00', 'is_active'=>0, 'is_public'=>1));

        //insert instance owner
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'Owner 1',
        'email'=>'owner@example.com'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>2));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>3));

        //insert 2 insights for a private instance and 3 for a public instance
        $time_now = date("Y-m-d H:i:s");
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-02', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-02', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-03', 'slug'=>'avg_replies_per_week',
        'instance_id'=>'2', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'another_slug',
        'instance_id'=>'1', 'text'=>'Retweet spike! Your post got retweeted 110 times',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));
        //insight with no text shouldn't be returned
        $builders[] = FixtureBuilder::build('insights', array('date'=>'2012-05-01', 'slug'=>'another_slug',
        'instance_id'=>'1', 'text'=>'',
        'emphasis'=>Insight::EMPHASIS_HIGH, 'time_generated'=>$time_now));

        //assert that page of insights includes from both private and public
        $dao = new InsightMySQLDAO();
        $results = $dao->getAllOwnerInstanceInsights(1, $page_count=10, $page_number=1);
        $this->assertEqual(sizeof($results), 7);
        foreach ($results as $result) {
            $this->assertTrue(isset($result->instance));
        }
    }

    public function testDoesInsightExist() {
        $dao = new InsightMySQLDAO();
        $result = $dao->doesInsightExist("avg_replies_per_week", 1);
        $this->assertTrue($result);
        $result = $dao->doesInsightExist("avg_replies_per_week", 10);
        $this->assertFalse($result);
        $result = $dao->doesInsightExist("yo_yo_yooo", 1);
        $this->assertFalse($result);
    }
}