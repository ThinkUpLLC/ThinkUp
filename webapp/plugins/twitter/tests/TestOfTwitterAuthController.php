<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/controller/class.TwitterAuthController.php';

/**
 * Test of TwitterAuthController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfTwitterAuthController extends ThinkUpUnitTestCase {

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('TwitterAuthController class test');
    }

    /**
     * Setup
     */
    public function setUp(){
        parent::setUp();
    }

    public function testConstructor() {
        $controller = new TwitterAuthController(true);
        $this->assertTrue(isset($controller));
    }
    //Test not logged in
    public function testNotLoggedIn() {
        $controller = new TwitterAuthController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));

    }

    //Test no params
    public function testLoggedInMissingParams() {
        $_SESSION['user'] = 'me@example.com';
        $controller = new TwitterAuthController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual('Secret token not set.', $v_mgr->getTemplateDataItem('infomsg'), "Info msg set");
    }

    //Test Session param but no Get param
    public function testLoggedInMissingToken() {
        $_SESSION['user'] = 'me@example.com';
        $_SESSION['oauth_request_token_secret'] = 'XXX';
        $controller = new TwitterAuthController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual('No OAuth token specified.', $v_mgr->getTemplateDataItem('infomsg'), "Info msg set");
    }

    //Test Session param but no Get param
    public function testLoggedInMissingSessionWithGet() {
        $_SESSION['user'] = 'me@example.com';
        $_GET['oauth_token'] = 'XXX';
        $controller = new TwitterAuthController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual('Secret token not set.', $v_mgr->getTemplateDataItem('infomsg'), "Info msg set");
    }

    public function testLoggedInAllParams() {
        $_SESSION['user'] = 'me@example.com';
        $_GET['oauth_token'] = 'XXX';
        $_SESSION['oauth_request_token_secret'] = 'XXX';

        $owner_builder = FixtureBuilder::build('owners', array('id'=>'10', 'email'=>'me@example.com'));
        $plugn_opt_builder1 = FixtureBuilder::build('plugin_options', array('plugin_id'=>'1',
        'option_name'=>'oauth_consumer_key', 'option_value'=>'XXX'));
        $plugn_opt_builder2 = FixtureBuilder::build('plugin_options', array('plugin_id'=>'1',
        'option_name'=>'oauth_consumer_secret', 'option_value'=>'YYY'));

        $controller = new TwitterAuthController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $results = $v_mgr->getTemplateDataItem('infomsg');
        $this->assertTrue(strpos($results, 'Twitter authentication successful!')>0);
        $this->assertTrue(strpos($results, 'Instance does not exist.')>0);
        $this->assertTrue(strpos($results, 'Created instance.')>0);
    }

}
