<?php
/**
 *
 * ThinkUp/tests/TestOfLocationMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Dwi Widiastuti, Gina Trapani, ekansh
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author ekansh <ekanshpreet[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Dwi Widiastuti, Gina Trapani, ekansh
*/
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfLocationMySQLDAO extends ThinkUpUnitTestCase {
    protected $DAO;
    protected $logger;
    public function __construct() {
        $this->UnitTestCase('LocationMySQLDAO class test');
    }

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->DAO = new LocationMySQLDAO();

        //Insert test data into test table
        $q = "INSERT INTO tu_encoded_locations (short_name, full_name, latlng)
        VALUES ('New Delhi', 'New Delhi, Delhi, India', '28.635308,77.22496');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_encoded_locations (short_name, full_name, latlng)
        VALUES ('Chennai', 'Chennai, Tamil Nadu, India', '13.060416,80.249634');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_encoded_locations (short_name, full_name, latlng)
        VALUES ('19.017656 72.856178', 'Mumbai, Maharashtra, India', '19.017656,72.856178');";
        PDODAO::$PDO->exec($q);
    }

    public function tearDown() {
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