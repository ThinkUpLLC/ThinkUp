<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';

class TestOfUtils extends ThinkTankBasicUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('Utils class test');
    }

    public function setUp() {
    }

    public function tearDown() {
    }

    public function testgetPluginViewDirectory() {
        global $THINKTANK_CFG;
        $path = Utils::getPluginViewDirectory('twitter');
        $this->assertEqual($path, $THINKTANK_CFG['source_root_path'].'webapp/plugins/twitter/view/');

        $path = Utils::getPluginViewDirectory('sweetmaryjane');
        $this->assertEqual($path, $THINKTANK_CFG['source_root_path'].'webapp/plugins/sweetmaryjane/view/');
    }

    public function testGetPercentage(){
        $this->assertEqual(Utils::getPercentage(50, 100), 50);
        $this->assertEqual(Utils::getPercentage(250, 1000), 25);
        $this->assertEqual(Utils::getPercentage('not', 'anumber'), 0);
        $this->assertEqual(Utils::getPercentage(150, 50), 300);
    }
}