<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.LocationMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';

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