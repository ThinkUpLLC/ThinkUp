<?php
/**
 *
 * ThinkUp/tests/TestOfPost.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of Post class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestOfPost extends ThinkUpBasicUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('Post class test');
    }

    public function testExtractURLs() {
        $testme= "blah blah blah http:///badurl.com d http://bit.ly and http://example.org";
        $urls = Post::extractURLs($testme);
        $expected = array ('http:///badurl.com', 'http://bit.ly', 'http://example.org');
        $this->assertIdentical($expected, $urls);
        //@TODO Finesse the regex to NOT match URLs with triple slashes, http:///badurl.com
    }
}