<?php
/**
 *
 * ThinkUp/tests/TestOfFileDataManager.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani, Guillaume Boudreau
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfFileDataManager extends ThinkUpBasicUnitTestCase {

    public function testGetDataPathNoConfigFile() {
        Config::destroyInstance();
        $this->removeConfigFile();

        //test just path
        $path = FileDataManager::getDataPath();
        $this->assertEqual($path, THINKUP_WEBAPP_PATH.'data/');

        //test path with file
        $path = FileDataManager::getDataPath('myfile.txt');
        $this->assertEqual($path, THINKUP_WEBAPP_PATH.'data/myfile.txt');
        $this->restoreConfigFile();
    }

    public function testGetDataPathConfigExistsWithoutDataDirValue() {
        Config::destroyInstance();
        $this->removeConfigFile();
        $cfg_values = array("table_prefix"=>"thinkupyo", "db_host"=>"myserver.com");
        $config = Config::getInstance($cfg_values);

        //test just path
        $path = FileDataManager::getDataPath();
        $this->assertEqual($path, THINKUP_WEBAPP_PATH.'data/');

        //test path with file
        $path = FileDataManager::getDataPath('myfile.txt');
        $this->assertEqual($path, THINKUP_WEBAPP_PATH.'data/myfile.txt');
        $this->restoreConfigFile();
    }

    public function testGetDataPathConfigExistsWithDataDirValue() {
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        //if test fails here, the config file doesn't have datadir_path set
        $this->assertNotNull($THINKUP_CFG['datadir_path']);

        //test just path
        $path = FileDataManager::getDataPath();
        $this->assertEqual($path, $THINKUP_CFG['datadir_path']);

        //test path with file
        $path = FileDataManager::getDataPath('myfile.txt');
        $this->assertEqual($path, $THINKUP_CFG['datadir_path'].'myfile.txt');
    }

    public function testGetBackupPath() {
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        //if test fails here, the config file doesn't have datadir_path set
        $this->assertNotNull($THINKUP_CFG['datadir_path']);

        //test just path
        $path = FileDataManager::getBackupPath();
        $this->assertEqual($path, $THINKUP_CFG['datadir_path'].'backup/');

        //test just path
        $path = FileDataManager::getBackupPath('README.txt');
        $this->assertEqual($path, $THINKUP_CFG['datadir_path'].'backup/README.txt');
    }
}