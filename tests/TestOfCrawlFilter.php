<?php
/**
 *
 * ThinkUp/tests/TestOfCrawlFilter.php
 *
 * Copyright (c) 2013 Eduard Cucurella
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
 * Test of Session
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Eduard Cucurella
 * @author Eduard Cucurella <ecucurella[dot]t[at]tv3[dot]cat>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';


class TestOfCrawlFilter extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }
    
    public function testConstructor() {
        $filter = new CrawlFilter();
        $this->assertTrue(isset($filter));
    }
    
    public function testSetFilterParameters() {
        $this->assertTrue(CrawlFilter::getFilter() == -1);
        $this->assertTrue(CrawlFilter::getSelected() == -1);
        CrawlFilter::setFilterParameters('3','1');
        $this->assertTrue(CrawlFilter::getFilter() == 3);
        $this->assertTrue(CrawlFilter::getSelected() == 1);        
    }

    public function testGetFilter() {
        $this->assertTrue(CrawlFilter::getFilter() == -1);
        CrawlFilter::setFilterParameters('5','1');
        $this->assertTrue(CrawlFilter::getFilter() == 5);
    }

    public function testGetSelected() {
        $this->assertTrue(CrawlFilter::getSelected() == -1);
        CrawlFilter::setFilterParameters('3','0');
        $this->assertTrue(CrawlFilter::getSelected() == 0);
    }

    public function testIsFilterNeeded() {
        $this->assertFalse(CrawlFilter::isFilterNeeded());
        CrawlFilter::setFilterParameters('3','0');
        $this->assertTrue(CrawlFilter::isFilterNeeded());
    }

    public function testSetFilterUsed() {
        $this->assertFalse(CrawlFilter::isFilterNeeded());
        CrawlFilter::setFilterParameters('3','0');
        $this->assertTrue(CrawlFilter::isFilterNeeded());
        CrawlFilter::setFilterUsed();
        $this->assertFalse(CrawlFilter::isFilterNeeded());
    }
}