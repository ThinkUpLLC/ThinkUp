<?php
/**
 *
 * ThinkUp/webapp/plugins/flickrthumbnails/tests/TestOfFlickrThumbnailsPluginConfigurationController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Dwi Widiastuti
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
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';

require_once THINKUP_ROOT_PATH.
'webapp/plugins/flickrthumbnails/controller/class.FlickrThumbnailsPluginConfigurationController.php';

/**
 * Test of FlickrThumbnailsPluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfFlickrThumbnailsPluginConfigurationController extends ThinkUpUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('FlickrThumbnailsPluginConfigurationController class test');
    }
    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('flickr', 'FlickrThumbnailsPlugin');

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

    }
    public function testConstructor() {
        $controller = new FlickrThumbnailsPluginConfigurationController(null, 'flickrthumbnails');
        $this->assertTrue(isset($controller), 'constructor test');
    }

    public function testOutputNoParams() {
        // build some options data
        $options_arry = $this->buildPluginOptions();

        //not logged in, no owner set
        $controller = new FlickrThumbnailsPluginConfigurationController(null, 'flickrthumbnails');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));

        //logged in
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FlickrThumbnailsPluginConfigurationController($owner, 'flickrthumbnails');
        $output = $controller->go();
        $this->assertPattern('/Flickr API key/', $output);
    }

    /**
     * build plugin option values
     */
    private function buildPluginOptions() {
        $plugin_options1 =
        FixtureBuilder::build('plugin_options',
        array('plugin_id' => 1, 'option_name' => 'flickr_api_key', 'option_value' => "dummykey") );
        return array($plugin_options1);
    }
}
