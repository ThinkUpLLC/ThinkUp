<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/interface.Controller.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.PublicTimelineController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkTank.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';

class TestOfPublicTimelineController extends ThinkTankUnitTestCase {

    function __construct() {
        $this->UnitTestCase('PublicTimelineController class test');
    }

    function setUp(){
        parent::setUp();

        //Add instance_owner
        $q = "INSERT INTO tt_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);

        //Insert test data into test table
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev', 'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);

        //Make public
        $q = "INSERT INTO tt_instances (id, network_user_id, network_username, is_public) VALUES (1, 13, 'ev', 1);";
        $this->db->exec($q);

        //Add a bunch of posts
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar, post_text, source, pub_date, mention_count_cache, retweet_count_cache) VALUES ($counter, 13, 'ev', 'Ev Williams', 'avatar.jpg', 'This is post $counter', 'web', '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5);";
            $this->db->exec($q);
            $counter++;
        }

    }

    function tearDown(){
        parent::tearDown();
        $_REQUEST["page"] =  null;
        $_REQUEST["v"] = null;
    }

    function testConstructor() {
        $controller = new PublicTimelineController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    function testControlNoParams() {
        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        $this->assertTrue(strpos( $results, "Latest public posts and public replies") > 0, "default timeline");
    }

    function testControlPage2DefaultList() {
        $_REQUEST["page"] = '2';

        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        $this->assertTrue(strpos( $results, "Latest public posts and public replies") > 0, "default timeline, page 2");
        $this->assertTrue(strpos( $results, "Page 2") > 0, "default timeline, page 2");
    }

    function testControlMostReplies() {
        $_REQUEST["v"] = 'mostreplies';

        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        $this->assertTrue(strpos( $results, "Posts that have been replied to most often") > 0, "most replies list");
    }


}
