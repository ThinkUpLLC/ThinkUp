<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookPlugin.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}

require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookPlugin.php';

class TestOfFacebookPlugin extends ThinkUpUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('FacebookPlugin class test');
    }

    public function setUp() {
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('facebook', 'FacebookPlugin');
        $webapp->setActivePlugin('facebook');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testMenuItemRegistration() {
        $webapp = Webapp::getInstance();
        $logger = Logger::getInstance();
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $menus = $webapp->getDashboardMenu($instance);
        $posts_menu = $menus[0]->items;

        $this->assertEqual(sizeof($posts_menu), 3, "Test number of post tabs");
        $first_post_tab = $posts_menu[0];
        $this->assertEqual($first_post_tab->short_name, "all_facebook_posts", "Test short name of first post tab");
        $this->assertEqual($first_post_tab->name, "All", "Test name of first post tab");
        $this->assertEqual($first_post_tab->description, "", "Test description of first post tab");

        $first_post_tab_datasets = $first_post_tab->getDatasets();
        $first_post_tab_dataset = $first_post_tab_datasets[0];
        $this->assertEqual($first_post_tab_dataset->name, "all_facebook_posts",
        "Test first post tab's first dataset name");
        $this->assertEqual($first_post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($first_post_tab_dataset->dao_method_name, "getAllPosts",
        "Test first post tab's first dataset fetching method");
        $logger->close();
    }
}