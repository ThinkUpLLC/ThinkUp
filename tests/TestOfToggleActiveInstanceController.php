<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilder.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpAuthController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpAdminController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkUp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Owner.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.OwnerInstance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PluginHook.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Webapp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ToggleActiveInstanceController.php';

class TestOfToggleActiveInstanceController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('ToggleActiveInstanceController class test');
    }

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new ToggleActiveInstanceController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testNotAnAdmin() {
        $_SESSION['user'] = 'me@example.com';
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must be a ThinkUp admin to do this', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testMissingInstanceParam() {
        $_SESSION['user'] = 'me@example.com';
        $_SESSION['user_is_admin'] = true;
        $_GET['p'] = 1;
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 'Missing required parameters.', $results);
    }

    public function testMissingActiveParam() {
        $_SESSION['user'] = 'me@example.com';
        $_SESSION['user_is_admin'] = true;
        $_GET['u'] = 'ginatrapani';
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 'Missing required parameters.', $results);
    }

    public function testBothParamsNonExistentInstance() {
        $_SESSION['user'] = 'me@example.com';
        $_SESSION['user_is_admin'] = true;
        $_GET['u'] = 1;
        $_GET['p'] = 1;
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 0, $results);
    }

    public function testBothParamsExistentInstance() {
        $builder = FixtureBuilder::build('instances', array('id'=>12, 'is_active'=>1));
        $_SESSION['user'] = 'me@example.com';
        $_SESSION['user_is_admin'] = true;
        $_GET['u'] = '12';
        $_GET['p'] = '0';
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 1);
    }
}