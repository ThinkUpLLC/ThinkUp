<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/interface.Controller.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankAuthController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.PostController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkTank.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
try {
    $db = new Database($THINKTANK_CFG);
    $conn = $db->getConnection();
} catch(Exception $e) {
    echo $e->getMessage();
}

/**
 * Test of Post Controller
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfPostController extends ThinkTankUnitTestCase {

    /**
     * Constructor
     */
    function __construct() {
        $this->UnitTestCase('PostController class test');
    }

    /**
     * Add test post to database
     */
    function setUp(){
        parent::setUp();
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar, post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES (1001, 13, 'ev', 'Ev Williams', 'avatar.jpg', 'This is a test post', 'web', '2006-01-01 00:05:00', ".rand(0, 4).", 5);";
        $this->db->exec($q);
    }

    /**
     * Test constructor
     */
    function testConstructor() {
        $controller = new PostController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    /**
     * Test controller when user is not logged in
     */
    function testControlNotLoggedIn() {
        $controller = new PostController(true);
        $results = $controller->go();

        $this->assertEqual('You must be logged in to do this', $results);
    }

    /**
     * Test controller when user is logged in, but there's no Post ID on the query string
     */
    function testControlLoggedInNoPostID() {
        $_SESSION['user'] = 'me@example.com';

        $controller = new PostController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "Post not found") > 0, "no post");
    }

    /**
     * Test controller when user is logged in and there is a valid Post ID on the query string
     */
    function testControlLoggedInWithPostID() {
        $_SESSION['user'] = 'me@example.com';
        $_GET["t"] = '1001';

        $controller = new PostController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "This is a test post") > 0, "no post");
    }

    /**
     * Test controller when logged in but there's a numeric but nonexistent Post ID
     */
    function testControlLoggedInWithNumericButNonExistentPostID(){
        $_SESSION['user'] = 'me@example.com';
        $_GET["t"] = '11';

        $controller = new PostController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "Post not found") > 0, "no post");
    }

    /**
     * Test controller when logged in but a non-numeric post ID
     */
    function testControlLoggedInWithNonNumericPostID(){
        $_SESSION['user'] = 'me@example.com';
        $_GET["t"] = 'notapostID45';

        $controller = new PostController(true);
        $results = $controller->go();

        $this->assertTrue(strpos( $results, "Post not found") > 0, "no post");
    }
}