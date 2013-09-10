<?php
/**
 *
 * ThinkUp/tests/TestOfTableStatsMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie
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
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfTableStatsMySQLDAO extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
        $optiondao = new OptionMySQLDAO();
        $this->pdo = $optiondao->connect();
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testDAOFactory() {
        $this->assertIsA(DAOFactory::getDAO('TableStatsDAO'), 'TableStatsMySQLDAO');
    }

    public function testGetCounts() {
        $table_stats_daa =  new TableStatsMySQLDAO();
        // no counts...
        $counts = $table_stats_daa->getTableRowCounts();
        foreach($counts as $table) {
            if ($table['table'] == $this->table_prefix . 'options') {
                $this->assertEqual($table['count'], 1);
            } else if ($table['table'] == $this->table_prefix . 'plugins') {
                $this->assertEqual($table['count'], 6);
            } else {
                $this->assertEqual($table['count'], 0);
            }
        }

        // are we sorted by count desc?
        $this->assertEqual(6,$counts[0]['count']);
        $this->assertEqual(1,$counts[1]['count']);
        $this->assertEqual(0,$counts[2]['count']);
    }
}