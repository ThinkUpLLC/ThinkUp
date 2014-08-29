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

    public function testInsightTermsForYouTube() {
        $terms = new InsightTerms('youtube');

        $count = 3;
        $text = "Your ".$terms->getNoun('post')." got ".$count." ".$terms->getNoun('reply', ($count > 1))
        ." from 2 ".$terms->getNoun('follower', InsightTerms::PLURAL);

        $this->assertNotNull($terms);
        $this->assertEqual($text, "Your video got 3 comments from 2 viewers");
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


    public function testGetProcessedText() {
        $twitter_terms = new InsightTerms('twitter');
        $result = $twitter_terms->getProcessedText('%posts %posted %post %likes %liked %like %reply %replies');
        $this->assertEqual('tweets tweeted tweet favorites favorited favorite reply replies', $result);
        $result = $twitter_terms->getProcessedText('%retweets %retweet %followers %follower %shared');
        $this->assertEqual('retweets retweet followers follower retweeted', $result);

        $fb_terms = new InsightTerms('facebook');
        $result = $fb_terms->getProcessedText('%posts %posted %post %likes %liked %like %reply %replies');
        $this->assertEqual('status updates posted status update likes liked like comment comments', $result);
        $result = $fb_terms->getProcessedText('%retweets %retweet %followers %follower %shared');
        $this->assertEqual('reshares reshare friends friend reshared', $result);


        $result = $twitter_terms->getProcessedText('%animal %posted %thing', array('animal'=>'fox','thing'=>'video'));
        $this->assertEqual('fox tweeted video', $result);

        $result = $twitter_terms->getProcessedText('%animal %posted %thing');
        $this->assertEqual('%animal tweeted %thing', $result);
    }

    public function testSwapInSecondPerson() {
        $terms = new InsightTerms('twitter');

        $username = 'buffysummers';
        $text = "2 interesting people followed @buffysummers.";
        $new_text = "2 interesting people followed you.";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);

        $username = 'buffysummers';
        $text = "@buffysummers has passed 1 million viewers!";
        $new_text = "You've passed 1 million viewers!";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);

        $terms = new InsightTerms('facebook');
        $username = 'Buffy Summers';
        $text = "Buffy Summers's status update got 17 comments.";
        $new_text = "Your status update got 17 comments.";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);

        $username = 'Willow Rosenberg';
        $text = "Looks like it will be 8 weeks before Willow Rosenberg reaches 100,000 followers.";
        $new_text = "Looks like it will be 8 weeks before you reach 100,000 followers.";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);

        $username = 'Willow Rosenberg';
        $text = "Willow Rosenberg reaches 100,000 followers in 8 weeks.";
        $new_text = "You reach 100,000 followers in 8 weeks.";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);

        $username = 'Willow Rosenberg';
        $text = "Hey, did you see that Xander Harris followed Willow Rosenberg?";
        $new_text = "Hey, did you see that Xander Harris followed you?";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);

        $username = 'Willow Rosenberg';
        $text = "Where in the world is Willow Rosenberg?";
        $new_text = "Where in the world are you?";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);

        $username = 'Willow Rosenberg';
        $text = "Is Willow Rosenberg the best?";
        $new_text = "Are you the best?";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);

        $username = 'Willow Rosenberg';
        $text = "Willow Rosenberg hasn't replied to Andre Durand in over a year.";
        $new_text = "You haven't replied to Andre Durand in over a year.";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);

        $username = 'Willow Rosenberg';
        $text = "That's why Willow Rosenberg hasn't replied to Andre Durand in over a year.";
        $new_text = "That's why you haven't replied to Andre Durand in over a year.";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);

        $username = 'Willow Rosenberg';
        $text = "5 people thought Willow Rosenberg was worth retweeting";
        $new_text = "5 people thought you were worth retweeting";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);

        $username = 'Willow Rosenberg';
        $text = "Willow Rosenberg was on fire this week!";
        $new_text = "You were on fire this week!";
        $result = $terms->swapInSecondPerson($username, $text);
        $this->assertEqual($result, $new_text);
    }
}
