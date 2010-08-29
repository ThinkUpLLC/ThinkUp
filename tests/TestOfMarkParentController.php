<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfMarkParentController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('MarkParentController class test');
    }

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new MarkParentController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new MarkParentController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testMissingParams() {
        $_SESSION['user'] = 'me@example.com';

        $controller = new MarkParentController(true);
        $results = $controller->go();
        $this->assertEqual($results, 'Missing required parameters.', $results);
    }

    public function testSuccessfulAssignment() {
        $_SESSION['user'] = 'me@example.com';

        $builders = $this->buildPosts();

        $post_dao = DAOFactory::getDAO('PostDAO');
        $post = $post_dao->getPost(1, 'twitter');
        $this->assertEqual($post->in_reply_to_post_id, 0);

        $_GET["t"] = 'post.index.tpl';
        $_GET["ck"] = 'cachekey';
        $_GET["pid"] = 11;
        $_GET["oid"] = array(1);
        $_GET['n'] = 'twitter';

        $controller = new MarkParentController(true);
        $results = $controller->go();
        $this->assertPattern('/Assignment successful./', $results);

        $post = $post_dao->getPost(1, 'twitter');
        $this->assertEqual($post->in_reply_to_post_id, 11);

        // On second try, nothing changes
        $results = $controller->go();
        $this->assertPattern('/No data was changed./', $results);
    }

    private function buildPosts() {
        $parent_builder = FixtureBuilder::build('posts',
        array("post_id"=>1, 'network'=>'twitter', 'in_reply_to_post_id'=>0));
        $orphan_builder = FixtureBuilder::build('posts',
        array("post_id"=>11, 'network'=>'twitter', 'in_reply_to_post_id'=>0));
        return array($parent_builder, $orphan_builder);
    }
}