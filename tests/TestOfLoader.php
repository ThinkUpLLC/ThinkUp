<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of Loader class
 *
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
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
        THINKUP_WEBAPP_PATH . 'model' . DS,
        THINKUP_WEBAPP_PATH . 'controller' . DS,
        THINKUP_WEBAPP_PATH . 'model'. DS . 'exceptions' . DS
        ));

        // check special classes
        $this->assertEqual( Loader::getSpecialClasses(),
        array('Smarty'=>THINKUP_ROOT_PATH . 'extlib' . DS . 'Smarty-2.6.26' . DS .'libs' . DS . 'Smarty.class.php'));
    }

    public function testLoaderRegisterWithStringAdditionalPath() {
        // Loader with string of path as additional path
        $loader = Loader::register(array(THINKUP_ROOT_PATH . 'tests' . DS . 'classes'));

        // check if Loader is registered to spl autoload
        $this->assertTrue($loader, 'Loader is registered to spl autoload');

        // check lookup path with single additionalPath
        $this->assertEqual( Loader::getLookupPath(), array(
        THINKUP_WEBAPP_PATH . 'model' . DS,
        THINKUP_WEBAPP_PATH . 'controller' . DS,
        THINKUP_WEBAPP_PATH . 'model'. DS . 'exceptions' . DS,
        THINKUP_ROOT_PATH . 'tests' . DS . 'classes'
        ));
    }

    public function testLoaderRegisterWithArrayAdditionalPaths() {
        // Loader with array of path as additional path
        $loader = Loader::register(array(
        THINKUP_ROOT_PATH . 'tests',
        THINKUP_ROOT_PATH . 'tests' . DS . 'classes'
        ));

        // check if Loader is registered to spl autoload
        $this->assertTrue($loader, 'Loader is registered to spl autoload');

        // check lookup path with array additionalPath
        $this->assertEqual( Loader::getLookupPath(), array(
        THINKUP_WEBAPP_PATH . 'model' . DS,
        THINKUP_WEBAPP_PATH . 'controller' . DS,
        THINKUP_WEBAPP_PATH . 'model'. DS . 'exceptions' . DS,
        THINKUP_ROOT_PATH . 'tests',
        THINKUP_ROOT_PATH . 'tests' . DS . 'classes'
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
        THINKUP_WEBAPP_PATH . 'model' . DS,
        THINKUP_WEBAPP_PATH . 'controller' . DS,
        THINKUP_WEBAPP_PATH . 'model'. DS . 'exceptions' . DS,
        ));

        Loader::addPath(THINKUP_ROOT_PATH . 'tests' . DS . 'classes');
        $this->assertEqual( Loader::getLookupPath(), array(
        THINKUP_WEBAPP_PATH . 'model' . DS,
        THINKUP_WEBAPP_PATH . 'controller' . DS,
        THINKUP_WEBAPP_PATH . 'model'. DS . 'exceptions' . DS,
        THINKUP_ROOT_PATH . 'tests' . DS . 'classes'
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