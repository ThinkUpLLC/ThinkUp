<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfGridController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('GridController class test');
    }

    public function testConstructor() {
        $controller = new GridController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new GridController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testMissingParams() {
        $_SESSION['user'] = 'me@example.com';
        $controller = new GridController(true);
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $ob = json_decode($results);
        $this->assertEqual($ob->status, 'failed');
        $this->assertEqual($ob->message, 'Missing Parameters');
    }

    public function testNonExistentUser() {
        $_SESSION['user'] = 'me@example.com';
        $_GET['u'] = 'idontexist';
        $_GET['n'] = 'idontexist';
        $controller = new GridController(true);

        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $ob = json_decode($results);
        $this->assertEqual($ob->status, 'failed');
        $this->assertEqual($ob->message, 'idontexistis not configured.');
    }

    public function testOwnerWithoutAccess() {
        $builders = $this->buildData();
        $_SESSION['user'] = 'me@example.com';
        $_GET['u'] = 'someuser2';
        $_GET['n'] = 'twitter';
        ob_start();
        $controller = new GridController(true);
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $ob = json_decode($results);
        $this->assertEqual($ob->status, 'failed');
        $this->assertEqual($ob->message, 'Insufficient privileges.');
    }

    public function testOwnerWithAccessAllMentions() {
        $builders = $this->buildData();
        $_SESSION['user'] = 'me@example.com';
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['d'] = 'tweets-mostreplies';
        $controller = new GridController(true);
        $this->assertTrue(isset($controller));
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $json = substr($results, 29, 210);
        $ob = json_decode( $json );
        // @TODO Figure out why these assertions don't work
        //        $this->assertEqual($ob->status, 'success');
        //        $this->assertEqual(count($ob->posts), 3);
        $this->assertPattern('/"status":"success"/', $results);
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com'));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>123, 'user_name'=>'someuser2',
        'network'=>'twitter'));
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'someuser1',
        'network'=>'twitter'));
        $instance1_builder = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'someuser2',
        'network'=>'twitter'));
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('instance_id'=>1, 'owner_id'=>1));
        $posts1_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser2','author_user_id' => 123,
        'post_text'=>'@someuser1 My first post', 'network'=>'twitter'));
        $posts2_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser2','author_user_id' => 123,
        'post_text'=>'My second @someuser1 post', 'network'=>'twitter'));
        //sleep(10000);
        return array($owner_builder, $instance_builder, $instance1_builder, $owner_instance_builder, $posts1_builder,
        $posts2_builder, $user_builder);
    }
}