<?php
/**
 *
 * ThinkUp/tests/TestOfDomainMetricsMySQLDAO.php
 *
 * Copyright (c) 2011 SwellPath, Inc.
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
 *
 * @author Christian G. Warden <cwarden[at]xerus[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 SwellPath, Inc.
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfDomainMetricsMySQLDAO extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $dao = new DomainMetricsMySQLDAO();
        $this->assertTrue(isset($dao));
    }

    public function testInsert() {
        $dao = new DomainMetricsMySQLDAO();
        $result = $dao->upsert(5, '2011-01-01', 10, 1);

        $this->assertEqual($result, 1, 'One count inserted');
    }

    public function testUpdate() {
        $dao = new DomainMetricsMySQLDAO();
        $result = $dao->upsert(5, '2011-01-01', 10, 1);

        $result = $dao->upsert(5, '2011-01-01', 20, 2);
        $this->assertEqual($result, 2, 'Two rows affected');

        $sql = "SELECT instance_id, date, widget_like_views, widget_likes FROM " . $this->table_prefix .
        "domain_metrics";
        $stmt = DomainMetricsMySQLDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        $this->assertEqual(sizeof($data), 1);
        $this->assertEqual($data[0]['widget_like_views'], 20);
        $this->assertEqual($data[0]['widget_likes'], 2);
    }

    public function testGetEarliestAndLatest() {
        $dao = new DomainMetricsMySQLDAO();
        $result = $dao->upsert(5, '2011-01-01', 10, 1);
        $result = $dao->upsert(5, '2011-01-02', 20, 2);

        $this->assertEqual($dao->getEarliest(5), strtotime('2011-01-01 00:00:00'));
        $this->assertEqual($dao->getLatest(5), strtotime('2011-01-02 00:00:00'));
    }

    public function testGetWidgetHistory() {
        $instance = FixtureBuilder::build('instances', array('id' => 1, 'network_user_id'=>'123456789',
         'network'=>'facebook domain'));
        $metrics = array('instance_id' => 1, 'date'=>'-2d', 'widget_like_views'=>20, 'widget_likes' => 2);
        $builder1 = FixtureBuilder::build('domain_metrics', $metrics);

        $metrics = array('instance_id' => 1, 'date'=>'-3d', 'widget_like_views'=>10, 'widget_likes' => 3);
        $builder2 = FixtureBuilder::build('domain_metrics', $metrics);
        $dao = new DomainMetricsMySQLDAO();
        $history = $dao->getWidgetHistory('123456789', 'facebook domain', 'DAY', 5);

        // JSON is custom format for Google Charts, containing javascript Date objects
        $this->assertTrue(is_string($history));
        $history_data = json_decode($history);
        $this->assertNull($history_data);

        $this->assertPattern('/"v":new Date\(\d{4},\d{1,2},\d{1,2}\)/', $history);
        $history = preg_replace('/(new Date\(\d{4},\d{1,2},\d{1,2}\))/', '"$1"', $history);
        $history_data = json_decode($history);
        $this->assertTrue(is_object($history_data));

        $two_days_ago = strtotime('2 days ago');
        $three_days_ago = strtotime('3 days ago');

        $this->assertEqual($history_data->rows[0]->c[0]->v, sprintf('new Date(%d,%d,%d)', date('Y', $three_days_ago),
        date('n', $three_days_ago) - 1, date('j', $three_days_ago)));
        $this->assertEqual($history_data->rows[0]->c[0]->f, sprintf('%s', date('m/d/y', $three_days_ago)));
        $this->assertEqual($history_data->rows[0]->c[1]->v, 10);
        $this->assertEqual($history_data->rows[0]->c[2]->v, 3);

        $this->assertEqual($history_data->rows[1]->c[0]->v, sprintf('new Date(%d,%d,%d)', date('Y', $two_days_ago),
        date('n', $two_days_ago) - 1, date('j', $two_days_ago)));
        $this->assertEqual($history_data->rows[1]->c[0]->f, sprintf('%s', date('m/d/y', $two_days_ago)));
        $this->assertEqual($history_data->rows[1]->c[1]->v, 20);
        $this->assertEqual($history_data->rows[1]->c[2]->v, 2);
    }

}

