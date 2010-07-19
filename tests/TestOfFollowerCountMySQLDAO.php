<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.FollowerCountMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilder.php';

class TestOfFollowerCountMySQLDAO extends ThinkTankUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('FollowerCountMySQLDAO class test');
    }

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $dao = new FollowerCountMySQLDAO();
        $this->assertTrue(isset($dao));
    }

    public function testInsert() {
        $dao = new FollowerCountMySQLDAO();
        $result = $dao->insert(930061, 'twitter', 1001);

        $this->assertEqual($result, 1, 'One count inserted');
    }

    public function testGetHistory() {
        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-1d', 'count'=>140);
        $builder1 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-2d', 'count'=>100);
        $builder2 = FixtureBuilder::build('follower_count', $follower_count);

        $follower_count = array('network_user_id'=>930061, 'network'=>'twitter', 'date'=>'-3d', 'count'=>120);
        $builder3 = FixtureBuilder::build('follower_count', $follower_count);

        $dao = new FollowerCountMySQLDAO();
        $result = $dao->getHistory(930061, 'twitter', 'DAY');
        $this->assertEqual(sizeof($result), 3, '3 sets of data returned--history, percentages, Y axis');
        $this->assertEqual(sizeof($result['history']), 3, '3 counts returned');
        $this->assertEqual(sizeof($result['percentages']), 3, '3 percentages returned');
        $this->assertEqual(sizeof($result['y_axis']), 4, '4 Y axis points returned');
        $this->assertEqual($result['y_axis'][0], 100);
        $this->assertEqual($result['y_axis'][1], 110);
        $this->assertEqual($result['y_axis'][2], 120);
        $this->assertEqual($result['y_axis'][3], 140);
    }
}