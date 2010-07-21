<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilder.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankAuthController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ExportController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkTank.php';
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

class TestOfExportController extends ThinkTankUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('ExportController class test');
    }

    public function testConstructor() {
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/You must be logged in to do this/", $results);
    }

    public function testMissingParams() {
        $_SESSION['user'] = 'me@example.com';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/No user to retrieve./", $results);
    }

    public function testNonExistentUser() {
        $_SESSION['user'] = 'me@example.com';
        $_GET['u'] = 'idontexist';
        $_GET['n'] = 'idontexist';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/User idontexist on idontexist is not in ThinkTank./", $results);
    }

    public function testOwnerWithoutAccess() {
        $builders = $this->buildData();
        $_SESSION['user'] = 'me@example.com';
        $_GET['u'] = 'someuser2';
        $_GET['n'] = 'twitter';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/Insufficient privileges/", $results);
    }

    public function testOwnerWithAccess() {
        $builders = $this->buildData();

        $_SESSION['user'] = 'me@example.com';
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/My first post/", $results);
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com'));
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'someuser1',
        'network'=>'twitter'));
        $instance1_builder = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'someuser2',
        'network'=>'twitter'));
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('instance_id'=>1, 'owner_id'=>1));
        $posts1_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1',
        'post_text'=>'My first post', 'network'=>'twitter'));
        $posts2_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1',
        'post_text'=>'My second post', 'network'=>'twitter'));

        return array($owner_builder, $instance_builder, $instance1_builder, $owner_instance_builder, $posts1_builder,
        $posts2_builder);
    }
}