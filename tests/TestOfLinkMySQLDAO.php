<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.LinkMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';

/**
 * Test Of Link DAO
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author christoffer Viken <christoffer[at]viken[dot]me>
 */
class TestOfLinkMySQLDAO extends ThinkTankUnitTestCase {
    /**
     * Constructor
     */
    function __construct() {
        $this->UnitTestCase('LinkMySQLDAO class test');
    }
    
    /**
     * Constructs a database and populates it.
     */
    function setUp() {
        parent::setUp();
        $this->DAO = new LinkMySQLDAO();

        //Insert test links (not images, not expanded)
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $q  = "INSERT INTO tt_links (url, title, clicks, post_id, is_image) ";
            $q .= " VALUES ('http://example.com/".$counter."', 'Link $counter', 0, $post_id, 0);";
            PDODAO::$PDO->exec($q);
            $counter++;
        }

        //Insert test links (images from Flickr, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $q  = "INSERT INTO tt_links (url, title, clicks, post_id, is_image) ";
            $q .= "VALUES ('http://flic.kr/p/".$counter."', 'Link $counter', 0, $post_id, 1);";
            PDODAO::$PDO->exec($q);
            $counter++;
        }

        //Insert test links with errors (images from Flickr, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $q  = "INSERT INTO tt_links (url, title, clicks, post_id, is_image, error) ";
            $q .= "VALUES ('http://flic.kr/p/".$counter."', 'Link $counter', 0, $post_id, 1, ";
            $q .= "'Generic test error message, Photo not found');";
            PDODAO::$PDO->exec($q);

            $counter++;
        }

        //Insert several of the same shortened link
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $q  = "INSERT INTO tt_links (url, title, clicks, post_id, is_image, error) ";
            $q .= "VALUES ('http://bit.ly/beEEfs', 'Link $counter', 0, $post_id, 1, '');";
            $this->db->exec($q);
            $counter++;
        }

        //Insert several posts
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $user_id = ($counter * 5) + 2;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $q  = "INSERT INTO tt_posts ( ";
            $q .= " post_id, author_user_id, author_username, author_fullname ";
            $q .= " ) ";
            $q .= "VALUES ('$post_id', $user_id, 'user$counter', 'User$counter Name$counter' ";
            $q .= " );";
            $this->db->exec($q);
            $counter++;
        }

        $q  = "INSERT INTO tt_follows (";
        $q .= " follower_id, user_id, active ";
        $q .= " ) ";
        $q .= " VALUES ";
        $q .= " (2, 7, 1), ";
        $q .= " (2, 22, 1), ";
        $q .= " (2, 17, 1), ";
        $q .= " (2, 12, 0), ";
        $q .= " (27, 2, 1), ";
        $q .= " (18, 22, 0), ";
        $q .= " (12, 22, 1) ";
        $this->db->exec($q);
    }

    /**
     * Destructs the database, so it can be reconstructed for next test
     */
    function tearDown() {
        parent::tearDown();
        $this->DAO = null;
    }

    /**
     * Test Of Insert Method
     */
    function testInsert(){
        $result = $this->DAO->insert(
            'http://example.com/test',
            'http://very.long.domain.that.nobody.would.bother.to.type.com/index.php',
            'Very Long URL',
        12345678901
        );
        //Is insert ID returned?
        $this->assertEqual($result, 56);

        //OK now check it
        $result = $this->DAO->getLinkByUrl('http://example.com/test');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://example.com/test');
        $this->assertEqual($result->expanded_url, 'http://very.long.domain.that.nobody.would.bother.to.type.com/index.php');
        $this->assertEqual($result->title, 'Very Long URL');
        $this->assertEqual($result->post_id, 12345678901);
    }
    
    /**
     * Test Of saveExpandedUrl method
     */
    function testSaveExpandedUrl() {
        $linkstoexpand = $this->DAO->getLinksToExpand();

        $link = $linkstoexpand[0];
        $this->DAO->saveExpandedUrl($link, "http://expandedurl.com");

        $updatedlink = $this->DAO->getLinkByUrl($link);
        $this->assertEqual($updatedlink->expanded_url, "http://expandedurl.com");

        $this->DAO->saveExpandedUrl($link, "http://expandedurl1.com", 'my title');
        $updatedlink = $this->DAO->getLinkByUrl($link);
        $this->assertEqual($updatedlink->expanded_url, "http://expandedurl1.com");
        $this->assertEqual($updatedlink->title, "my title");

        $this->DAO->saveExpandedUrl($link, "http://expandedurl2.com", 'my title1', 1);
        $updatedlink = $this->DAO->getLinkByUrl($link);
        $this->assertEqual($updatedlink->expanded_url, "http://expandedurl2.com");
        $this->assertEqual($updatedlink->title, "my title1");
        $this->assertTrue($updatedlink->is_image);
    }

    /**
     * Test Of saveExpansionError Method
     */
    function testSaveExpansionError() {
        $linktogeterror = $this->DAO->getLinkById(10);

        $this->assertEqual($linktogeterror->error, '');
        $this->DAO->saveExpansionError($linktogeterror->url, "This is expansion error text");

        $linkthathaserror = $this->DAO->getLinkById(10);
        $this->assertEqual($linkthathaserror->error, "This is expansion error text");
    }

    /**
     * Test Of update Method
     */
    function testUpdate(){
        $result = $this->DAO->insert(
            'http://example.com/test',
            'http://very.long.domain.that.nobody.would.bother.to.type.com/index.php',
            'Very Long URL',
        15000
        );
        $this->assertEqual($result, 56);

        $result = $this->DAO->update(
            'http://example.com/test', 
            'http://very.long.domain.that.nobody.would.bother.to.type.com/image.png', 
            'Even Longer URL', 
        15001,
        true
        );
        $this->assertEqual($result, 1);

        //OK now check it
        $result = $this->DAO->getLinkByUrl('http://example.com/test');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://example.com/test');
        $this->assertEqual($result->expanded_url, 'http://very.long.domain.that.nobody.would.bother.to.type.com/image.png');
        $this->assertEqual($result->title, 'Even Longer URL');
        $this->assertEqual($result->post_id, 15001);
        $this->assertEqual($result->id, 56);
    }

    /**
     * Test Of getLinksByFriends Method
     */
    function testGetLinksByFriends(){
        $result = $this->DAO->getLinksByFriends(2);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 12);
        $posts = array(
            80=>array('pid'=>80, 'uid'=>2, 'fr'=>true),
            81=>array('pid'=>81, 'uid'=>7, 'fr'=>true),
            82=>array('pid'=>82, 'uid'=>12, 'fr'=>false),
            83=>array('pid'=>83, 'uid'=>17, 'fr'=>true),
            84=>array('pid'=>84, 'uid'=>22, 'fr'=>true)
        );
        foreach($result as $key=>$val){
            $this->assertIsA($val, "link");
            $this->assertIsA($val->container_post, "Post");
            $num = $val->post_id;
            $pid = $posts[$num]['pid'];
            $uid = $posts[$num]['uid'];
            $this->assertEqual($val->container_post->post_id, $pid);
            $this->assertEqual($val->container_post->author_user_id, $uid);
            $this->assertTrue($posts[$num]['fr']);
        }
    }
    
    /**
     * Test Of getPhotosByFriends Method
     */
    function testGetPhotosByFriends(){
        $result = $this->DAO->getPhotosByFriends(2);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 9);
        $posts = array(
            80=>array('pid'=>80, 'uid'=>2, 'fr'=>true),
            81=>array('pid'=>81, 'uid'=>7, 'fr'=>true),
            82=>array('pid'=>82, 'uid'=>12, 'fr'=>false),
            83=>array('pid'=>83, 'uid'=>17, 'fr'=>true),
            84=>array('pid'=>84, 'uid'=>22, 'fr'=>true)
        );
        foreach($result as $key=>$val){
            $this->assertIsA($val, "link");
            $this->assertIsA($val->container_post, "Post");
            $this->assertTrue($val->is_image);
            $num = $val->post_id;
            $pid = $posts[$num]['pid'];
            $uid = $posts[$num]['uid'];
            $this->assertEqual($val->container_post->post_id, $pid);
            $this->assertEqual($val->container_post->author_user_id, $uid);
            $this->assertTrue($posts[$num]['fr']);
        }
    }

    /**
     * Test Of getLinksToExpand Method
     */
    function testGetLinksToExpand() {
        $linkstoexpand = $this->DAO->getLinksToExpand();
        $this->assertEqual(count($linkstoexpand), 46);
        $this->assertIsA($linkstoexpand, "array");
    }

    /**
     * Test Of getLinkByID
     */
    function testGetLinkById() {
        $link = $this->DAO->getLinkById(1);

        $this->assertEqual($link->id, 1);
        $this->assertEqual($link->url, 'http://example.com/0');
    }

    /**
     * Test Of getLinksToExpandByURL Method
     */
    function testGetLinksToExpandByURL() {
        $flickrlinkstoexpand = $this->DAO->getLinksToExpandByUrl('http://flic.kr/');

        $this->assertEqual(count($flickrlinkstoexpand), 5);
        $this->assertIsA($flickrlinkstoexpand, "array");
    }

}