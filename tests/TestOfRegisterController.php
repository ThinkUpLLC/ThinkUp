<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankAuthController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.PublicTimelineController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.PrivateDashboardController.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.RegisterController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkTank.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkTankPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.WebappPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.CrawlerPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Owner.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PluginHook.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Webapp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTab.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTabDataset.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilder.php';

require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
if (!$RUNNING_ALL_TESTS) {
    require_once $SOURCE_ROOT_PATH.'extlib/twitteroauth/twitteroauth.php';
}
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkTank.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';

/**
 * Test of RegisterController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfRegisterController extends ThinkTankUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('RegisterController class test');
    }

    public function setUp() {
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');
    }

    public function testConstructor() {
        $controller = new RegisterController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Register | ThinkTank") > 0);
    }

    public function testAlreadyLoggedIn() {
        $session = new Session();
        $cryptpass = $session->pwdcrypt("secretpassword");
        $owner = array('id'=>1, 'email'=>'me@example.com', 'pwd'=>$cryptpass, 'is_activated'=>1);
        $builder1 = FixtureBuilder::build('owners', $owner);
        $instance = array('id'=>1);
        $builder2 = FixtureBuilder::build('instances', $instance);
        $owner_instance = array('owner_id'=>1, 'instance_id'=>1);
        $builder3 = FixtureBuilder::build('owner_instances', $owner_instance);
        $_SESSION['user'] = 'me@example.com';

        $controller = new RegisterController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Private Dashboard | ThinkTank") > 0);
    }

    public function testAllMissingFields() {
        $_POST['Submit'] = 'Register';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Please fill out all required fields.');
    }

    public function testSomeMissingFields() {
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Please fill out all required fields.');
        $this->assertEqual($v_mgr->getTemplateDataItem('name'), 'Angelina Jolie');
        $this->assertEqual($v_mgr->getTemplateDataItem('mail'), 'angie@example.com');
    }

    public function testMismatchedPasswords() {
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mmypass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Passwords do not match.');
        $this->assertEqual($v_mgr->getTemplateDataItem('name'), 'Angelina Jolie');
        $this->assertEqual($v_mgr->getTemplateDataItem('mail'), 'angie@example.com');
    }

    public function testSuccessfulRegistration() {
        $_SERVER['HTTP_HOST'] = "http://mytestthinktank/";
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mypass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('successmsg'),
        'Success! Check your email for an activation link.');
    }
}

/**
 * Mock Captcha for test use
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Captcha {
    public function generate() {
        return '';
    }

    public function check() {
        return true;
    }
}

/**
 * Mock Mailer for test use
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Mailer {
    public static function mail($to, $subject, $message) {
        return $message;
    }
}