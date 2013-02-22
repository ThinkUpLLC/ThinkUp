<?php
/**
 *
 * ThinkUp/tests/TestOfStreamProcMySQLDAO.php
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
 * Test of StreamProcDAO
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Amy Unruh
 * @author Amy Unruh
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfStreamProcMySQLDAO extends ThinkUpUnitTestCase {
    /**
     *
     * @var StreamProcMySQLDAO
     */
    protected $dao;
    /**
     * @var Logger
     */
    protected $logger;

    public function setUp() {
        parent::setUp();
        $this->dao = new StreamProcMySQLDAO();
        $this->builders = self::buildData();
        $this->logger = Logger::getInstance();
    }

    protected function buildData() {
        //Insert test data into test table
        for ($i = 1; $i < 4; $i++) {
            $pid = 4000 + $i;
            $email = "bob$i@example.com";
            $builders[] = FixtureBuilder::build('stream_procs', array('process_id'=>$pid, 'email'=>$email,
            'instance_id'=>$i, 'last_report'=>null)); // these will all have current timestamp
        }
        // now build one with an older date that will register as 'inactive'.
        $last_report = '2011-02-18 00:15:00';
        $builders[] = FixtureBuilder::build('stream_procs', array('process_id'=>3000, 'email'=>'ted@example.com',
        'instance_id'=>4, 'last_report'=>$last_report));
        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        $this->logger->close();
    }

    public function testCreateNewStreamProcDAO() {
        $dao = DAOFactory::getDAO('StreamProcDAO');
        $this->assertTrue(isset($dao));
        $dao = new StreamProcMySQLDAO();
        $this->assertTrue(isset($dao));
    }

    public function testInsertProcessInfo() {
        $pid = 4444;
        $email = 'emailaddress';
        $inst_id = 3;
        $this->dao-> insertProcessInfo($pid, $email, $inst_id);
        $res = $this->dao->getProcessInfo($pid);
        $this->assertEqual($res['email'], 'emailaddress');
        // now try inserting the same owner information again
        try {
            $this->dao-> insertProcessInfo($pid, $email, $inst_id);
            $this->fail("should throw PDOException");
        } catch(PDOException $e) {
            $this->assertPattern('/Integrity constraint violation/', $e->getMessage());
        }
    }

    public function testGetProcInfoForPID() {
        $res = $this->dao->getProcessInfo(4002);
        $this->assertEqual($res['process_id'], 4002);
        $this->assertEqual($res['email'], 'bob2@example.com');
    }

    public function testGetProcInfoForOwner() {
        $res = $this->dao->getProcessInfoForOwner('bob2@example.com', 4);
        $this->assertEqual($res, null);
        $res = $this->dao->getProcessInfoForOwner('bob2@example.com', 2);
        $this->assertEqual($res['process_id'], 4002);
        $this->assertEqual($res['email'], 'bob2@example.com');
    }

    public function testGetProcInfoByInstance() {
        $res = $this->dao->getProcessInfoForInstance(5);
        $this->assertEqual($res, null);
        $res = $this->dao->getProcessInfoForInstance(2);
        $this->assertEqual($res['process_id'], 4002);
        $this->assertEqual($res['email'], 'bob2@example.com');
    }

    public function testReportPIDActive() {
        $gap = 10;
        $res = $this->dao->getProcessInfo(3000);
        $this->assertTrue(strtotime($res['last_report']) < (time() - $gap));
        $this->dao->reportProcessActive(3000);
        $res = $this->dao->getProcessInfo(3000);
        $this->assertFalse(strtotime($res['last_report']) < (time() - $gap));
    }

    public function testReportStreamProcessActive() {
        $gap = 10;
        $res = $this->dao->getProcessInfo(3000);
        $this->assertTrue(strtotime($res['last_report']) < (time() - $gap));
        $this->dao->reportOwnerProcessActive('ted@example.com', 4);
        $res = $this->dao->getProcessInfo(3000);
        $this->assertFalse(strtotime($res['last_report']) < (time() - $gap));
    }

    public function testGetAllStreamPIDs() {
        $res = $this->dao->getAllStreamProcessIDs();
        $this->assertEqual(sizeof($res), 4);
        $this->assertEqual($res[0]['process_id'], 3000);
        $this->assertEqual($res[1]['process_id'], 4001);
        $this->assertEqual($res[2]['process_id'], 4002);
        $this->assertEqual($res[3]['process_id'], 4003);
    }

    public function testGetAllStreamProcessesIndexed() {
        $proc_info = $this->dao->getAllStreamProcesses();
        $this->assertEqual(sizeof($proc_info), 4);
        $this->assertTrue(isset($proc_info['bob3@example.com_3']));
        $this->assertEqual($proc_info['bob3@example.com_3']['process_id'], 4003);
    }

    public function testDeletePID() {
        $this->dao->deleteProcess(4001);
        try {
            $res = $this->dao->getProcessInfo(4001);
            $this->fail("should throw StreamingException");
        } catch (StreamingException $e) {
        }
    }
}
