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

    public function testGetMultiplierAdverb() {
        $terms = new InsightTerms('youtube');
        $this->assertEqual($terms->getMultiplierAdverb(1), '1x');
        $this->assertEqual($terms->getMultiplierAdverb(2), 'double');
        $this->assertEqual($terms->getMultiplierAdverb(3), 'triple');
        $this->assertEqual($terms->getMultiplierAdverb(4), 'quadruple');
        $this->assertEqual($terms->getMultiplierAdverb(5), '5x');
        $this->assertEqual($terms->getMultiplierAdverb(1), '1x');
        $this->assertEqual($terms->getMultiplierAdverb(0.5), 'half');
        $this->assertEqual($terms->getMultiplierAdverb(0.3), 'a third of');
        $this->assertEqual($terms->getMultiplierAdverb(0.25), 'a quarter of');
        $this->assertEqual($terms->getMultiplierAdverb(0.1), '0.1x');
    }

    public function testGetOccurrencesAdverb() {
        $terms = new InsightTerms('youtube');
        $this->assertEqual($terms->getOccurrencesAdverb(1), 'once');
        $this->assertEqual($terms->getOccurrencesAdverb(2), 'twice');
        $this->assertEqual($terms->getOccurrencesAdverb(3), '3 times');
    }

    public function testGetSyntacticTimeDifference() {
        $delta_1 = 60 * 60 * 3; // 3 hours
        $delta_2 = 60 * 6; // 6 minutes
        $delta_3 = 60 * 60 * 24 * 4; // 4 days
        $delta_4 = 60 * 60 * 24; // 1 day

        $result_1 = InsightTerms::getSyntacticTimeDifference($delta_1);
        $result_2 = InsightTerms::getSyntacticTimeDifference($delta_2);
        $result_3 = InsightTerms::getSyntacticTimeDifference($delta_3);
        $result_4 = InsightTerms::getSyntacticTimeDifference($delta_4);

        $this->assertEqual($result_1, '3 hours');
        $this->assertEqual($result_2, '6 minutes');
        $this->assertEqual($result_3, '4 days');
        $this->assertEqual($result_4, '1 day');
    }

    public function testGetPhraseForAddingAsFriend() {
        $terms = new InsightTerms('google+');

        $result_1 = $terms->getPhraseForAddingAsFriend('testeriffic');

        $terms = new InsightTerms('facebook');

        $result_2 = $terms->getPhraseForAddingAsFriend('testeriffic');

        $terms = new InsightTerms('twitter');

        $result_3 = $terms->getPhraseForAddingAsFriend('@testeriffic');

        $this->assertEqual($result_1, "added testeriffic to new circles");
        $this->assertEqual($result_2, "added testeriffic as a friend");
        $this->assertEqual($result_3, "followed @testeriffic");
    }
}
