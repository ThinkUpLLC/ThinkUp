<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of PluginHook class
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfPluginHook extends ThinkUpBasicUnitTestCase {

    /**
     * Constructor
     */
    function __construct() {
        $this->UnitTestCase('PluginHook class test');
    }

    /**
     * Test registerPlugin
     */
    function testRegisterAndGetPlugin() {
        $test_ph = new TestFauxHookableApp();
        $test_ph->registerPlugin('facebook', "FacebookPlugin");
        $test_ph->registerPlugin('twitter', "TwitterPlugin");
        $test_ph->registerPlugin('flickr', "FlickrPlugin");

        $this->assertEqual($test_ph->getPluginObject("facebook"), "FacebookPlugin");
        $this->assertEqual($test_ph->getPluginObject("twitter"), "TwitterPlugin");
        $this->assertEqual($test_ph->getPluginObject("flickr"), "FlickrPlugin");
    }
    /**
     * Test getPluginObject
     */
    function testGetPluginObjectDoesntExist() {
        $test_ph = new TestFauxHookableApp();
        $this->expectException( new Exception("No plugin object defined for: notregistered") );
        $plugin_obj = $test_ph->getPluginObject("notregistered");
    }

    /**
     * Test registerPerformAppFunction and emit
     * @TODO Test for registering an object which does not exist; currently this causes a PHP fatal error
     */
    function testRegisterPerformAppFunction() {
        //register first, should work
        $test_ph = new TestFauxHookableApp();
        $test_ph->registerPerformAppFunction('TestFauxPlugin');
        $test_ph->performAppFunction();

        //register an object without the right method
        $test_ph->registerPerformAppFunction('TestFauxPluginOne');
        $this->expectException( new Exception("The TestFauxPluginOne object does not have a performAppFunction method.") );
        $test_ph->performAppFunction();
    }
}