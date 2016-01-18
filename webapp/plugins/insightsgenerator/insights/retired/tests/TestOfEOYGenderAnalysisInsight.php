<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfEOYGenderAnalysisInsight.php
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
 * Test of EOYGenderAnalysisInsight
 *
 * Copyright (c) 2014-2016 Anna Shkerina
 *
 * @author Chris Moyer chris@inarow.net
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Chris Moyer
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoygenderanalysis.php';

class TestOfEOYGenderAnalysisInsight extends ThinkUpInsightUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testEOYGenderAnalysisEqual() {
        $builders = self::buildData(12, 12);
        $instance = new Instance();
        $instance->id = 100;
        $instance->network_user_id = '9654321';
        $instance->network_username = 'Cyrus Beene';
        $instance->network = 'facebook';

        $insight_plugin = new EOYGenderAnalysisInsight();
        $insight_plugin->generateInsight($instance, null, array(), 1);

        $insight_dao = new InsightMySQLDAO();
        //sleep(1000);
        $result = $insight_dao->getInsight($insight_plugin->slug, 100, date('Y')."-".$insight_plugin->run_date);
        $gender_data = unserialize($result->related_data);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Cyrus Beene's status updates resonated with women and men equally"
            ." in 2014");
        $this->assertEqual($result->text, "This year, <strong>12 likes and comments</strong> on Cyrus Beene's "
            . "status updates were by people who identify as female, compared to <strong>12</strong> by people "
            . "who identify as male.");
        $this->assertIsA($gender_data, "array");
        $this->assertEqual(count($gender_data), 1);
        $this->assertTrue($gender_data['pie_chart']['male'] == $gender_data['pie_chart']['female']);
        $this->assertEqual($gender_data['pie_chart']['male'], 12);
        $this->assertEqual($gender_data['pie_chart']['female'], 12);

        $this->dumpRenderedInsight($result, $instance, "Normal case, Equal Numbers");
    }

    public function testEOYGenderAnalysisWomen() {
        $builders = self::buildData(99, 1234);
        $instance = new Instance();
        $instance->id = 100;
        $instance->network_user_id = '9654321';
        $instance->network_username = 'Olivia Pope';
        $instance->network = 'facebook';

        $insight_plugin = new EOYGenderAnalysisInsight();
        $insight_plugin->generateInsight($instance, null, array(), 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight($insight_plugin->slug, 100, date('Y')."-".$insight_plugin->run_date);
        $gender_data = unserialize($result->related_data);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Olivia Pope's status updates resonated with women in 2014");
        $this->assertEqual($result->text, "This year, <strong>1,234 likes and comments</strong> on Olivia Pope's "
            . "status updates were by people who identify as female, compared to <strong>99</strong> by people who "
            . "identify as male.");
        $this->assertIsA($gender_data, "array");
        $this->assertEqual(count($gender_data), 1);
        $this->assertTrue($gender_data['pie_chart']['male'] < $gender_data['pie_chart']['female']);
        $this->assertEqual($gender_data['pie_chart']['male'], 99);
        $this->assertEqual($gender_data['pie_chart']['female'], 1234);

        $this->dumpRenderedInsight($result, $instance, "Normal case, More Females");
    }

    public function testEOYGenderAnalysisMen() {
        $builders = self::buildData(23, 12);
        $instance = new Instance();
        $instance->id = 100;
        $instance->network_user_id = '9654321';
        $instance->network_username = 'Fitzgerald Grant';
        $instance->network = 'facebook';

        $insight_plugin = new EOYGenderAnalysisInsight();
        $insight_plugin->generateInsight($instance, null, array(), 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight($insight_plugin->slug, 100, date('Y')."-".$insight_plugin->run_date);
        $gender_data = unserialize($result->related_data);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Fitzgerald Grant's status updates resonated with men in 2014");
        $this->assertEqual($result->text, "This year, <strong>12 likes and comments</strong> on Fitzgerald Grant's "
            . "status updates were by people who identify as female, compared to <strong>23</strong> by people who "
            . "identify as male.");
        $this->assertIsA($gender_data, "array");
        $this->assertEqual(count($gender_data), 1);
        $this->assertTrue($gender_data['pie_chart']['male'] > $gender_data['pie_chart']['female']);
        $this->assertEqual($gender_data['pie_chart']['male'], 23);

        $this->dumpRenderedInsight($result, $instance, "Normal case, More Males");
    }

    public function testEOYGenderAnalysisForFacebookLessThanTenGenderDataBits() {
        $builders = self::buildData(5, 4);
        $instance = new Instance();
        $instance->id = 100;
        $instance->network_user_id = '9654321';
        $instance->network_username = 'Olivia Pope';
        $instance->network = 'facebook';
        $insight_plugin = new EOYGenderAnalysisInsight();
        $insight_plugin->generateInsight($instance, null, array(), 1);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight($insight_plugin->slug, 100, date('Y')."-".$insight_plugin->run_date);
        $this->assertNull($result);
    }

    public function testMonthQualifier() {
        $builders = self::buildData(7, 8, 'June 1');
        $instance = new Instance();
        $instance->id = 100;
        $instance->network_user_id = '9654321';
        $instance->network_username = 'Abby Whelan';
        $instance->network = 'facebook';

        $insight_plugin = new EOYGenderAnalysisInsight();
        $insight_plugin->generateInsight($instance, null, array(), 1);

        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight($insight_plugin->slug, 100, date('Y')."-".$insight_plugin->run_date);
        $gender_data = unserialize($result->related_data);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Abby Whelan's status updates resonated with women in 2014");
        $this->assertEqual($result->text, "This year, <strong>8 likes and comments</strong> on Abby Whelan's status "
            . "updates were by people who identify as female, compared to <strong>7</strong> by people who identify "
            . "as male (at least since June).");
        $this->assertIsA($gender_data, "array");
        $this->assertEqual(count($gender_data), 1);
        $this->assertTrue($gender_data['pie_chart']['male'] < $gender_data['pie_chart']['female']);
        $this->assertEqual($gender_data['pie_chart']['male'], 7);
        $this->assertEqual($gender_data['pie_chart']['female'], 8);

        $this->dumpRenderedInsight($result, $instance, "More Female, Qualified Month");
    }

    private function buildData($male, $female, $pub_date='January 4') {
        $builders = array();

        for ($i=0; $i<$male; $i++) {
            $builders[] = FixtureBuilder::build('users', array('user_id'=>123+$i, 'user_name'=>'Male'.$i,
                'full_name'=>'Male #'.$i, 'gender'=>'male', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
                'network'=>'facebook'));
        }
        for ($i=0; $i<$female; $i++) {
            $builders[] = FixtureBuilder::build('users', array('user_id'=>99923+$i, 'user_name'=>'Female'.$i,
                'full_name'=>'Female #'.$i, 'gender'=>'female', 'avatar'=>'avatar.jpg', 'is_protected'=>0,
                'network'=>'facebook'));
        }

        $builders[] = FixtureBuilder::build('posts', array('id'=>333, 'post_id'=>333,
            'author_user_id'=>'9654321', 'author_username'=>'Abby Whelan', 'author_fullname'=>'Olivia Pope',
            'author_avatar'=>'avatar.jpg', 'network'=>'facebook', 'post_text'=>'This is a simple post.',
            'pub_date'=>date('Y-m-d', strtotime($pub_date)) , 'reply_count_cache'=>3,
            'is_protected'=>0,'favlike_count_cache' => 2));

        $id = 334;
        for ($i=0;$i<$male; $i++) {
            if ($i%2 == 1) {
                $builders[] = FixtureBuilder::build('posts', array('id'=>$id, 'post_id'=>$id++,
                    'author_user_id'=>123+$i,'author_avatar'=>'avatar.jpg', 'network'=>'facebook',
                    'post_text'=>'This is a simple comment.',
                    'pub_date'=>'-12h', 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
                    'in_reply_to_post_id' => '333'));
            } else {
                $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'333', 'author_user_id'=>'9654321',
                    'fav_of_user_id'=>123+$i, 'network'=>'facebook'));
            }
        }
        for ($i=0;$i<$female; $i++) {
            if ($i%2 == 1) {
                $builders[] = FixtureBuilder::build('posts', array('id'=>$id, 'post_id'=>$id++,
                    'author_user_id'=>99923+$i,'author_avatar'=>'avatar.jpg', 'network'=>'facebook',
                    'post_text'=>'This is a simple comment.',
                    'pub_date'=>'-12h', 'reply_count_cache'=>0, 'is_protected'=>0,'favlike_count_cache' =>0,
                    'in_reply_to_post_id' => '333'));
            } else {
                $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'333', 'author_user_id'=>'9654321',
                    'fav_of_user_id'=>99923+$i, 'network'=>'facebook'));
            }
        }
        return $builders;
    }
}
