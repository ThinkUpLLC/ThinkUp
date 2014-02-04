<?php
/**
 *
 * ThinkUp/webapp/plugins/expandurls/tests/TestOfExpandURLsPluginConfigurationController.php
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
 *
 * Test of ExpandURLsPluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';

require_once THINKUP_ROOT_PATH.
'webapp/plugins/expandurls/controller/class.ExpandURLsPluginConfigurationController.php';

class TestOfExpandURLsPluginConfigurationController extends ThinkUpUnitTestCase {
    public function setUp(){
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('expandurls', 'ExpandURLsPlugin');
        $this->builders = self::buildData();
    }

    protected function buildData(){
        $builders = array();

        //Add owner
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1, 'pwd'=>'XXX', 'activation_code'=>8888));

        //Add instance_owner
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));

        //Insert test data into test table
        $builders[] = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'last_updated'=>'2005-01-01 13:48:05'));

        //Make public
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>13,
        'network_username'=>'ev', 'is_public'=>1));

        //Add a bunch of posts
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter, 'author_user_id'=>13,
            'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'avatar'=>'avatar.jpg',
            'post_text'=>'This is post'.$counter, 'source'=>'web', 'pub_date'=>'2006-01-01 00:'.$pseudo_minute.':00',
            'reply_count_cache'=>rand(0, 4), 'retweet_count_cache'=>5));
            $counter++;
        }
        return $builders;
    }

    public function tearDown(){
        $this->builders = null;
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new ExpandURLsPluginConfigurationController(null, 'flickrthumbnails');
        $this->assertTrue(isset($controller), 'constructor test');
    }

    public function testOutputNoParams() {
        //not logged in, no owner set
        $controller = new ExpandURLsPluginConfigurationController(null, 'flickrthumbnails');
        $output = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);

        //logged in
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new ExpandURLsPluginConfigurationController($owner, 'flickrthumbnails');
        $output = $controller->go();
        $this->assertPattern('/Flickr API key/', $output);
        $this->assertPattern('/Bit.ly API key/', $output);
        $this->assertPattern('/Bit.ly Username/', $output);
    }

    /**
     * Test config not admin
     */
    public function testConfigOptionsNotAdmin() {
        // build some options data
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new ExpandURLsPluginConfigurationController($owner, 'flickrthumbnails');
        $output = $controller->go();
        $this->debug($output);
        // we have a text form element with proper data
        $this->assertNoPattern('/Save Settings/', $output); // should have no submit option
        $this->assertNoPattern('/plugin_options_error_flickr_api_key/', $output); // should have no api key
        $this->assertNoPattern('/plugin_options_error_bitly_api_key/', $output); // should have no api key
        $this->assertNoPattern('/plugin_options_error_bitly_login/', $output); // should have no login name
        $this->assertPattern('/var is_admin = false/', $output); // not a js admin

        //app not configured
        $controller = new ExpandURLsPluginConfigurationController($owner, 'flickrthumbnails');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = true/', $output); //is not configured, no required values
    }

    /**
     * Test config isa admin
     */
    public function testConfigOptionsIsAdmin() {
        // build some options data
        $this->simulateLogin('me@example.com', $isadmin = true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new ExpandURLsPluginConfigurationController($owner, 'flickrthumbnails');
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertPattern('/Save Settings/', $output); // should have submit option
        $this->assertPattern('/plugin_options_error_flickr_api_key/', $output); // should have api key option
        $this->assertPattern('/plugin_options_error_bitly_api_key/', $output); // should have api key option
        $this->assertPattern('/plugin_options_error_bitly_login/', $output); // should have login name option
        $this->assertPattern('/var is_admin = true/', $output); // is a js admin

        //app not configured
        $controller = new ExpandURLsPluginConfigurationController($owner, 'flickrthumbnails');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = true/', $output); //is not configured, no required values
    }
}
