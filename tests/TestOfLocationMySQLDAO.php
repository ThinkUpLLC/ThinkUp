<?php
/**
 *
 * ThinkUp/tests/TestOfLocationMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Ekansh Preet Singh
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Ekansh Preet Singh
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfLocationMySQLDAO extends ThinkUpUnitTestCase {
    protected $DAO;
    protected $logger;

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->DAO = new LocationMySQLDAO();
        $this->builders = self::buildData();
    }

    protected function buildData() {
        $builders = array();

        //Insert test data into test table
        $builders[] = FixtureBuilder::build('encoded_locations', array('short_name'=>'New Delhi',
        'full_name'=>'New Delhi, Delhi, India', 'latlng'=>'28.635308,77.22496'));

        $builders[] = FixtureBuilder::build('encoded_locations', array('short_name'=>'Chennai',
        'full_name'=>'Chennai, Tamil Nadu, India', 'latlng'=>'13.060416,80.249634'));

        $builders[] = FixtureBuilder::build('encoded_locations', array('short_name'=>'19.017656 72.856178',
        'full_name'=>'Mumbai, Maharashtra, India', 'latlng'=>'19.017656,72.856178'));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        $this->logger->close();
        $this->DAO = null;
    }

    public function testgetLocation() {
        $location = $this->DAO->getLocation('New Delhi');
        $this->assertEqual($location['id'], 1);
        $this->assertEqual($location['short_name'], "New Delhi");
        $this->assertEqual($location['full_name'], "New Delhi, Delhi, India");
        $this->assertEqual($location['latlng'], "28.635308,77.22496");

        $location = $this->DAO->getLocation('19.017656 72.856178');
        $this->assertEqual($location['id'], 3);
        $this->assertEqual($location['short_name'], "19.017656 72.856178");
        $this->assertEqual($location['full_name'], "Mumbai, Maharashtra, India");
        $this->assertEqual($location['latlng'], "19.017656,72.856178");
    }

    public function testaddLocation() {
        $vals['short_name'] = "Bangalore";
        $vals['full_name'] = "Bangalore, Karnataka, India";
        $vals['latlng'] = "10,20";
        $location = $this->DAO->addLocation($vals);
        $location = $this->DAO->getLocation('Bangalore');
        $this->assertEqual($location['id'], 4);
    }
}