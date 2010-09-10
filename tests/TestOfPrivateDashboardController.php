<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';

/**
 * Test of PrivateDashboardController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfPrivateDashboardController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('PrivateDashboardController class test');
    }

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');

        //Add owner
        $q = "INSERT INTO tu_owners SET id=1, full_name='ThinkUp J. User', email='me@example.com', is_activated=1,
        pwd='XXX', activation_code='8888'";
        $this->db->exec($q);
    }

    public function tearDown(){
        parent::tearDown();
    }

    private function setUpValidOwnerInstanceWithPosts() {
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
            'Ev Williams', 'avatar.jpg', 'This is post $counter', 'web', 
            '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5);";
            $this->db->exec($q);
            $counter++;
        }
    }
    public function testConstructor() {
        $controller = new PrivateDashboardController(true);
        $this->assertTrue(isset($controller), 'constructor test');

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Private Dashboard');
    }

    public function testControlNotLoggedIn() {
        $this->setUpValidOwnerInstanceWithPosts();
        $controller = new PrivateDashboardController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "Latest public posts and public replies") > 0,
        "not logged in; render public timeline instead");
    }

    public function testControlLoggedInWithData() {
        $this->simulateLogin('me@example.com');
        $this->setUpValidOwnerInstanceWithPosts();
        $controller = new PrivateDashboardController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "It is nice to be nice") > 0, "logged in dashboard");

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Private Dashboard');

        $this->assertEqual($controller->getCacheKeyString(), 'index.tpl-me@example.com-ev-twitter', 'Cache key');
    }

    public function testControlLoggedInWithOutInstance() {
        $this->simulateLogin('me@example.com');
        $controller = new PrivateDashboardController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "It is nice to be nice") > 0, "logged in dashboard");

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Private Dashboard');

        $this->assertEqual($controller->getCacheKeyString(), 'index.tpl-me@example.com', 'Cache key');
    }

}
