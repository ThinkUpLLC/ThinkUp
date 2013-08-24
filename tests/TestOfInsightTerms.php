<?php
/**
 *
 * ThinkUp/tests/TestOfInsightTerms.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * Test of InsightTerms clas
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInsightTerms extends ThinkUpBasicUnitTestCase {
    public function testInsightTermsForTwitter() {
        $terms = new InsightTerms('twitter');

        $text = "Two of your ".$terms->getNoun('friend', InsightTerms::PLURAL)." ".$terms->getVerb('liked')
        ." your ".$terms->getNoun('post');

        $this->assertNotNull($terms);
        $this->assertEqual($text, "Two of your followers favorited your tweet");
    }

    public function testInsightTermsForGooglePlus() {
        $terms = new InsightTerms('google+');

        $count = 2;
        $text = "Two of your ".$terms->getNoun('friend', ($count > 1))." ".$terms->getVerb('liked')
        ." your ".$terms->getNoun('post');

        $this->assertNotNull($terms);
        $this->assertEqual($text, "Two of your friends +1'd your post");
    }

    public function testInsightTermsForFacebook() {
        $terms = new InsightTerms('facebook');

        $count = 3;
        $text = "Your ".$terms->getNoun('post')." got ".$count." ".$terms->getNoun('reply', ($count > 1))
        ." from 2 ".$terms->getNoun('follower', InsightTerms::PLURAL);

        $this->assertNotNull($terms);
        $this->assertEqual($text, "Your status update got 3 comments from 2 friends");
    }
}