<?php
/**
 *
 * ThinkUp/tests/TestOfLoader.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
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
 * Test of Loader class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfLoader extends ThinkUpBasicUnitTestCase {

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
        THINKUP_WEBAPP_PATH . '_lib/',
        THINKUP_WEBAPP_PATH . '_lib/model/',
        THINKUP_WEBAPP_PATH . "_lib/dao/",
        THINKUP_WEBAPP_PATH . '_lib/controller/',
        THINKUP_WEBAPP_PATH . '_lib/exceptions/'
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
        THINKUP_WEBAPP_PATH . '_lib/',
        THINKUP_WEBAPP_PATH . '_lib/model/',
        THINKUP_WEBAPP_PATH . "_lib/dao/",
        THINKUP_WEBAPP_PATH . '_lib/controller/',
        THINKUP_WEBAPP_PATH . '_lib/exceptions/',
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
        THINKUP_WEBAPP_PATH . '_lib/',
        THINKUP_WEBAPP_PATH . '_lib/model/',
        THINKUP_WEBAPP_PATH . "_lib/dao/",
        THINKUP_WEBAPP_PATH . '_lib/controller/',
        THINKUP_WEBAPP_PATH . '_lib/exceptions/',
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
        $this->assertClassInstantiates('User');

        $this->assertIsA(new PluginRegistrarCrawler, 'PluginRegistrarCrawler');
        $this->assertIsA(new DAOFactory, 'DAOFactory');

        $this->assertIsA(Config::getInstance(), 'Config');
        $this->assertIsA(Logger::getInstance('/tmp/test.log'), 'Logger');
    }

    public function testAdditionalPathAfterInitialRegister() {
        Loader::register();
        $this->assertEqual( Loader::getLookupPath(), array(
        THINKUP_WEBAPP_PATH . '_lib/',
        THINKUP_WEBAPP_PATH . '_lib/model/',
        THINKUP_WEBAPP_PATH . "_lib/dao/",
        THINKUP_WEBAPP_PATH . '_lib/controller/',
        THINKUP_WEBAPP_PATH . '_lib/exceptions/',
        ));

        Loader::addPath(THINKUP_ROOT_PATH . 'tests/classes');
        $this->assertEqual( Loader::getLookupPath(), array(
        THINKUP_WEBAPP_PATH . '_lib/',
        THINKUP_WEBAPP_PATH . '_lib/model/',
        THINKUP_WEBAPP_PATH . "_lib/dao/",
        THINKUP_WEBAPP_PATH . '_lib/controller/',
        THINKUP_WEBAPP_PATH . '_lib/exceptions/',
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

    public function testDefinePathConstants() {
        Loader::definePathConstants();

        $this->assertTrue( defined('THINKUP_ROOT_PATH') );
        $this->assertTrue( is_readable(THINKUP_ROOT_PATH) );
        $this->debug(THINKUP_ROOT_PATH);

        $this->assertTrue( defined('THINKUP_WEBAPP_PATH') );
        $this->assertTrue( is_readable(THINKUP_WEBAPP_PATH) );
        $this->debug(THINKUP_WEBAPP_PATH);
    }

    public function testAddSpecialClass() {
        // SimpleTest can't catch fatal errors so this assertion doesn't work
        // $this->expectError();
        // $lookup_test = new ConsumerUserStream();

        Loader::addSpecialClass('ConsumerUserStream', 'plugins/twitterrealtime/model/class.ConsumerUserStream.php');
        $special_classes = Loader::getSpecialClasses();
        $this->assertEqual( Loader::getSpecialClasses(),
        array(
        'Smarty'=>THINKUP_WEBAPP_PATH . '_lib/extlib/Smarty-2.6.26/libs/Smarty.class.php',
        'ConsumerUserStream'=>THINKUP_WEBAPP_PATH . 'plugins/twitterrealtime/model/class.ConsumerUserStream.php'
        ));
        //shouldn't throw a not found error
        $lookup_test = new ConsumerUserStream('username', 'password');
    }
}
