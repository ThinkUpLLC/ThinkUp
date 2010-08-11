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
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PluginHook.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Webapp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkUpPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.WebappPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.CrawlerPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTab.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTabDataset.php';

if (!$RUNNING_ALL_TESTS) {
    require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/tests/classes/mock.facebook.php';
}
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/controller/class.FacebookPluginConfigurationController.php';

// Instantiate global database variable
//@TODO remove this when the PDO port is complete
try {
    $db = new Database($THINKUP_CFG);
    $conn = $db->getConnection();
} catch(Exception $e) {
    echo $e->getMessage();
}

/**
 * Test of FacebookPluginConfigurationController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfFacebookPluginConfigurationController extends ThinkUpUnitTestCase {

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('FacebookPluginConfigurationController class test');
    }

    /**
     * Setup
     */
    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');

        //Add owner
        $q = "INSERT INTO tu_owners SET id=1, full_name='ThinkUp J. User', email='me@example.com', is_activated=1,
        pwd='XXX', activation_code='8888'";
        $this->db->exec($q);

        //Add instance_owner
        $q = "INSERT INTO tu_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);

        //Insert test data into test table
        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev',
        'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);

        //Make public
        $q = "INSERT INTO tu_instances (id, network_user_id, network_username, is_public) VALUES (1, 13, 'ev', 1);";
        $this->db->exec($q);

        //Add a bunch of posts
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES ($counter, 13, 'ev', 
            'Ev Williams', 'avatar.jpg', 'This is post $counter', 'web', '2006-01-01 00:$pseudo_minute:00', ".
            rand(0, 4).", 5);";
            $this->db->exec($q);
            $counter++;
        }
    }

    /**
     * Tear down
     */
    public function tearDown(){
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $controller = new FacebookPluginConfigurationController(null);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    /**
     * Test output
     */
    public function testOutputNoParams() {
        //not logged in, no owner set
        $controller = new FacebookPluginConfigurationController(null);
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));

        //logged in
        $_SESSION['user'] = 'me@example.com';
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail($_SESSION['user']);
        $controller = new FacebookPluginConfigurationController($owner);
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('owner_instances'), 'array', 'Owner instances set');
        $this->assertTrue($v_mgr->getTemplateDataItem('fbconnect_link') != '', 'Authorization link set');
    }

    public function testConfigNotSet() {
        //unset FB API keys in Config
        $config = Config::getInstance();
        $config->setValue('facebook_api_key', null);
        $config->setValue('facebook_api_secret', null);
        $api_key = $config->getValue('facebook_api_key');
        $this->assertTrue(!isset($api_key));

        //logged in
        $_SESSION['user'] = 'me@example.com';
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail($_SESSION['user']);
        $controller = new FacebookPluginConfigurationController($owner);
        $v_mgr = $controller->getViewManager();
        //@TODO Figure out why API keys are not set here in the test, but they are in the controller
        //$this->assertEqual($v_mgr->getTemplateDataItem('error'),
        //'Please set your Facebook API key and secret in config.inc.php');
    }
}
