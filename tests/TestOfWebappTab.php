<?php
/**
 *
 * ThinkUp/tests/TestOfWebappTab.php
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
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of WebappTab
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfWebappTab extends ThinkUpBasicUnitTestCase {

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('WebappTab class test');
    }

    /**
     * Set up test
     */
    public function setUp() {
        parent::setUp();
    }

    /**
     * Tear down test
     */
    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $tab = new WebappTab('my_short_name', "Name of My Tab");
        $this->assertEqual($tab->short_name, 'my_short_name');
        $this->assertEqual($tab->name, 'Name of My Tab');
        $this->assertEqual($tab->description, '');
        $this->assertEqual($tab->view_template, 'inline.view.tpl');

        $datasets = $tab->getDatasets();
        $this->assertIsA($datasets, 'array');
        $this->assertEqual(sizeof($datasets), 0);

    }
}