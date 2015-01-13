<?php
/**
 *
 * ThinkUp/webapp/plugins/instagram/tests/TestOfInstagramPlugin.php
 *
 * Copyright (c) 2009-2013 Dimosthenis Nikoudis
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
 * Test of InstagramPluginConfigurationController
 *
 * @author Dimosthenis Nikoudis <dnna[at]dnna[dot]gr>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Dimosthenis Nikoudis
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/model/class.InstagramPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/model/class.InstagramCrawler.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/model/class.InstagramAPIAccessor.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/tests/classes/mock.Proxy.php';

class TestOfInstagramPlugin extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('instagram', 'InstagramPlugin');
        $webapp_plugin_registrar->setActivePlugin('instagram');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $plugin = new InstagramPlugin();
        $this->assertIsA($plugin, 'InstagramPlugin');
        $this->assertEqual(count($plugin->required_settings), 2);
        $this->assertFalse($plugin->isConfigured());
    }

    public function testDeactivate() {
        //all instagram accounts should be set to inactive on plugin deactivation
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $logger = Logger::getInstance();
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $instance_builder_1 = FixtureBuilder::build('instances', array('network_username'=>'julie',
        'network'=>'instagram', 'crawler_last_run'=>'-1d', 'is_activated'=>'1', 'is_public'=>'1'));

        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $fb_active_instances = $instance_dao->getAllInstances("DESC", true, "instagram");
        $this->assertEqual(sizeof($fb_active_instances), 1);

        $fb_plugin = new InstagramPlugin();
        $fb_plugin->deactivate();

        $fb_active_instances = $instance_dao->getAllInstances("DESC", true, "instagram");
        $this->assertEqual(sizeof($fb_active_instances), 0);

        $logger->close();
    }
}
