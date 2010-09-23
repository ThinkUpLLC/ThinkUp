<?php
/**
 *
 * ThinkUp/tests/TestOfPluginMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfPluginMySQLDAO extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'plugins';

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
    }

    private static function pluginSort($a, $b) {
        return strcmp($a->name, $b->name);
    }

    public function testGetInstalledPlugins() {
        # build our data
        $builders_array = $this->buildData();
        # init our dao
        $dao = new PluginMySQLDAO();

        $plugins = $dao->getInstalledPlugins($this->config->getValue("source_root_path"));
        $this->assertEqual(count($plugins),6);

        usort($plugins, 'self::pluginSort');
        $this->assertEqual($plugins[0]->name,"Expand URLs", "Ex-url 'name' Test");
        $this->assertEqual($plugins[0]->folder_name,"expandurls", "Ex-url 'folder_name' test");

        $this->assertEqual($plugins[1]->name,"Facebook", "Facebook 'name' Test");
        $this->assertEqual($plugins[1]->folder_name,"facebook", "Facebook 'folder_name' test");

        $this->assertEqual($plugins[2]->name,"Flickr Thumbnails", "Flickr 'name' Test");
        $this->assertEqual($plugins[2]->folder_name,"flickrthumbnails", "Flickr 'folder_name' test");

        $this->assertEqual($plugins[3]->name, "GeoEncoder", "GeoEncoder 'name' Test");
        $this->assertEqual($plugins[3]->folder_name, "geoencoder", "GeoEncoder 'folder_name' test");

        $this->assertEqual($plugins[4]->name,"Hello ThinkUp", "Hello 'name' Test");
        $this->assertEqual($plugins[4]->folder_name,"hellothinkup", "Hello 'folder_name' test");

        $this->assertEqual($plugins[5]->name,"Twitter", "Twitter 'name' Test");
        $this->assertEqual($plugins[5]->folder_name,"twitter", "Twitter 'folder_name' test");
    }

    public function testInsertPugin() {
        # build our data
        $builders_array = $this->buildData();
        # init our dao
        $dao = new PluginMySQLDAO();
        # get a plugn data object
        $plugin = $this->createPlugin();

        // bad plugin object
        try {
            $dao->insertPlugin('not a plugin object, just a string');
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires a valid plugin data object/', $e->getMessage());
        }

        // no name
        try {
            $plugin = $this->createPlugin();
            $plugin->name = null;
            $dao->insertPlugin($plugin);
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires a valid plugin data object/', $e->getMessage());
        }

        // no folder
        try {
            $plugin = $this->createPlugin();
            $plugin->folder_name = null;
            $dao->insertPlugin($plugin);
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires a valid plugin data object/', $e->getMessage());
        }

        // no is_active
        try {
            $plugin = $this->createPlugin();
            $plugin->is_active = null;
            $dao->insertPlugin($plugin);
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires a valid plugin data object/', $e->getMessage());
        }

        // a good plugin insert
        $plugin = $this->createPlugin();
        $this->assertTrue($dao->insertPlugin($plugin), 'a successful insert');
        $sql = "select * from " . $this->prefix . 'plugins where name = "' . $plugin->name . '"';
        $stmt = PluginMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->verifyPluginData($data, $plugin);

        // a good plugin insert no homepage
        $plugin = $this->createPlugin();
        $plugin->name = 'has no home page';
        $plugin->homepage = null;
        $this->assertTrue($dao->insertPlugin($plugin), 'a successful insert');
        $sql = "select * from " . $this->prefix . 'plugins where name = "' . $plugin->name . '"';
        $stmt = PluginMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->verifyPluginData($data, $plugin);

        // a good plugin insert not active
        $plugin = $this->createPlugin(array('is_active' => false));
        $plugin->name = 'not active';
        $plugin->homepage = null;
        $this->assertTrue($dao->insertPlugin($plugin), 'a successful insert');
        $sql = "select * from " . $this->prefix . 'plugins where name = "' . $plugin->name . '"';
        $stmt = PluginMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->verifyPluginData($data, $plugin);
    }

    public function testUpdatePugin() {
        # build our data
        $builders_array = $this->buildData();
        # init our dao
        $dao = new PluginMySQLDAO();

        // bad plugin object
        try {
            $dao->updatePlugin('not a plugin object, just a string');
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires a valid plugin data object/', $e->getMessage());
        }

        // no name
        try {
            $plugin = $this->createPlugin();
            $plugin->name = null;
            $dao->updatePlugin($plugin);
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires a valid plugin data object/', $e->getMessage());
        }

        // no folder
        try {
            $plugin = $this->createPlugin();
            $plugin->folder_name = null;
            $dao->updatePlugin($plugin);
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires a valid plugin data object/', $e->getMessage());
        }

        // no is_active
        try {
            $plugin = $this->createPlugin();
            $plugin->is_active = null;
            $dao->updatePlugin($plugin);
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires a valid plugin data object/', $e->getMessage());
        }

        // no id
        try {
            $plugin = $this->createPlugin();
            $dao->updatePlugin($plugin);
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires a valid plugin data object/', $e->getMessage());
        }

        # get a plugin data object to update
        $plugin = $this->createPlugin(array('name' => 'mojo jojo 2', 'folder_name' => 'awesomer, two!!!',
        'version' => '1.5.1'));

        //no record to update
        $plugin->id = -9999;
        $this->assertFalse($dao->updatePlugin($plugin));

        //valid update
        $test_plugin_records = $builders_array[0]->columns;
        $plugin->id = $test_plugin_records['last_insert_id'];
        $this->assertTrue($dao->updatePlugin($plugin));
        $sql = "select * from " . $this->prefix . 'plugins where id = ' . $test_plugin_records['last_insert_id'];
        $stmt = PluginMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->verifyPluginData($data, $plugin);

        //valid update no description
        $plugin = $this->createPlugin(array('name' => 'mojo jojo 222', 'folder_name' => 'awesomer, two too!!!',
        'version' => '1.5.1.a', 'description' => null));
        $test_plugin_records = $builders_array[0]->columns;
        $plugin->id = $test_plugin_records['last_insert_id'];
        $this->assertTrue($dao->updatePlugin($plugin));
        $sql = "select * from " . $this->prefix . 'plugins where id = ' . $test_plugin_records['last_insert_id'];
        $stmt = PluginMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->verifyPluginData($data, $plugin);

        //valid update inactive
        $plugin = $this->createPlugin(array('name' => 'mojo jojo 222', 'folder_name' => 'awesomer, two too!!!',
        'version' => '1.5.1.a', 'description' => null, 'is_active' => false));
        $test_plugin_records = $builders_array[0]->columns;
        $plugin->id = $test_plugin_records['last_insert_id'];
        $this->assertTrue($dao->updatePlugin($plugin));
        $sql = "select * from " . $this->prefix . 'plugins where id = ' . $test_plugin_records['last_insert_id'];
        $stmt = PluginMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->verifyPluginData($data, $plugin);
    }

    public function testSetActive() {
        # build our data
        $builders_array = $this->buildData();
        # init our dao
        $dao = new PluginMySQLDAO();

        // flip form flase to true
        $test_plugin_records = $builders_array[0]->columns;
        $id = $test_plugin_records['last_insert_id'];
        $this->assertTrue($dao->setActive($id, true));
        $sql = "select * from " . $this->prefix . 'plugins where id = ' . $test_plugin_records['last_insert_id'];
        $stmt = PluginMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['is_active'], 1);

        // already true
        $test_plugin_records = $builders_array[1]->columns;
        $id = $test_plugin_records['last_insert_id'];
        // nothing updated, so false
        $this->assertFalse($dao->setActive($id, true));
        $sql = "select * from " . $this->prefix . 'plugins where id = ' . $test_plugin_records['last_insert_id'];
        $stmt = PluginMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['is_active'], 1);

        // flip to false
        $test_plugin_records = $builders_array[1]->columns;
        $id = $test_plugin_records['last_insert_id'];
        $this->assertTrue($dao->setActive($id, false));
        $sql = "select * from " . $this->prefix . 'plugins where id = ' . $test_plugin_records['last_insert_id'];
        $stmt = PluginMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['is_active'], 0);

    }

    public function testIsPluginActive() {
        # build our data
        $builders_array = $this->buildData();
        # init our dao
        $dao = new PluginMySQLDAO();

        $this->assertTrue($dao->isPluginActive(1));
        $this->assertFalse($dao->isPluginActive(2));
        $this->assertFalse($dao->isPluginActive(15));
    }

    public function testGetPluginId() {
        # build our data
        $builders_array = $this->buildData();
        # init our dao
        $dao = new PluginMySQLDAO();

        $this->assertEqual($dao->getPluginId('twitter'), 1);
        $this->assertEqual($dao->getPluginId('idontexist'), null);
        $this->assertEqual($dao->getPluginId('testpluginact'), 3);
    }

    public function testGetPluginFolder() {
        # build our data
        $builders_array = $this->buildData();
        # init our dao
        $dao = new PluginMySQLDAO();

        $this->assertEqual($dao->getPluginFolder(1), 'twitter');
        $this->assertEqual($dao->getPluginFolder(99), null);
        $this->assertEqual($dao->getPluginFolder(3), 'testpluginact');
    }

    public function testGetAllPlugins() {
        # build our data
        $builders_array = $this->buildData();
        # init our dao
        $dao = new PluginMySQLDAO();

        $plugins = $dao->getAllPlugins();
        $this->assertTrue(count($plugins) == 3);
        $this->assertTrue($plugins[1]->name == "My Test Plugin");
        $this->assertTrue($plugins[1]->folder_name == "testplugin");

        // no plugins?
        $builders_array = null;
        $this->assertEqual(sizeof($dao->getAllPlugins()), 0);
    }

    public function testGetActivePlugins() {
        # build our data
        $builders_array = $this->buildData();
        # init our dao
        $dao = new PluginMySQLDAO();
        $plugins = $dao->getActivePlugins();

        $this->assertEqual(count($plugins), 2);
        $this->assertEqual($plugins[0]->name,"Twitter");
        $this->assertEqual($plugins[0]->folder_name,"twitter");

        // no plugins?
        $builders_array = null;
        $this->assertEqual(sizeof($dao->getActivePlugins()), 0);
    }

    protected function verifyPluginData($data, $object) {
        if(isset( $object->id )) {
            $this->assertEqual($data['id'], $object->id);
        }
        $this->assertEqual($data['name'], $object->name);
        $this->assertEqual($data['folder_name'], $object->folder_name);
        $this->assertEqual($data['description'], $object->description);
        $this->assertEqual($data['author'], $object->author);
        $this->assertEqual($data['homepage'], $object->homepage);
        $this->assertEqual($data['version'], $object->version);
        $this->assertEqual($data['is_active'] ? true : false, $object->is_active);
    }

    protected function createPlugin($vars = array()) {
        $plugin = new Plugin( array(
           'name' => isset($vars['name']) ? $vars['name'] : "Awesome Plugin!",
           'folder_name' => isset($vars['folder_name']) ? $vars['folder_name'] : 'awesome_folder',
           'description' => isset($vars['description']) ? $vars['description'] : 'Man, what an awesome plugin',
           'author' => isset($vars['author']) ? $vars['author'] : 'Mojo Jojo',
           'homepage' => isset($vars['homepage']) ? $vars['homepage'] : 'http://mojojojo.example.com',
           'version' => isset($vars['version']) ? $vars['version'] : '1.3',
           'is_active' => isset($vars['is_active']) ? $vars['is_active'] : true,
           'icon' => '/awesome.jpg'
           ));
           return $plugin;
    }

    protected function buildData() {
        //Insert test data into test table
        //The default Twitter plugin is inserted by default
        $plugin1 = array(
            'name' => 'My Test Plugin', 'folder_name' => 'testplugin',
            'description' => 'Proof of concept plugin',
            'author' => 'Gina Trapani', 'homepage' => 'http://thinkupapp.com',
            'version' => '0.01', 'is_active' => '0' 
            );
            $builder1 = FixtureBuilder::build(self::TEST_TABLE,  $plugin1);

            $plugin2 = array(
            'name' => 'My Test Plugin Activated', 'folder_name' => 'testpluginact',
            'description' => 'Proof of concept plugin',
            'author' => 'Gina Trapani', 'homepage' => 'http://thinkupapp.com',
            'version' => '0.01', 'is_active' => '1' 
            );
            $builder2 = FixtureBuilder::build(self::TEST_TABLE,  $plugin2);
            return array($builder1, $builder2);
    }

    public function testValidatePluginId() {
        # init our dao
        $dao = new PluginMySQLDAO();
        $builder = FixtureBuilder::build('plugins');
        $this->assertFalse($dao->isValidPluginId(-99));
        $this->assertTrue($dao->isValidPluginId( $builder->columns[ 'last_insert_id' ] ));
    }
}
