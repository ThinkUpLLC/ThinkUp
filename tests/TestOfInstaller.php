<?php
/**
 *
 * ThinkUp/tests/TestOfInstaller.php
 *
 * Copyright (c) 2009-2013 Dwi Widiastuti, Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * Test Of Installer
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Dwi Widiastuti, Gina Trapani
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInstaller extends ThinkUpUnitTestCase {
    public function __construct() {
        if ( !defined('THINKUP_ROOT_PATH') ) {
            define('THINKUP_ROOT_PATH', str_replace("\\",'/', dirname(dirname(__FILE__))) .'/');
        }

        if ( !defined('THINKUP_WEBAPP_PATH') ) {
            define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH.'webapp/');
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
        $this->debug("Running testInstallerCheckVersion");
        $installer = Installer::getInstance();
        $this->assertTrue($installer->checkVersion());
        $this->assertFalse($installer->checkVersion('4'));

        $ver = $installer->getRequiredVersion();
        $ver = $ver['php'] + 0.1;

        $this->assertTrue($installer->checkVersion($ver));
    }

    public function testInstallerCheckDependency() {
        $installer = Installer::getInstance();
        $dependency = $installer->checkDependency();
        $this->assertTrue($dependency['curl'], 'cURL is installed');
        $this->assertTrue($dependency['gd'], 'gd lib is installed');
        $this->assertTrue($dependency['pdo'], 'pdo lib is installed');
        $this->assertTrue($dependency['pdo_mysql'], 'pdo mysql lib is installed');
        $this->assertTrue($dependency['json'], 'json lib is installed');
        $this->assertTrue($dependency['hash'], 'hash lib is installed');
        $this->assertTrue($dependency['ZipArchive'], 'ZipArchive lib is installed');
    }

    public function testInstallerCheckPermission() {
        $installer = Installer::getInstance();
        $perms = $installer->checkPermission();
        $this->assertTrue($perms['data_dir']);
        $this->assertTrue($perms['cache']);
    }

    public function testIsSessionDirectoryWritable() {
        //get whatever session save path is set to
        $session_save_path = ini_get('session.save_path');

        ini_set('session.save_path', FileDataManager::getDataPath());
        $installer = Installer::getInstance();
        $session_save_permission = $installer->isSessionDirectoryWritable();
        $this->assertTrue($session_save_permission);

        //reset back to what it was
        ini_set('session.save_path', $session_save_path);
    }

    public function testIsInvalidSessionDirectoryWritable() {
        $session_save_path = ini_get('session.save_path');
        $installer = Installer::getInstance();

        // set session save dir to something invalid
        ini_set('session.save_path', '/someinvalidpath/wecantwriteto/');

        $session_save_permission = $installer->isSessionDirectoryWritable();
        $this->assertFalse($session_save_permission);

        //reset back to what it was
        ini_set('session.save_path', $session_save_path);
    }

    public function testInstallerCheckPath() {
        $installer = Installer::getInstance();
        $this->assertTrue($installer->checkPath(array('source_root_path' => THINKUP_ROOT_PATH,
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
        $q = "DROP TABLE ".
        $config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."favorites, ".
        //$config->getValue('table_prefix')."follows, ".
        $config->getValue('table_prefix')."cookies, ".
        $config->getValue('table_prefix')."count_history, ".
        $config->getValue('table_prefix')."groups, ".
        $config->getValue('table_prefix')."group_members, ".
        $config->getValue('table_prefix')."hashtags," .
        $config->getValue('table_prefix')."hashtags_posts, " .
        $config->getValue('table_prefix')."instances, ".
        $config->getValue('table_prefix')."instances_hashtags, ".
        $config->getValue('table_prefix')."instances_facebook, ".
        $config->getValue('table_prefix')."instances_twitter, ".
        $config->getValue('table_prefix')."invites," .
        $config->getValue('table_prefix')."insight_baselines," .
        $config->getValue('table_prefix')."insights," .
        //$config->getValue('table_prefix')."links," .
        $config->getValue('table_prefix')."links_short," .
        $config->getValue('table_prefix')."mentions," .
        $config->getValue('table_prefix')."mentions_posts, " .
        $config->getValue('table_prefix')."owner_instances, ".
        $config->getValue('table_prefix')."owners, ".
        $config->getValue('table_prefix')."options, ".
        $config->getValue('table_prefix')."places," .
        $config->getValue('table_prefix')."places_posts, " .
        $config->getValue('table_prefix')."plugins, ".
        $config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".
        $config->getValue('table_prefix')."stream_data, " .
        $config->getValue('table_prefix')."stream_procs, ".
        $config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."users, ".
        $config->getValue('table_prefix')."photos, ".
        $config->getValue('table_prefix')."sessions, ".
        $config->getValue('table_prefix')."videos;";
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
        $q = "DROP TABLE ".
        $config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."favorites, ".
        $config->getValue('table_prefix')."cookies, ".
        $config->getValue('table_prefix')."count_history, ".
        $config->getValue('table_prefix')."groups, ".
        $config->getValue('table_prefix')."group_members, ".
        $config->getValue('table_prefix')."hashtags," .
        $config->getValue('table_prefix')."hashtags_posts, " .
        $config->getValue('table_prefix')."instances, ".
        $config->getValue('table_prefix')."instances_hashtags, ".
        $config->getValue('table_prefix')."instances_facebook, ".
        $config->getValue('table_prefix')."instances_twitter, ".
        $config->getValue('table_prefix')."invites," .
        $config->getValue('table_prefix')."insight_baselines," .
        $config->getValue('table_prefix')."insights," .
        $config->getValue('table_prefix')."links_short," .
        $config->getValue('table_prefix')."mentions," .
        $config->getValue('table_prefix')."mentions_posts, " .
        $config->getValue('table_prefix')."owner_instances, ".
        $config->getValue('table_prefix')."owners, ".
        $config->getValue('table_prefix')."options, ".
        $config->getValue('table_prefix')."places," .
        $config->getValue('table_prefix')."places_posts, " .
        $config->getValue('table_prefix')."plugins, ".
        $config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".
        $config->getValue('table_prefix')."stream_data, " .
        $config->getValue('table_prefix')."stream_procs, ".
        $config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."users, ".
        $config->getValue('table_prefix')."photos, ".
        $config->getValue('table_prefix')."sessions, ".
        $config->getValue('table_prefix')."videos";

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
        $q = "DROP TABLE ".
        $config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."favorites, ".
        $config->getValue('table_prefix')."cookies, ".
        $config->getValue('table_prefix')."count_history, ".
        $config->getValue('table_prefix')."groups, ".
        $config->getValue('table_prefix')."group_members, ".
        $config->getValue('table_prefix')."hashtags," .
        $config->getValue('table_prefix')."hashtags_posts, " .
        $config->getValue('table_prefix')."instances, ".
        $config->getValue('table_prefix')."instances_facebook, ".
        $config->getValue('table_prefix')."instances_twitter, ".
        $config->getValue('table_prefix')."invites," .
        $config->getValue('table_prefix')."insight_baselines," .
        $config->getValue('table_prefix')."insights," .
        $config->getValue('table_prefix')."mentions," .
        $config->getValue('table_prefix')."mentions_posts, " .
        $config->getValue('table_prefix')."owner_instances, ".
        $config->getValue('table_prefix')."owners, ".
        $config->getValue('table_prefix')."options, ".
        $config->getValue('table_prefix')."places," .
        $config->getValue('table_prefix')."places_posts, " .
        $config->getValue('table_prefix')."plugins, ".
        $config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".
        $config->getValue('table_prefix')."stream_data, " .
        $config->getValue('table_prefix')."stream_procs, ".
        $config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."photos, ".
        $config->getValue('table_prefix')."users;";
        PDODAO::$PDO->exec($q);

        Installer::$show_tables = array();
        $expected = Installer::$tables;
        array_pop($expected);
        $this->assertFalse($installer->doThinkUpTablesExist($config_array));
    }

    public function testInstallerIsThinkUpInstalled() {
        $this->debug("Running testInstallerIsThinkUpInstalled");
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();

        $installer = Installer::getInstance();

        //drop some tables so is_installed will be false
        $this->DAO = new InstallerMySQLDAO($config_array);
        $q = "DROP TABLE ".
        $config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."favorites, ".
        $config->getValue('table_prefix')."cookies, ".
        $config->getValue('table_prefix')."count_history, ".
        $config->getValue('table_prefix')."groups, ".
        $config->getValue('table_prefix')."group_members, ".
        $config->getValue('table_prefix')."hashtags," .
        $config->getValue('table_prefix')."hashtags_posts, " .
        $config->getValue('table_prefix')."instances, ".
        $config->getValue('table_prefix')."instances_hashtags, ".
        $config->getValue('table_prefix')."instances_facebook, ".
        $config->getValue('table_prefix')."instances_twitter, ".
        $config->getValue('table_prefix')."invites," .
        $config->getValue('table_prefix')."insight_baselines," .
        $config->getValue('table_prefix')."insights," .
        $config->getValue('table_prefix')."mentions," .
        $config->getValue('table_prefix')."mentions_posts, " .
        $config->getValue('table_prefix')."owner_instances, ".
        $config->getValue('table_prefix')."owners, ".
        $config->getValue('table_prefix')."options, ".
        $config->getValue('table_prefix')."places," .
        $config->getValue('table_prefix')."places_posts, " .
        $config->getValue('table_prefix')."plugins, ".
        $config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".
        $config->getValue('table_prefix')."stream_data, " .
        $config->getValue('table_prefix')."stream_procs, ".
        $config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."photos, ".
        $config->getValue('table_prefix')."sessions, ".
        $config->getValue('table_prefix')."users;";
        PDODAO::$PDO->exec($q);

        if ( file_exists(THINKUP_WEBAPP_PATH . 'config.inc.php') ) {
            // test when config file exists
            $this->debug("config file exists");
            $version_met = $installer->checkStep1();
            $this->debug("version met ".Utils::varDumpToString($version_met));
            $db_check = $installer->checkDb($config_array);
            $this->debug("db check ".Utils::varDumpToString($db_check));
            $table_present = $installer->doThinkUpTablesExist($config_array);
            $this->debug("table present ".Utils::varDumpToString($table_present));
            $is_installed = $installer->isThinkUpInstalled($config_array);
            $this->debug("is installed ".Utils::varDumpToString($is_installed));
            $expected = ($version_met && $db_check && $table_present);
            $this->assertEqual($is_installed, $expected);
            $this->assertFalse($is_installed);
        } else {
            // test when config doesn't exist
            $this->debug("config file does not exist");
            $this->assertFalse( $installer->isThinkUpInstalled($this->config) );
            $expected = $installer->getErrorMessages();
            $this->assertEqual( $expected['config_file'], "Config file doesn't exist.");
        }
    }

    public function testInstallerPopulateTablesWithNonStandardPrefix() {
        $config = Config::getInstance();
        $non_standard_prefix = 'non_standard_tu_';
        $config->setValue('table_prefix', $non_standard_prefix);
        $config_array = $config->getValuesArray();

        $expected_table = $non_standard_prefix . 'instances_twitter';

        $installer = Installer::getInstance();
        $db = $installer->setDb($config_array);
        $log_verbose = $installer->populateTables($config_array);
        $this->assertTrue(isset($log_verbose[$expected_table]));

        $q = sprintf("SHOW TABLES LIKE '%s'", $expected_table);
        $stmt = PDODAO::$PDO->query($q);
        $table = $stmt->fetch(PDO::FETCH_NUM);
        $this->assertEqual($table[0], $expected_table);
    }

    public function testInstallerPopulateTables() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();

        $installer = Installer::getInstance();

        // test on existing owner table that's recognized as a ThinkUp table
        // drop everything but owners
        $dao = new InstallerMySQLDAO($config_array);
        $q = "DROP TABLE ".
        $config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."favorites, ".
        $config->getValue('table_prefix')."cookies, ".
        $config->getValue('table_prefix')."count_history, ".
        $config->getValue('table_prefix')."follows, ".
        $config->getValue('table_prefix')."groups, ".
        $config->getValue('table_prefix')."group_members, ".
        $config->getValue('table_prefix')."hashtags," .
        $config->getValue('table_prefix')."hashtags_posts, " .
        $config->getValue('table_prefix')."instances, ".
        $config->getValue('table_prefix')."instances_hashtags, ".
        $config->getValue('table_prefix')."instances_facebook, ".
        $config->getValue('table_prefix')."instances_twitter, ".
        $config->getValue('table_prefix')."invites," .
        $config->getValue('table_prefix')."insight_baselines," .
        $config->getValue('table_prefix')."insights," .
        $config->getValue('table_prefix')."links," .
        $config->getValue('table_prefix')."links_short," .
        $config->getValue('table_prefix')."mentions," .
        $config->getValue('table_prefix')."mentions_posts, " .
        $config->getValue('table_prefix')."owner_instances, ".
        //$config->getValue('table_prefix')."owners, ".
        $config->getValue('table_prefix')."options, ".
        $config->getValue('table_prefix')."places," .
        $config->getValue('table_prefix')."places_posts, " .
        $config->getValue('table_prefix')."plugins, ".
        $config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".
        $config->getValue('table_prefix')."stream_data, " .
        $config->getValue('table_prefix')."stream_procs, ".
        $config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."users, ".
        $config->getValue('table_prefix')."photos, ".
        $config->getValue('table_prefix')."sessions, ".
        $config->getValue('table_prefix')."videos;";
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
        $q = "DROP TABLE ".
        $config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."favorites, ".
        $config->getValue('table_prefix')."cookies, ".
        $config->getValue('table_prefix')."count_history, ".
        $config->getValue('table_prefix')."follows, ".
        $config->getValue('table_prefix')."groups, ".
        $config->getValue('table_prefix')."group_members, ".
        $config->getValue('table_prefix')."hashtags," .
        $config->getValue('table_prefix')."hashtags_posts, " .
        $config->getValue('table_prefix')."instances, ".
        $config->getValue('table_prefix')."instances_hashtags, ".
        $config->getValue('table_prefix')."instances_facebook, ".
        $config->getValue('table_prefix')."instances_twitter, ".
        $config->getValue('table_prefix')."invites," .
        $config->getValue('table_prefix')."insight_baselines," .
        $config->getValue('table_prefix')."insights," .
        $config->getValue('table_prefix')."links," .
        $config->getValue('table_prefix')."links_short," .
        $config->getValue('table_prefix')."mentions," .
        $config->getValue('table_prefix')."mentions_posts, " .
        $config->getValue('table_prefix')."owner_instances, ".
        $config->getValue('table_prefix')."owners, ".
        $config->getValue('table_prefix')."options, ".
        $config->getValue('table_prefix')."places," .
        $config->getValue('table_prefix')."places_posts, " .
        $config->getValue('table_prefix')."plugins, ".
        $config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".
        $config->getValue('table_prefix')."stream_data, " .
        $config->getValue('table_prefix')."stream_procs, ".
        $config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."users, ".
        $config->getValue('table_prefix')."photos, ".
        $config->getValue('table_prefix')."sessions, ".
        $config->getValue('table_prefix')."videos;";

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
        $q = "DROP TABLE ".
        $config->getValue('table_prefix')."encoded_locations, ".
        $config->getValue('table_prefix')."favorites, ".
        $config->getValue('table_prefix')."cookies, ".
        $config->getValue('table_prefix')."count_history, ".
        $config->getValue('table_prefix')."follows, ".
        $config->getValue('table_prefix')."groups, ".
        $config->getValue('table_prefix')."group_members, ".
        $config->getValue('table_prefix')."hashtags," .
        $config->getValue('table_prefix')."hashtags_posts, " .
        $config->getValue('table_prefix')."instances, ".
        $config->getValue('table_prefix')."instances_hashtags, ".
        $config->getValue('table_prefix')."instances_facebook, ".
        $config->getValue('table_prefix')."instances_twitter, ".
        $config->getValue('table_prefix')."invites," .
        $config->getValue('table_prefix')."insight_baselines," .
        $config->getValue('table_prefix')."insights," .
        $config->getValue('table_prefix')."links," .
        $config->getValue('table_prefix')."links_short," .
        $config->getValue('table_prefix')."mentions," .
        $config->getValue('table_prefix')."mentions_posts, " .
        $config->getValue('table_prefix')."owner_instances, ".
        $config->getValue('table_prefix')."owners, ".
        $config->getValue('table_prefix')."options, ".
        $config->getValue('table_prefix')."places," .
        $config->getValue('table_prefix')."places_posts, " .
        $config->getValue('table_prefix')."plugins, ".
        $config->getValue('table_prefix')."post_errors, ".
        $config->getValue('table_prefix')."posts, ".
        $config->getValue('table_prefix')."stream_data, " .
        $config->getValue('table_prefix')."stream_procs, ".
        $config->getValue('table_prefix')."user_errors, ".
        $config->getValue('table_prefix')."users, ".
        $config->getValue('table_prefix')."photos, ".
        $config->getValue('table_prefix')."sessions, ".
        $config->getValue('table_prefix')."videos;";
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
        $expected = 'Your ThinkUp table repairs are <strong class="okay">complete</strong>.';
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

        $expected_tables = array('cookies', 'count_history', 'encoded_locations', 'favorites', 'follows',
        'group_members', 'groups', 'hashtags', 'hashtags_posts',
        'insight_baselines', 'insights', 'instances', 'instances_facebook', 'instances_hashtags', 'instances_twitter',
        'invites', 'links', 'links_short', 'mentions', 'mentions_posts', 'options',
        'owner_instances', 'owners', 'photos', 'places','places_posts',
        'plugins', 'post_errors', 'posts', 'sessions', 'stream_data', 'stream_procs', 'user_errors', 'users', 'videos');
        $this->assertIdentical($tables, $expected_tables);
    }

    public function testStoreServerName() {
        $option_dao = DAOFactory::getDAO('OptionDAO');
        $current_stored_server_name = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'server_name');
        $this->assertNull($current_stored_server_name);

        $_SERVER['HTTP_HOST'] = 'mytestthinkup';
        Installer::storeServerName();
        $current_stored_server_name = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'server_name');
        $this->assertNotNull($current_stored_server_name);
        $this->assertEqual($current_stored_server_name->option_value, 'mytestthinkup');
        $this->assertEqual($current_stored_server_name->option_name, 'server_name');

        $_SERVER['SERVER_NAME'] = 'myreallygoodtest';
        Installer::storeServerName();
        $current_stored_server_name = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'server_name');
        $this->assertNotNull($current_stored_server_name);
        $this->assertEqual($current_stored_server_name->option_value, 'myreallygoodtest');
        $this->assertEqual($current_stored_server_name->option_name, 'server_name');
    }
}
