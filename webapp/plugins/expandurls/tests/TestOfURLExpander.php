<?php
/**
 *
 * ThinkUp/webapp/plugins/expandurls/tests/TestOfURLExpander.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * Test of URLExpander
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'plugins/expandurls/model/class.URLExpander.php';

// We don't include this test in the build because it makes live requests to web pages

class TestOfExpandURLsPlugin extends ThinkUpBasicUnitTestCase {
    public function testConstructor() {
        $obj = new URLExpander();
        $this->assertIsA($obj, 'URLExpander');
    }

    public function testGetWebPageDetails() {
        $details = URLExpander::getWebPageDetails('http://google.com');
        print_r($details);

        $details = URLExpander::getWebPageDetails('http://smarterware.org');
        print_r($details);

        $details = URLExpander::getWebPageDetails("http://www.slate.com/blogs/future_tense/2013/08/29/klout_deletes".
        "_tweet_martin_luther_king_would_have_had_an_awesome_klout_score.html?utm_content=buffer9b03f&utm_source=bu".
        "ffer&utm_medium=twitter&utm_campaign=Buffer");
        print_r($details);

        $details = URLExpander::getWebPageDetails('https://twitter.com/LamarrWilson/status/372582861548703745/photo/1');
        print_r($details);

        $details = URLExpander::getWebPageDetails('http://justdelete.me/');
        print_r($details);

        $details = URLExpander::getWebPageDetails('https://stellar.io/x/manage/membership');
        print_r($details);

        $details = URLExpander::getWebPageDetails('https://www.wepay.com/donations/free-barrett-brown');
        print_r($details);
    }
}