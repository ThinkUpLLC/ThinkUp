<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterPluginConfigurationController.php
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
 *
 *
 * Test of TwitterPluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/init.tests.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/controller/class.TwitterPluginConfigurationController.php';

class TestOfTwitterPluginConfigurationController extends ThinkUpUnitTestCase {

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('TwitterPluginConfigurationController class test');
    }

    /**
     * Setup
     */
    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');

        //Add owner
        $q = "INSERT INTO tu_owners SET id=1, full_name='ThinkUp J. User', email='me@example.com',
        is_activated=1, pwd='XXX', activation_code='8888'";
        $this->db->exec($q);

        //Add instance_owner
        $q = "INSERT INTO tu_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);

        //Insert test data into test table
        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev',
        'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);

        //Make public
        $q = "INSERT INTO tu_instances (id, network_user_id, network_username, is_public) VALUES (1, 13, 'ev', 1);";
        $this->db->exec($q);

        //Add a bunch of posts
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES ($counter, 13, 'ev', 
            'Ev Williams', 'avatar.jpg', 'This is post $counter', 'web', '2006-01-01 00:$pseudo_minute:00', ".
            rand(0, 4).", 5);";
            $this->db->exec($q);
            $counter++;
        }
        $_SERVER['SERVER_NAME'] = 'dev.thinkup.com';
        $_SERVER['HTTP_HOST'] = 'http://';
        $_SERVER['REQUEST_URI'] = '';

    }

    /**
     * Tear down
     */
    public function tearDown(){
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
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));

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
     * Test user submission
     */
    public function testAddTwitterUserNoTwitterAuth() {

        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        $_GET["twitter_username"] = "anildash";
        $_GET["p"]="twitter";
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('successmsg'), "Added anildash to ThinkUp.");
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
        $this->assertNoPattern('/save options/', $output); // should have no submit option
        $this->assertNoPattern('/plugin_options_oauth_consumer_secret/', $output); // should have no secret option
        $this->assertNoPattern('/plugin_options_archive_limit/', $output); // should have no limit option
        $this->assertNoPattern('/plugin_options_oauth_consumer_key/', $output); // should have no key option
        $this->assertPattern('/var is_admin = false/', $output); // not a js admin

        //app not configured
        $options_arry[0]->truncateTable('plugin_options');
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    /**
     * Test config isa admin
     */
    public function testConfigOptionsIsAdmin() {

        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertPattern('/save options/', $output); // should have no submit option
        $this->assertPattern('/plugin_options_oauth_consumer_secret/', $output); // should have secret option
        $this->assertPattern('/plugin_options_archive_limit/', $output); // should have limit option
        $this->assertPattern('/plugin_options_oauth_consumer_key/', $output); // should have key option
        $this->assertPattern('/var is_admin = true/', $output); // is a js admin

        //app not configured
        $options_arry[0]->truncateTable('plugin_options');
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    /**
     * build plugin option values
     */
    private function buildPluginOptions() {
        $plugin_options1 =
        FixtureBuilder::build('plugin_options',
        array('plugin_id' => 1, 'option_name' => 'oauth_consumer_key', 'option_value' => "1234") );
        $plugin_options2 =
        FixtureBuilder::build('plugin_options',
        array('plugin_id' => 1, 'option_name' => 'oauth_consumer_secret', 'option_value' => "12345") );
        return array($plugin_options1, $plugin_options2);

    }
}
