<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterPluginConfigurationController.php
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
 *
 * Test of TwitterPluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/tests/classes/mock.TwitterOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/controller/class.TwitterPluginConfigurationController.php';

class TestOfTwitterPluginConfigurationController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitter', 'TwitterPlugin');
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
            'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'author_avatar'=>'avatar.jpg',
            'post_text'=>'This is post'.$counter, 'source'=>'web', 'pub_date'=>'2006-01-01 00:'.$pseudo_minute.':00',
            'reply_count_cache'=>rand(0, 4), 'retweet_count_cache'=>5));
            $counter++;
        }
        $_SERVER['SERVER_NAME'] = 'dev.thinkup.com';
        $_SERVER['HTTP_HOST'] = 'http://';
        $_SERVER['REQUEST_URI'] = '';

        return $builders;
    }

    public function tearDown(){
        $this->builders = null;
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $controller = new TwitterPluginConfigurationController(null, 'twitter');
        $this->assertTrue(isset($controller), 'constructor test');
    }

    /**
     * Test output
     */
    public function testOutputNoParams() {
        // build some options data
        $options_arry = $this->buildPluginOptions();

        //not logged in, no owner set
        $controller = new TwitterPluginConfigurationController(null, 'twitter');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('error_msg'));

        //logged in
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('owner_instances'), 'array', 'Owner instances set');
        $this->assertTrue($v_mgr->getTemplateDataItem('oauthorize_link') != '', 'Authorization link set');
    }

    /**
     * Test config not admin
     */
    public function testConfigOptionsNotAdmin() {
        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertNoPattern('/Save Settings/', $output); // should have no submit option
        $this->assertNoPattern('/plugin_options_oauth_consumer_secret/', $output); // should have no secret option
        $this->assertNoPattern('/plugin_options_archive_limit/', $output); // should have no limit option
        $this->assertNoPattern('/plugin_options_oauth_consumer_key/', $output); // should have no key option
        $this->assertPattern('/var is_admin = false/', $output); // not a js admin
    }

    /**
     * Test config is a admin
     */
    public function testConfigOptionsIsAdmin() {
        $_SERVER['SERVER_NAME'] = 'mytestthinkup';
        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertPattern('/Save Settings/', $output); // should have no submit option
        $this->assertPattern('/plugin_options_oauth_consumer_secret/', $output); // should have secret option
        $this->assertPattern('/plugin_options_archive_limit/', $output); // should have limit option
        $this->assertPattern('/plugin_options_oauth_consumer_key/', $output); // should have key option
        $this->assertPattern('/var is_admin = true/', $output); // is a js admin

        //not SSL by default
        $this->assertNoPattern('/https:\/\/mytestthinkup/', $output);
    }

    /**
     * Test SSL
     */
    public function testConfigOptionsIsAdminWithSSL() {
        $_SERVER['HTTPS'] = true;
        $_SERVER['SERVER_NAME'] = 'mytestthinkup';
        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        $output = $controller->go();

        $expected_pattern = '/Callback URL:
    <small>
      <code style="font-family:Courier;" id="clippy_2988">https:\/\//';
        $this->assertPattern($expected_pattern, $output);

        $this->assertNoPattern('/http:\/\/mytestthinkup/', $output);
    }

    public function testLocalhostConversionTo1270001() {
        $_SERVER['HTTPS'] = null;
        $_SERVER['SERVER_NAME'] = 'localhost';
        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        $output = $controller->go();

        $this->assertPattern('/http:\/\/127\.0\.0\.1/', $output);
    }

    /*
     * Test required settings not set
     */
    public function testConfigOptionsMissingRequiredValues() {
        $_SERVER['SERVER_NAME'] = 'mytestthinkup';
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertPattern('/Save Settings/', $output); // should have no submit option
        $this->assertPattern('/plugin_options_oauth_consumer_secret/', $output); // should have secret option
        $this->assertPattern('/plugin_options_archive_limit/', $output); // should have limit option
        $this->assertPattern('/plugin_options_oauth_consumer_key/', $output); // should have key option
        $this->assertPattern('/var is_admin = true/', $output); // is a js admin

        //app not configured
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured

        //not SSL by default
        $this->assertNoPattern('/https:\/\/mytestthinkup/', $output);

        //assert site URL is set so user can configure the app
        $v_mgr = $controller->getViewManager();

        $site_url = $v_mgr->getTemplateDataItem('thinkup_site_url');
        $this->assertEqual($site_url, Utils::getApplicationURL());
        $twitter_app_name = $v_mgr->getTemplateDataItem('twitter_app_name');
        $this->assertEqual($twitter_app_name, "ThinkUp ". $_SERVER['SERVER_NAME']);
    }

    /**
     * Test csrf token
     */
    public function testForDeleteCSRFToken() {
        $_SERVER['SERVER_NAME'] = 'mytestthinkup';
        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true, true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        $output = $controller->go();
        $this->assertPattern('/name="csrf_token" value="'. self::CSRF_TOKEN . '"/', $output);
    }

    public function testLocalhostOAuthCallbackLink() {
        require THINKUP_WEBAPP_PATH.'config.inc.php';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $options_array = $this->buildPluginOptions();

        $controller = new TwitterPluginConfigurationController(null, 'twitter');
        $config = Config::getInstance();

        //From logged in
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();

        //Check if a URL was passed
        $auth_link = $v_mgr->getTemplateDataItem('oauthorize_link');
        $this->assertEqual("test_auth_URL_".urlencode("http://127.0.0.1".$THINKUP_CFG['site_root_path'].
        "plugins/twitter/auth.php"), $auth_link);
    }

    /**
     * build plugin option values
     */
    private function buildPluginOptions() {
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-1';
        $plugin_options1 =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'oauth_consumer_key', 'option_value' => "1234") );
        $plugin_options2 =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'oauth_consumer_secret', 'option_value' => "12345") );
        $plugin_options3 =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'num_twitter_errors', 'option_value' => "5") );
        return array($plugin_options1, $plugin_options2, $plugin_options3);
    }
}
