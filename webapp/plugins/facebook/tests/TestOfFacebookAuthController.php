<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/init.tests.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/controller/class.FacebookAuthController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/tests/classes/mock.facebook.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/facebook/facebook.php';

/**
 * Test of FacebookAuthController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfFacebookAuthController extends ThinkUpUnitTestCase {

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('FacebookAuthController class test');
    }

    /**
     * Setup
     */
    public function setUp(){
        parent::setUp();
    }

    public function testConstructor() {
        $controller = new FacebookAuthController(true);
        $this->assertTrue(isset($controller));
    }
    //Test not logged in
    public function testNotLoggedIn() {
        $controller = new FacebookAuthController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testLoggedInMissingParam() {
        $_SESSION['user'] = 'me@example.com';
        $option_builders = $this->buildPluginOptions();
        $controller = new FacebookAuthController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual('No session key specified.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testLoggedInWithAllParams() {
        $_SESSION['user'] = 'me@example.com';
        $_GET["sessionKey"] = "1234";
        $option_builders = $this->buildPluginOptions();
        $controller = new FacebookAuthController(true);
        $results = $controller->go();

        //API keys set below are still invalid, so:
        $this->assertPattern('/Invalid API key/', $results);
    }

    /**
     * build plugin option values
     */
    private function buildPluginOptions() {
        $plugin1 = FixtureBuilder::build('plugins', array('id'=>2, 'folder_name'=>'facebook'));
        $plugin_opt1 = FixtureBuilder::build('plugin_options',
        array('plugin_id' => 2, 'option_name' => 'facebook_api_key', 'option_value' => "dummy_key") );
        $plugin_opt2 = FixtureBuilder::build('plugin_options',
        array('plugin_id' => 2, 'option_name' => 'facebook_api_secret', 'option_value' => "dummy_secret") );
        return array($plugin_opt1, $plugin_opt2, $plugin1);
    }

}