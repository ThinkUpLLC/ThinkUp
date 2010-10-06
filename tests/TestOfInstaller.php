<?php
/**
 *
 * ThinkUp/tests/TestOfInstaller.php
 *
 * Copyright (c) 2009-2010 Dwi Widiastuti, Gina Trapani
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
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test Of Installer
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Dwi Widiastuti, Gina Trapani
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestOfInstaller extends ThinkUpUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('Installer class test');
        if ( !defined('THINKUP_ROOT_PATH') ) {
            define('THINKUP_ROOT_PATH', str_replace("\\",'/', dirname(dirname(__FILE__))) .'/');
        }

        if ( !defined('THINKUP_WEBAPP_PATH') ) {
            define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp/');
        }

        if ( !defined('THINKUP_BASE_URL') ) {
            // Define base URL, the same as $THINKUP_CFG['site_root_path']
            $current_script_path = explode('/', $_SERVER['PHP_SELF']);
            array_pop($current_script_path);
            if ( in_array($current_script_path[count($current_script_path)-1],
            array('account', 'post', 'session', 'user', 'install')) ) {
                array_pop($current_script_path);
            }
            $current_script_path = implode('/', $current_script_path) . '/';
            define('THINKUP_BASE_URL', $current_script_path);
        }
    }

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $inst = new Installer();
        $this->assertTrue(isset($inst));
        $this->assertIsA($inst, 'Installer');

        //test singleton
        $inst1 = Installer::getInstance();
        $this->assertTrue(isset($inst1));
        $this->assertIsA($inst1, 'Installer');
    }

    public function testGetInstallerInstance() {
        $this->assertIsA(Installer::getInstance(), 'Installer');
    }

    public function testInstallerCheckVersion() {
        $this->assertTrue(Installer::checkVersion());
        $this->assertFalse(Installer::checkVersion('4'));

        $ver = Installer::getRequiredVersion();
        $ver = $ver['php'] + 0.1;

        $this->assertTrue(Installer::checkVersion($ver));
    }

    public function testInstallerCheckDependency() {
        $dependency = Installer::checkDependency();
        $this->assertTrue($dependency['curl'], 'cURL is installed');
        $this->assertTrue($dependency['gd'], 'gd lib is installed');
        $this->assertTrue($dependency['pdo'], 'pdo lib is installed');
        $this->assertTrue($dependency['pdo_mysql'], 'pdo mysql lib is installed');
    }

    public function testInstallerCheckPermission() {
        $perms = Installer::checkPermission();
        $this->assertTrue($perms['compiled_view'], THINKUP_ROOT_PATH .
                'webapp/view/compiled_view is writeable by the webserver');
        $this->assertTrue($perms['cache'], THINKUP_ROOT_PATH .
                'webapp/view/compiled_view/cache is writeable by the webserver');
    }

    public function testInstallerCheckPath() {
        $this->assertTrue(Installer::checkPath(array('source_root_path' => THINKUP_ROOT_PATH,
                'smarty_path' => THINKUP_WEBAPP_PATH . '_lib/extlib/Smarty-2.6.26/libs/')));
    }

    public function testInstallerCheckStep1() {
        $installer = Installer::getInstance();
        $this->assertTrue($installer->checkStep1());
    }

    public function testInstallerCheckDb() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $installer = Installer::getInstance();

        $cdb = $installer->checkDb($config_array);
        $this->assertTrue($cdb);

        $db = $installer->setDb($config_array);
        $this->assertIsA($db, 'InstallerMySQLDAO');
    }

    public function testInstallerShowTables() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();

        // test with some tables
        // drop all but follows and links
        $this->DAO = new InstallerMySQLDAO($config_array);
        $q = "DROP TABLE ".$config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."follower_count, ".$config->getValue('table_prefix').
        "instances, ".$config->getValue('table_prefix')."owner_instances, ".$config->getValue('table_prefix').
        "owners, ".$config->getValue('table_prefix')."plugin_options,
        ".$config->getValue('table_prefix')."plugins, ".$config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".$config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."users;";
        PDODAO::$PDO->exec($q);

        $installer = Installer::getInstance();
        Installer::$show_tables = null; //set show_tables to null to force a refresh
        $tables = $installer->showTables($config_array);
        $expected = array($config->getValue('table_prefix').'follows', $config->getValue('table_prefix').'links');
        $this->assertIdentical(Installer::$show_tables, $expected);

        // test with a table
        // drop links so only follows is left
        $q = "DROP TABLE ".$config->getValue('table_prefix')."links;";
        PDODAO::$PDO->exec($q);

        $installer = Installer::getInstance();
        Installer::$show_tables = null; //set show_tables to null to force a refresh
        $tables = $installer->showTables($config_array);
        $expected = array($config->getValue('table_prefix').'follows');
        $this->assertIdentical(Installer::$show_tables, $expected);

        // test with no tables; drop follows
        $q = "DROP TABLE ".$config->getValue('table_prefix')."follows;";
        PDODAO::$PDO->exec($q);
        $installer = Installer::getInstance();
        Installer::$show_tables = null; //set show_tables to null to force a refresh
        $tables = $installer->showTables($config_array);
        $expected = array();
        $this->assertIdentical($tables, $expected);
        $this->assertIdentical(Installer::$show_tables, $expected);
    }

    public function testInstallerCheckTable() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();

        // test with complete tables (will fail)
        $installer = Installer::getInstance();
        Installer::$show_tables = array();
        $expected = Installer::$tables;
        try {
            $this->assertTrue($installer->checkTable($config_array));
            $this->fail('should throw an InstallerException');
        } catch(InstallerException $e) {
            $this->assertPattern('/database tables already exist./', $e->getMessage(), $e->getMessage());
        }

        // test with incomplete tables (also fail)
        $this->DAO = new InstallerMySQLDAO($config_array);
        $q = "DROP TABLE ".$config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."follower_count, ".$config->getValue('table_prefix').
        "instances, ".$config->getValue('table_prefix')."owner_instances, ".$config->getValue('table_prefix').
        "owners, ".$config->getValue('table_prefix')."plugin_options,
        ".$config->getValue('table_prefix')."plugins, ".$config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".$config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."users;";
        PDODAO::$PDO->exec($q);

        Installer::$show_tables = array();
        try {
            $this->assertTrue($installer->checkTable($config_array));
            $this->fail('should throw an InstallerException');
        } catch(InstallerException $e) {
            $this->assertPattern('/database tables already exist./', $e->getMessage(), $e->getMessage());
        }

        // test with empty table
        $q = "DROP TABLE ".$config->getValue('table_prefix')."links;";
        PDODAO::$PDO->exec($q);
        $q = "DROP TABLE ".$config->getValue('table_prefix')."follows;";
        PDODAO::$PDO->exec($q);
        Installer::$show_tables = array();
        $this->assertTrue($installer->checkTable($config_array));

        // test with complete tables but with different prefix
        $tables = Installer::$tables;
        foreach ($tables as $key => $table) {
            $tables[$key] = 'prefix_' . $config_array['table_prefix'] . $table;
        }
        Installer::$show_tables = array();
        $this->assertTrue($installer->checkTable($config_array));
    }

    public function testDoThinkUpTablesExist() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();

        // test with complete tables
        $installer = Installer::getInstance();
        Installer::$show_tables = array();
        $expected = Installer::$tables;
        $this->assertTrue($installer->doThinkUpTablesExist($config_array));

        // test with incomplete tables (will fail)
        $this->DAO = new InstallerMySQLDAO($config_array);
        $q = "DROP TABLE ".$config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."follower_count, ".$config->getValue('table_prefix').
        "instances, ".$config->getValue('table_prefix')."owner_instances, ".$config->getValue('table_prefix').
        "owners, ".$config->getValue('table_prefix')."plugin_options,
        ".$config->getValue('table_prefix')."plugins, ".$config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".$config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."users;";
        PDODAO::$PDO->exec($q);

        Installer::$show_tables = array();
        $expected = Installer::$tables;
        array_pop($expected);
        $this->assertFalse($installer->doThinkUpTablesExist($config_array));
    }

    public function testInstallerIsThinkUpInstalled() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();

        $installer = Installer::getInstance();

        if ( file_exists(THINKUP_WEBAPP_PATH . 'config.inc.php') ) {
            // test when config file exists
            $version_met = $installer->checkStep1();
            $db_check = $installer->checkDb($config_array);
            $table_present = $installer->doThinkUpTablesExist($config_array);
            $is_installed = $installer->isThinkUpInstalled($config_array);
            $expected = ($version_met && $db_check && $table_present);
            $this->assertEqual($is_installed, $expected);
            $this->assertFalse($is_installed);
        } else {
            // test when config doesn't exist
            $this->assertFalse( $installer->isThinkUpInstalled($this->config) );
            $expected = $installer->getErrorMessages();
            $this->assertEqual( $expected['config_file'], "Config file doesn't exist.");
        }
    }

    public function testInstallerPopulateTables() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();

        $installer = Installer::getInstance();

        // test on existing owner table that's recognized as a ThinkUp table
        // drop everything but owners
        $dao = new InstallerMySQLDAO($config_array);
        $q = "DROP TABLE ".$config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."follower_count, ".$config->getValue('table_prefix')."instances, ".
        $config->getValue('table_prefix')."owner_instances, ".$config->getValue('table_prefix')."plugin_options, ".
        $config->getValue('table_prefix')."plugins, ".$config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".$config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."users, ".$config->getValue('table_prefix')."follows, ".
        $config->getValue('table_prefix')."links;";
        PDODAO::$PDO->exec($q);

        Installer::$show_tables = array();
        $log_verbose = $installer->populateTables($config_array);
        $tables = Installer::$tables;
        $expected = array();
        foreach ($tables as $k => $v) {
            $expected[$config_array['table_prefix'].$v] = "Created table {$config_array['table_prefix']}$v";
        }
        unset($expected["{$config_array['table_prefix']}owners"]);
        $this->assertEqual($log_verbose, $expected);

        // test with verbose on empty test database
        $dao = new InstallerMySQLDAO($config_array);
        $q = "DROP TABLE ".$config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."follower_count, ".$config->getValue('table_prefix')."instances, ".
        $config->getValue('table_prefix')."owner_instances, ".$config->getValue('table_prefix')."plugin_options, ".
        $config->getValue('table_prefix')."plugins, ".$config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".$config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."users, ".$config->getValue('table_prefix')."follows, ".
        $config->getValue('table_prefix')."links, ".$config->getValue('table_prefix')."owners;";
        PDODAO::$PDO->exec($q);

        Installer::$show_tables = array();
        $log_verbose = $installer->populateTables($config_array);
        $tables = Installer::$tables;
        $expected = array();
        foreach ($tables as $k => $v) {
            $expected[$config_array['table_prefix'].$v] = "Created table {$config_array['table_prefix']}$v";
        }
        $this->assertEqual($log_verbose, $expected);

        // test on existent tables that's not recognized as a ThinkUp table
        $this->DAO = new InstallerMySQLDAO($config_array);
        $q = "DROP TABLE ".$config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."follower_count, ".$config->getValue('table_prefix')."instances, ".
        $config->getValue('table_prefix')."owner_instances, ".$config->getValue('table_prefix')."owners, ".
        $config->getValue('table_prefix')."plugin_options, ".$config->getValue('table_prefix')."plugins, ".
        $config->getValue('table_prefix')."post_errors, ".$config->getValue('table_prefix')."posts, ".
        $config->getValue('table_prefix')."user_errors, ".$config->getValue('table_prefix')."users, ".
        $config->getValue('table_prefix')."follows, ".$config->getValue('table_prefix')."links;";
        PDODAO::$PDO->exec($q);
        $q = "CREATE TABLE weird_random_table(id INT);";
        PDODAO::$PDO->exec($q);

        Installer::$show_tables = array();
        $log_verbose = $installer->populateTables($config_array);
        $tables = Installer::$tables;
        $expected = array();
        foreach ($tables as $k => $v) {
            $expected[$config_array['table_prefix'].$v] = "Created table {$config_array['table_prefix']}$v";
        }
        $this->assertEqual($log_verbose, $expected);

        // test on fully ThinkUp table
        Installer::$show_tables = array();
        $installer->populateTables($config_array);
        // supply verbose on second paramater
        $log_verbose = $installer->populateTables($config_array, true);
        $expected = array();
        $this->assertIdentical($log_verbose, $expected);
    }

    public function testInstallerRepairTables() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $installer = Installer::getInstance();

        // test repair on healthy and complete tables
        Installer::$show_tables = array();
        $installer->populateTables($config_array);
        $expected = 'Your ThinkUp tables are <strong class="okay">complete</strong>.';
        $messages = $installer->repairTables($config_array);
        $this->assertIdentical($messages['table_complete'], $expected, $messages['table_complete']);

        // test repair on missing tables
        $this->DAO = new InstallerMySQLDAO($config_array);
        $q = "DROP TABLE ".$config->getValue('table_prefix')."owners;";
        PDODAO::$PDO->exec($q);
        Installer::$show_tables = array();
        $expected = '/There are <strong class="not_okay">1 missing tables/i';
        $messages = $installer->repairTables($config_array);
        $this->assertPattern($expected, $messages['missing_tables']);
    }

    public function testGetTablesToInstall(){
        $installer = Installer::getInstance();
        $tables = $installer->getTablesToInstall();
        $expected_tables = array('encoded_locations', 'follower_count', 'follows', 'instances', 'links',
        'owner_instances', 'owners', 'plugin_options', 'plugins', 'post_errors', 'posts', 'user_errors', 'users');
        $this->assertIdentical($tables, $expected_tables);
    }
}