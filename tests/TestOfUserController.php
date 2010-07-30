<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilder.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpAuthController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.UserController.php';
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

class TestOfUserController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('UserController class test');
    }

    public function testConstructor() {
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/You must be logged in to do this/", $results);
    }

    public function testMissingParams() {
        $_SESSION['user'] = 'me@example.com';
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/User and network not specified./", $results);
    }

    public function testNonExistentUser() {
        $_SESSION['user'] = 'me@example.com';
        $_GET['u'] = 'idontexist';
        $_GET['n'] = 'idontexist';
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/idontexist is not in the system./", $results);
    }

    public function testExistentUserWithoutInstance() {
        $builders = $this->buildData();

        $_SESSION['user'] = 'me@example.com';
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/someuser1/", $results);

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('user_statuses'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('user_statuses')), 1 );
        $this->assertIsA($v_mgr->getTemplateDataItem('instances'), 'array');
        $this->assertIsA($v_mgr->getTemplateDataItem('profile'), 'User');

        $this->assertEqual($controller->getCacheKeyString(), 'user.index.tpl-me@example.com-someuser1-twitter');
    }

    public function testExistentUserWithInstance() {
        $builders = $this->buildData();

        $_SESSION['user'] = 'me@example.com';
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['i'] = 'instancetestuser';
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/someuser1/", $results);

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'User Details: someuser1');
        $this->assertEqual($v_mgr->getTemplateDataItem('logo_link'), 'index.php');

        $this->assertEqual($controller->getCacheKeyString(),
        'user.index.tpl-me@example.com-someuser1-twitter-instancetestuser');
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com'));
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'someuser1',
        'network'=>'twitter'));
        $instance1_builder = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'someuser2',
        'network'=>'twitter'));
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('instance_id'=>1, 'owner_id'=>1));
        $posts1_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1', 'author_user_id'=>10,
        'post_text'=>'My first post', 'network'=>'twitter'));
        $posts2_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1',
        'post_text'=>'My second post', 'network'=>'twitter'));
        $user1_builder = FixtureBuilder::build('users', array('user_id'=>10, 'user_name'=>'someuser1',
        'network'=>'twitter'));

        return array($owner_builder, $instance_builder, $instance1_builder, $owner_instance_builder, $posts1_builder,
        $posts2_builder, $user1_builder);
    }
}