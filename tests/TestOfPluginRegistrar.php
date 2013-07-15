<?php
/**
 *
 * ThinkUp/tests/TestOfPluginRegistrar.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Test of PluginRegistrar class
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfPluginRegistrar extends ThinkUpBasicUnitTestCase {

    /**
     * Test registerPlugin
     */
    public function testRegisterAndGetPlugin() {
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
    public function testGetPluginObjectDoesntExist() {
        $test_ph = new TestFauxHookableApp();
        $this->expectException( new PluginNotFoundException("notregistered") );
        $plugin_obj = $test_ph->getPluginObject("notregistered");
    }

    /**
     * Test registerPerformAppFunction and emit
     * @TODO Test for registering an object which does not exist; currently this causes a PHP fatal error
     */
    public function testRegisterPerformAppFunction() {
        //register first, should work
        $test_ph = new TestFauxHookableApp();
        $test_ph->registerPerformAppFunction('TestFauxPlugin');
        $test_ph->performAppFunction();

        //register an object without the right method
        $test_ph->registerPerformAppFunction('TestFauxPluginOne');
        $this->expectException(new
        Exception("The TestFauxPluginOne object does not have a performAppFunction function."));
        $test_ph->performAppFunction();
    }

    public function testOrderOfPlugins() {
        $plugin_registrar = new PluginRegistrarCrawler();
        // Register some plugins that should run before the insight generator
        $plugin_registrar->registerCrawlerPlugin('TwitterPlugin', true);
        $plugin_registrar->registerCrawlerPlugin('FacebookPlugin', true);
        // Register the insight generator
        $plugin_registrar->registerCrawlerPlugin('InsightsGeneratorPlugin', true);
        // Register some that should run after the insight generator
        $plugin_registrar->registerCrawlerPlugin('FoursquarePlugin', false);
        $plugin_registrar->registerCrawlerPlugin('YouTubePlugin', false);
        // Order the plugins and check they're in the right order
        $plugin_registrar->orderPlugins('crawl');
        $plugins = $plugin_registrar->getObjectFunctionCallbacks();
        $this->assertNotNull($plugins);
        $crawl_plugins = $plugins['crawl'];
        $this->assertEqual($crawl_plugins[0][0], 'TwitterPlugin');
        $this->assertEqual($crawl_plugins[1][0], 'FacebookPlugin');
        $this->assertEqual($crawl_plugins[2][0], 'InsightsGeneratorPlugin');
        $this->assertEqual($crawl_plugins[3][0], 'FoursquarePlugin');
        $this->assertEqual($crawl_plugins[4][0], 'YouTubePlugin');
    }

    public function testOrderOfPluginsWithoutInsightGenerator() {
        $plugin_registrar = new PluginRegistrarCrawler();
        $plugin_registrar->registerCrawlerPlugin('TwitterPlugin', true);
        $plugin_registrar->registerCrawlerPlugin('FacebookPlugin', true);
        $plugin_registrar->registerCrawlerPlugin('FoursquarePlugin', false);
        $plugin_registrar->registerCrawlerPlugin('YouTubePlugin', false);
        // Order the plugins and check they're in the right order
        $plugin_registrar->orderPlugins('crawl');
        $plugins = $plugin_registrar->getObjectFunctionCallbacks();
        $this->assertNotNull($plugins);
        $crawl_plugins = $plugins['crawl'];
        $this->assertEqual($crawl_plugins[0][0], 'TwitterPlugin');
        $this->assertEqual($crawl_plugins[1][0], 'FacebookPlugin');
        $this->assertEqual($crawl_plugins[2][0], 'FoursquarePlugin');
        $this->assertEqual($crawl_plugins[3][0], 'YouTubePlugin');
    }
}
