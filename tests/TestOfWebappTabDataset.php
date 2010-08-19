<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of WebappTabDataset
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfWebappTabDataset extends ThinkUpUnitTestCase {

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('WebappTabDataset class test');
    }

    /**
     * Set up test
     */
    public function setUp() {
        parent::setUp();
    }

    /**
     * Tear down test
     */
    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test constructor with allowed DAO name
     */
    public function testConstructorAllowedDAO() {
        $dataset = new WebappTabDataset('all-posts', 'PostDAO', 'getAllPosts');
        $this->assertTrue(isset($dataset));
        $this->assertEqual($dataset->dao_name, 'PostDAO');
        $this->assertEqual($dataset->dao_method_name, 'getAllPosts');
        $this->assertIsA($dataset->method_params, 'array');
    }

    /**
     * Test constructor with disallowed DAO name
     */
    public function testConstructorDisallowedDAO() {
        $this->expectException(new Exception('BadDAO is not one of the allowed DAOs'));
        $dataset = new WebappTabDataset('all-posts', 'BadDAO', 'getAllPosts');
    }

    /**
     * Test retrieveData with an existing method
     */
    public function testRetrieveDataMethodExists() {
        $dataset = new WebappTabDataset('all-posts', 'PostDAO', 'getAllPosts', array(930061, 'twitter', 15));
        $data = $dataset->retrieveDataset();
        $this->assertTrue(isset($data));
        $this->assertIsA($data, 'array');
    }

    /**
     * Test retrieveData with an existing method
     */
    public function testRetrieveDataMethodDoesNotExist() {
        $dataset = new WebappTabDataset('all-posts', 'PostDAO', 'getAllPostsIDontExist', array(930061, 'twitter', 15));
        $this->expectException(new Exception('PostDAO does not have a getAllPostsIDontExist method.'));
        $data = $dataset->retrieveDataset();
    }
}