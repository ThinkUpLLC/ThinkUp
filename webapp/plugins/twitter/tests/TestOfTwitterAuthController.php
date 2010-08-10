<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpAuthController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkUp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Owner.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.OwnerInstance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PluginHook.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Webapp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkUpPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.WebappPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.CrawlerPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTab.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTabDataset.php';

if (!$RUNNING_ALL_TESTS) {
    require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php';
}
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/controller/class.TwitterAuthController.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilder.php';

// Instantiate global database variable
//@TODO remove this when the PDO port is complete
try {
    $db = new Database($THINKUP_CFG);
    $conn = $db->getConnection();
} catch(Exception $e) {
    echo $e->getMessage();
}

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
        $this->assertEqual('You must be logged in to do this', $results);
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
        //$instance_builder = FixtureBuilder::build('instances', array('network_username'=>'dougw'));
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
