<?php
/**
 *
 * ThinkUp/tests/TestOfStreamDataMySQLDAO.php
 *
 * Copyright (c) 2011-2013 Amy Unruh
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
 * Test of StreamData DAO
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Amy Unruh
 * @author Amy Unruh
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfStreamDataMySQLDAO extends ThinkUpUnitTestCase {
    /**
     *
     * @var StreamDataMySQLDAO
     */
    protected $dao;
    /**
     * @var Logger
     */
    protected $logger;

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
        $this->logger = Logger::getInstance();
        $this->dao = new StreamDataMySQLDAO();
    }

    protected function buildData() {
        for ($i = 0; $i < 3; $i++) {
            $content = "This is content string # $i";
            $builders[] = FixtureBuilder::build('stream_data', array('data'=>$content, 'network'=>'twitter'));
        }
        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        $this->logger->close();
    }

    public function testCreateNewStreamDataDAO() {
        $dao = DAOFactory::getDAO('StreamDataDAO');
        $this->assertTrue(isset($dao));
        $dao = new StreamDataMySQLDAO();
        $this->assertTrue(isset($dao));
    }

    public function testInsertStreamData() {
        $res = $this->dao->retrieveNextItem();
        $res = $this->dao->retrieveNextItem();
        $this->dao->insertStreamData('new content');
        $res = $this->dao->retrieveNextItem();
        $this->assertEqual('This is content string # 2', $res[1]);
        $res = $this->dao->retrieveNextItem();
        $this->assertEqual('new content', $res[1]);
    }

    public function testRetrieveNextItem() {
        $res = $this->dao->retrieveNextItem();
        $this->assertEqual(1, $res[0]);
        $this->assertEqual('This is content string # 0', $res[1]);
        $res = $this->dao->retrieveNextItem();
        $this->assertEqual(2, $res[0]);
        $this->assertEqual('This is content string # 1', $res[1]);
        $res = $this->dao->retrieveNextItem();
        $res = $this->dao->retrieveNextItem();
        $this->assertEqual(null, $res[0]); $this->assertEqual(null, $res[1]);
    }

    public function testResetID() {
        $res = $this->dao->retrieveNextItem();
        $res = $this->dao->retrieveNextItem();
        $res = $this->dao->retrieveNextItem(); // now empty
        $this->dao->insertStreamData('new content');
        $res = $this->dao->retrieveNextItem();
        $this->assertEqual(4, $res[0]);
        $this->dao->resetID(); //reset when empty
        $this->dao->insertStreamData('new content 2');
        $res = $this->dao->retrieveNextItem();
        $this->assertEqual(1, $res[0]);
        $this->dao->insertStreamData('new content 3');
        $this->dao->resetID(); // reset when not empty
        $res = $this->dao->retrieveNextItem();
        $this->assertEqual(2, $res[0]);
    }
}
