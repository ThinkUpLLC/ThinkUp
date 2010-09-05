<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfExportController extends ThinkUpUnitTestCase {

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

        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
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
        $this->assertPattern("/User idontexist on idontexist is not in ThinkUp./", $results);
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

        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $this->assertPattern("/My first post/", $results);
    }

    public function testExplicitPostsExport() {
        $builders = $this->buildData();

        $_SESSION['user'] = 'me@example.com';
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['type'] = 'posts';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $this->assertPattern("/My first post/", $results);
    }

    public function testRepliesExport() {
        $builders = $this->buildData();

        $_SESSION['user'] = 'me@example.com';
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['type'] = 'replies';
        $_GET['post_id'] = '1';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $this->assertPattern("/Reply to first post/", $results);
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com'));
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'someuser1',
        'network'=>'twitter'));
        $instance1_builder = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'someuser2',
        'network'=>'twitter'));
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('instance_id'=>1, 'owner_id'=>1));
        $posts1_builder = FixtureBuilder::build('posts', array('post_id' => '1', 'author_username'=>'someuser1',
        'post_text'=>'My first post', 'network'=>'twitter'));
        $posts2_builder = FixtureBuilder::build('posts', array('post_id' => '2', 'author_username'=>'someuser1',
        'post_text'=>'My second post', 'network'=>'twitter'));
        $reply_builder = FixtureBuilder::build('posts', array('post_id' => '3', 'author_username'=>'someuser2',
        'post_text'=>'Reply to first post', 'network'=>'twitter', 'in_reply_to_post_id' => '1'));

        return array($owner_builder, $instance_builder, $instance1_builder, $owner_instance_builder, $posts1_builder,
        $posts2_builder, $reply_builder);
    }
}
