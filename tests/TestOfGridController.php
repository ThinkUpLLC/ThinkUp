<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfGridController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('GridController class test');
    }

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');
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
        $this->simulateLogin('me@example.com');
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
        $this->simulateLogin('me@example.com');
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
        $this->simulateLogin('me@example.com');
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

    public function testOwnerWithAccessTweetsAll() {
        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['d'] = 'tweets-all';
        $controller = new GridController(true);
        $this->assertTrue(isset($controller));
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $json = substr($results, 29, 262);
        $ob = json_decode( $json );
        $this->assertEqual($ob->status, 'success');
        $this->assertEqual(count($ob->posts), 3);
        $this->assertPattern('/"status":"success"/', $results);
    }

    public function testNoProfilerOutput() {
        // Enable profiler
        $config = Config::getInstance();
        $config->setValue('enable_profiler', true);
        $_SERVER['HTTP_HOST'] = 'something';

        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['d'] = 'tweets-all';
        $controller = new GridController(true);
        $this->assertTrue(isset($controller));

        ob_start();
        $results = $controller->go();
        $results .= ob_get_contents();
        ob_end_clean();
        $json = substr($results, 29, 262);
        $ob = json_decode($json);
        // If the profiler outputs HTML (it shouldn't), the following will fail
        $this->assertIsA($ob, 'stdClass');
        unset($_SERVER['HTTP_HOST']);
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com'));
        
        $user_builder = FixtureBuilder::build('users', array('user_id'=>123, 'user_name'=>'someuser1',
        'network'=>'twitter'));
        
        $user_builder2 = FixtureBuilder::build('users', array('user_id'=>1234, 'user_name'=>'someuser2',
        'network'=>'twitter'));
        
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'someuser1',
        'network'=>'twitter', 'network_user_id' => 123));
        
        $instance_builder2 = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'someuser2',
        'network'=>'twitter'));
        
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('instance_id'=>1, 'owner_id'=>1));
        
        $posts1_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1','author_user_id' => 123,
        'post_text'=>'@someuser1 My first post', 'network'=>'twitter', 'post_id' => 1));
        $posts2_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1','author_user_id' => 123,
        'post_text'=>'My second @someuser1 post', 'network'=>'twitter', 'post_id' => 2));
        //sleep(10000);
        return array($owner_builder, $instance_builder, $owner_instance_builder, $posts1_builder,
        $posts2_builder, $user_builder, $user_builder2, $instance_builder2);
    }
}