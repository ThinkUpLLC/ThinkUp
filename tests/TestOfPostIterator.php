<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfPostIterator extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'posts';
    const TEST_TABLE_LINKS = 'links';

    public function __construct() {
        $this->UnitTestCase('TestOfPostIterator class test');
    }

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
        $this->prefix = $this->config->getValue('table_prefix');
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testNullStmt() {
        $post_it = new PostIterator(null);
        $cnt = 0;
        foreach($post_it as $key => $value) {
            $cnt++;
        }
        $this->assertEqual(0, $cnt, 'count should be zero');
    }

    public function testStmtNoResults() {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $sql = "select * from " . $this->prefix . self::TEST_TABLE;
        $stmt = PostMysqlDAO::$PDO->query($sql);
        $post_it = new PostIterator($stmt);
        $cnt = 0;
        foreach($post_it as $key => $value) {
            $cnt++;
        }
        $this->assertEqual(0, $cnt, 'count should be zero');
    }

    public function testValidResults() {
        // one result
        $builders = $this->buildOptions(1);
        $post_dao = DAOFactory::getDAO('PostDAO');
        $post_it = $post_dao->getAllPostsByUsernameIterator('mojojojo', 'twitter');
        $posts = $post_dao->getAllPostsByUsername('mojojojo', 'twitter');
        $cnt = 0;
        foreach($post_it as $key => $value) {
            $this->assertEqual($value->post_text, $posts[$cnt]->post_text);
            $this->assertIsA($value, 'Post');
            $cnt++;
        }
        $this->assertEqual(1, $cnt);

        // 10 results
        $builders = null;
        $builders = $this->buildOptions(10);
        $post_it = $post_dao->getAllPostsByUsernameIterator('mojojojo', 'twitter');
        $posts = $post_dao->getAllPostsByUsername('mojojojo', 'twitter');
        $cnt = 0;
        foreach($post_it as $key => $value) {
            $this->assertEqual($value->post_text, $posts[$cnt]->post_text);
            $this->assertIsA($value, 'Post');
            $cnt++;
        }
        $this->assertEqual(10, $cnt);

    }

    /**
     * build some posts
     */
    public function buildOptions($count) {
        $builders = array();
        for($i = 1; $i <= $count; $i++) {
            $builder = FixtureBuilder::build(self::TEST_TABLE,
            array('post_id' => $i, 'pub_date' => '-1h', 'author_username' => 'mojojojo', 'author_user_id' => 1) );
            array_push($builders, $builder);
            $post_id = $builder->columns[ 'last_insert_id' ];
            $link_builder = FixtureBuilder::build(self::TEST_TABLE_LINKS, array('post_id' => $post_id) );
            array_push($builders, $link_builder);

        }
        return $builders;
    }

}