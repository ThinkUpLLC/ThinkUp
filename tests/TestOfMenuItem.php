<?php
/**
 *
 * ThinkUp/tests/TestOfMenuItem.php
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
 * Test of MenuItem
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfMenuItem extends ThinkUpBasicUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $menu_item = new MenuItem("Name of My Menu Item");
        $this->assertEqual($menu_item->name, 'Name of My Menu Item');
        $this->assertEqual($menu_item->description, '');
        $this->assertEqual($menu_item->view_template, 'inline.view.tpl');
        $this->assertEqual($menu_item->parent, null);

        $datasets = $menu_item->getDatasets();
        $this->assertIsA($datasets, 'array');
        $this->assertEqual(sizeof($datasets), 0);

        $menu_item1 = new MenuItem("Name of My Menu Item 1", 'descriptive text', 'mytemplate.tpl', "my-parent");
        $this->assertEqual($menu_item1->name, 'Name of My Menu Item 1');
        $this->assertEqual($menu_item1->description, 'descriptive text');
        $this->assertEqual($menu_item1->view_template, 'mytemplate.tpl');
        $this->assertEqual($menu_item1->parent, "my-parent");
    }
}