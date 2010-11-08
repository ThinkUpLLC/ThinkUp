<?php
/**
 *
 * ThinkUp/tests/TestOfLoader.php
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
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of Loader class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestOfLoader extends ThinkUpBasicUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('Loader class test');
    }

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testLoaderRegisterDefault() {
        $loader = Loader::register();

        // check if Loader is registered to spl autoload
        $this->assertTrue($loader, 'Loader is registered to spl autoload');

        // check default lookup path without additionalPath
        $this->assertEqual( Loader::getLookupPath(), array(
        THINKUP_WEBAPP_PATH . '_lib/model/',
        THINKUP_WEBAPP_PATH . '_lib/controller/',
        THINKUP_WEBAPP_PATH . '_lib/model/exceptions/'
        ));

        // check special classes
        $this->assertEqual( Loader::getSpecialClasses(),
        array('Smarty'=>THINKUP_WEBAPP_PATH . '_lib/extlib/Smarty-2.6.26/libs/Smarty.class.php'));
    }

    public function testLoaderRegisterWithStringAdditionalPath() {
        // Loader with string of path as additional path
        $loader = Loader::register(array(THINKUP_ROOT_PATH . 'tests/classes'));

        // check if Loader is registered to spl autoload
        $this->assertTrue($loader, 'Loader is registered to spl autoload');

        // check lookup path with single additionalPath
        $this->assertEqual( Loader::getLookupPath(), array(
        THINKUP_WEBAPP_PATH . '_lib/model/',
        THINKUP_WEBAPP_PATH . '_lib/controller/',
        THINKUP_WEBAPP_PATH . '_lib/model/exceptions/',
        THINKUP_ROOT_PATH . 'tests/classes'
        ));
    }

    public function testLoaderRegisterWithArrayAdditionalPaths() {
        // Loader with array of path as additional path
        $loader = Loader::register(array(
        THINKUP_ROOT_PATH . 'tests',
        THINKUP_ROOT_PATH . 'tests/classes'
        ));

        // check if Loader is registered to spl autoload
        $this->assertTrue($loader, 'Loader is registered to spl autoload');

        // check lookup path with array additionalPath
        $this->assertEqual( Loader::getLookupPath(), array(
        THINKUP_WEBAPP_PATH . '_lib/model/',
        THINKUP_WEBAPP_PATH . '_lib/controller/',
        THINKUP_WEBAPP_PATH . '_lib/model/exceptions/',
        THINKUP_ROOT_PATH . 'tests',
        THINKUP_ROOT_PATH . 'tests/classes'
        ));
    }

    public function testLoaderUnregister() {
        Loader::register();
        $unreg = Loader::unregister();

        // check if Loader is succesfully unregistered
        $this->assertTrue($unreg, 'Unregister Loader');

        // make sure lookup path and special classes are null
        $this->assertNull(Loader::getLookupPath());
        $this->assertNull(Loader::getSpecialClasses());
    }

    public function testLoaderInstantiateClasses() {
        Loader::register();

        $this->assertClassInstantiates('Instance');
        $this->assertClassInstantiates('Config');

        $this->assertIsA(new Crawler, 'Crawler');
        $this->assertIsA(new DAOFactory, 'DAOFactory');

        $this->assertIsA(Config::getInstance(), 'Config');
        $this->assertIsA(Logger::getInstance('/tmp/test.log'), 'Logger');
    }

    public function testAdditionalPathAfterInitialRegister() {
        Loader::register();
        $this->assertEqual( Loader::getLookupPath(), array(
        THINKUP_WEBAPP_PATH . '_lib/model/',
        THINKUP_WEBAPP_PATH . '_lib/controller/',
        THINKUP_WEBAPP_PATH . '_lib/model/exceptions/',
        ));

        Loader::addPath(THINKUP_ROOT_PATH . 'tests/classes');
        $this->assertEqual( Loader::getLookupPath(), array(
        THINKUP_WEBAPP_PATH . '_lib/model/',
        THINKUP_WEBAPP_PATH . '_lib/controller/',
        THINKUP_WEBAPP_PATH . '_lib/model/exceptions/',
        THINKUP_ROOT_PATH . 'tests/classes'
        ));
    }

    public function assertClassInstantiates($class) {
        try {
            new $class;
            if ( !file_exists(THINKUP_WEBAPP_PATH . 'config.inc.php') ) {
                $this->fail('Missing Configuration File');
            } else {
                $this->pass('Configuration File Exists');
            }
        } catch (Exception $e) {
            if ( !$this->config_file_exists ) {
                $this->pass('Missing Configuration File');
            } else {
                $this->fail('Configuration File Exists But Failed to Load class');
            }
        }
    }
}