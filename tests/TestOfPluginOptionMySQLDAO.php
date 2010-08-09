<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Plugin.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilder.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PluginOptionMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';

class TestOfPluginOptionMySQLDAO extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'plugin_options';
    const TEST_TABLE_PLUGIN = 'plugins';

    public function __construct() {
        $this->UnitTestCase('PluginMySQLDAO class test');
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
        // clear options cache
        PluginOptionMySQLDAO::$cached_options = array();
    }


    public function testDeleteOption() {
        # init our dao
        $dao = new PluginOptionMySQLDAO();

        # build some options
        $builder1 = $this->buildOptions(1, 'test name', 'test option');
        $builder2 = $this->buildOptions(2, 'test name2', 'test option2');
        $builder3 = $this->buildOptions(3, 'test name3', 'test option3');

        $insert_id = $builder1->columns[ 'last_insert_id' ];
        $this->assertTrue( $dao->deleteOption( $insert_id ), "delete an option" );
        $sql = "select * from " . $this->prefix . 'plugin_options where id = ' . $insert_id;
        $stmt = PluginOptionMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertFalse($data, 'should be no plugin option data');

        $sql = "select count(*) as option_count from " . $this->prefix . 'plugin_options';
        $stmt = PluginOptionMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_count'], 2, 'we should have two options left');

        # try and delete a non existent option
        $this->assertFalse( $dao->deleteOption( -99 ), "delete an non existent option" );

    }

    public function testOfInsertOption() {
        # init our dao
        $dao = new PluginOptionMySQLDAO();
        $this->assertEqual(
        $dao->insertOption( 101, 'an option name', 'an option value' ), 1, "added/inserted an option, id  is 1" );
        $sql = "select * from " . $this->prefix . 'plugin_options where plugin_id = 101';
        $stmt = PluginOptionMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['id'], 1);
        $this->assertEqual($data['plugin_id'], 101);
        $this->assertEqual($data['option_name'], 'an option name', 'an option name');
        $this->assertEqual($data['option_value'], 'an option value', 'an option value');
        $sql = "select count(*) as option_count from " . $this->prefix . 'plugin_options';
        $stmt = PluginOptionMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_count'], 1, 'we should have one option');
    }

    public function testOfUpdateOption() {
        # init our dao
        $dao = new PluginOptionMySQLDAO();
        $builder1 = $this->buildOptions(1, 'test name', 'test option');
        $insert_id1 = $builder1->columns[ 'last_insert_id' ];
        $builder2 = $this->buildOptions(2, 'test name2', 'test option2');
        $insert_id2 = $builder2->columns[ 'last_insert_id' ];

        // update with a bad id
        $this->assertFalse( $dao->updateOption( -99, 'a name', 'a value', 'nothing updated' ) );

        // update with valid id
        $this->assertTrue(
        $dao->updateOption( $insert_id1, 'an option name updated', 'an option value updated' ),
            "updated an option" );
        // validate updated data
        $sql = "select * from " . $this->prefix . 'plugin_options where id = '. $insert_id1;
        $stmt = PluginOptionMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['id'], $insert_id1);
        $this->assertEqual($data['plugin_id'], $builder1->columns[ 'plugin_id' ]);
        $this->assertEqual($data['option_name'], 'an option name updated', 'name updated');
        $this->assertEqual($data['option_value'], 'an option value updated', 'value updated');

        // make sure we only update data for our id
        $sql = "select * from " . $this->prefix . 'plugin_options where id = '. $insert_id2;
        $stmt = PluginOptionMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['id'], $insert_id2);
        $this->assertEqual($data['plugin_id'], $builder2->columns[ 'plugin_id' ]);
        $this->assertEqual($data['option_name'], 'test name2', 'name not updated');
        $this->assertEqual($data['option_value'], 'test option2', 'value not updated');
    }

    public function testOfgetOptions() {
        # init our dao
        $dao = new PluginOptionMySQLDAO();
        $builder1 = $this->buildOptions(1, 'test name', 'test option');
        $insert_id1 = $builder1->columns[ 'last_insert_id' ];
        $builder2 = $this->buildOptions(2, 'test name2', 'test option2');
        $insert_id2 = $builder2->columns[ 'last_insert_id' ];
        $builder3 = $this->buildOptions(2, 'test name3', 'test option3');
        $insert_id3 = $builder3->columns[ 'last_insert_id' ];

        // bad plugin id
        $this->assertNull( $dao->getOptions(-99) );

        // gets all options if no plugin_id passed
        $options = $dao->getOptions();
        $this->assertNotNull( $options );
        $this->assertEqual(3, count($options));
        $this->assertIsA($options[0], 'PluginOption');
        $this->assertEqual($options[0]->plugin_id, 1);
        $this->assertEqual($options[1]->plugin_id, 2);
        $this->assertEqual($options[2]->plugin_id, 2);

        // gets all options if plugin_id passed
        $options = $dao->getOptions(1);
        $this->assertNotNull( $options );
        $this->assertEqual(1, count($options));
        $this->assertIsA($options[0], 'PluginOption');
        $this->assertEqual($options[0]->plugin_id, 1);
        $this->assertEqual($options[0]->id, 1);

        // gets all options if plugin_id passed one more time
        $options = $dao->getOptions(2);
        $this->assertNotNull( $options );
        $this->assertEqual(2, count($options));
        $this->assertIsA($options[0], 'PluginOption');
        $this->assertEqual($options[0]->plugin_id, 2);
        $this->assertEqual($options[0]->id, 2);
        $this->assertIsA($options[1], 'PluginOption');
        $this->assertEqual($options[1]->plugin_id, 2);
        $this->assertEqual($options[1]->id, 3);

    }

    public function testOfCachedOptions() {
        # init our dao
        $dao = new PluginOptionMySQLDAO();
        $builder1 = $this->buildOptions(1, 'test name', 'test option');
        $insert_id1 = $builder1->columns[ 'last_insert_id' ];
        $builder2 = $this->buildOptions(2, 'test name2', 'test option2');
        $insert_id2 = $builder2->columns[ 'last_insert_id' ];
        $builder3 = $this->buildOptions(2, 'test name3', 'test option3');
        $insert_id3 = $builder3->columns[ 'last_insert_id' ];

        // gets all options if no plugin_id passed
        $options = $dao->getOptions(null, true);
        $this->assertNotNull( $options );
        $this->assertEqual(3, count($options));
        $this->assertIsA($options[0], 'PluginOption');
        $this->assertEqual($options[0]->plugin_id, 1);
        $this->assertEqual($options[1]->plugin_id, 2);
        $this->assertEqual($options[2]->plugin_id, 2);

        // in the cache
        $options = PluginOptionMySQLDAO::$cached_options;
        $options = $options['all'];
        $this->assertNotNull( $options );
        $this->assertEqual(3, count($options));
        $this->assertIsA($options[0], 'PluginOption');
        $this->assertEqual($options[0]->plugin_id, 1);
        $this->assertEqual($options[1]->plugin_id, 2);
        $this->assertEqual($options[2]->plugin_id, 2);
        // gets all options if no plugin_id passed (cached)
        $options = $dao->getOptions(null, true);
        $this->assertNotNull( $options );
        $this->assertEqual(3, count($options));
        $this->assertIsA($options[0], 'PluginOption');
        $this->assertEqual($options[0]->plugin_id, 1);
        $this->assertEqual($options[1]->plugin_id, 2);
        $this->assertEqual($options[2]->plugin_id, 2);


        // gets all options if plugin_id passed
        $options = $dao->getOptions(1, true);
        $this->assertNotNull( $options );
        $this->assertEqual(1, count($options));
        $this->assertIsA($options[0], 'PluginOption');
        $this->assertEqual($options[0]->plugin_id, 1);
        $this->assertEqual($options[0]->id, 1);
        //in the cache?
        $options = PluginOptionMySQLDAO::$cached_options;
        $options = $options['1id'];
        $this->assertNotNull( $options );
        $this->assertEqual(1, count($options));
        $this->assertIsA($options[0], 'PluginOption');
        $this->assertEqual($options[0]->plugin_id, 1);
        $this->assertEqual($options[0]->id, 1);
        //cached?
        $options = $dao->getOptions(1, true);
        $this->assertNotNull( $options );
        $this->assertEqual(1, count($options));
        $this->assertIsA($options[0], 'PluginOption');
        $this->assertEqual($options[0]->plugin_id, 1);
        $this->assertEqual($options[0]->id, 1);

    }

    public function testValidatePluginId() {
        # init our dao
        $dao = new PluginOptionMySQLDAO();
        $builder = FixtureBuilder::build(self::TEST_TABLE_PLUGIN);
        $this->assertFalse($dao->isValidPluginId(-99));
        $this->assertTrue($dao->isValidPluginId( $builder->columns[ 'last_insert_id' ] ));
    }

    public function buildOptions($id, $name, $value) {
        $plugin_data = array(
            'plugin_id' => $id,
            'option_name' => $name,
            'option_value' => $value
        );
        $builder = FixtureBuilder::build(self::TEST_TABLE,  $plugin_data);
        return $builder;
    }

}