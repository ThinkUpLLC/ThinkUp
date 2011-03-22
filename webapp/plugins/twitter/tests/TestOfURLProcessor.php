<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfURLProcessor.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Amy Unruh
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 * @author Gina Trapani
 * @author Amy Unruh
 */
require_once dirname(__FILE__).'/../../../../tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.URLProcessor.php';

class TestOfURLProcessor extends ThinkUpUnitTestCase {
    /**
     * @var Logger
     */
    var $logger;
    /**
     * @var str
     */
    var $faux_data_path;

    public function __construct() {
        $this->UnitTestCase('URLProcessor class test');
    }

    public function setUp() {
        $this->logger = Logger::getInstance();
        $this->faux_data_path = THINKUP_ROOT_PATH. 'webapp/plugins/twitter/tests/testdata/URLProcessor';
        parent::setUp();
    }

    public function tearDown() {
        $this->logger->close();
        parent::tearDown();
    }

    public function testProcessTweetURLs() {
        //Twitpic
        $tweet["post_id"] = 100;
        $tweet['post_text'] = "This is a Twitpic post http://twitpic.com/blah Yay!";
        URLProcessor::processTweetURLs($this->logger, $tweet);

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://twitpic.com/blah');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://twitpic.com/blah');
        $this->assertEqual($result->expanded_url, 'http://twitpic.com/show/thumb/blah');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 100);
        $this->assertEqual($result->network, 'twitter');
        $this->assertTrue($result->is_image);

        //Yfrog
        $tweet["post_id"] = 101;
        $tweet['post_text'] = "This is a Yfrog post http://yfrog.com/blah Yay!";
        URLProcessor::processTweetURLs($this->logger, $tweet);

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://yfrog.com/blah');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://yfrog.com/blah');
        $this->assertEqual($result->expanded_url, 'http://yfrog.com/blah.th.jpg');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 101);
        $this->assertEqual($result->network, 'twitter');
        $this->assertTrue($result->is_image);

        //Twitgoo
        $tweet["post_id"] = 102;
        $tweet['post_text'] = "This is a Twitgoo post http://twitgoo.com/blah Yay!";
        URLProcessor::processTweetURLs($this->logger, $tweet);

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://twitgoo.com/blah');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://twitgoo.com/blah');
        $this->assertEqual($result->expanded_url, 'http://twitgoo.com/show/thumb/blah');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 102);
        $this->assertEqual($result->network, 'twitter');
        $this->assertTrue($result->is_image);

        //Picplz
        $tweet["post_id"] = 103;
        $tweet['post_text'] = "This is a Picplz post http://picplz.com/blah Yay!";
        URLProcessor::processTweetURLs($this->logger, $tweet);

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://picplz.com/blah');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://picplz.com/blah');
        $this->assertEqual($result->expanded_url, 'http://picplz.com/blah/thumb/');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 103);
        $this->assertEqual($result->network, 'twitter');
        $this->assertTrue($result->is_image);

        //Flic.kr
        $tweet["post_id"] = 104;
        $tweet['post_text'] = "This is a Flickr post http://flic.kr/blah Yay!";
        URLProcessor::processTweetURLs($this->logger, $tweet);

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://flic.kr/blah');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://flic.kr/blah');
        $this->assertEqual($result->expanded_url, '');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 104);
        $this->assertEqual($result->network, 'twitter');
        $this->assertTrue($result->is_image);
    }

    public function testProcessTweetInstagramURLs() {
        //instagr.am
        $tweet["post_id"] = 105;
        $tweet['post_text'] = "This is an Instagram post:  http://instagr.am/p/oyQ6/ :)";
        URLProcessor::processTweetURLs($this->logger, $tweet);
        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://instagr.am/p/oyQ6/');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://instagr.am/p/oyQ6/');
        $this->assertEqual($result->expanded_url,
        'http://images.instagram.com/media/2010/12/20/f0f411210cc54353be07cf74ceb79f3b_7.jpg');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 105);
        $this->assertEqual($result->network, 'twitter');
        $this->assertTrue($result->is_image);

        // bad instagr.am URL
        $tweet["post_id"] = 106;
        $tweet['post_text'] = "This is an Instagram post with a bad URL:  http://instagr.am/p/oyQ5/ :(";
        URLProcessor::processTweetURLs($this->logger, $tweet);
        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://instagr.am/p/oyQ5/');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://instagr.am/p/oyQ5/');
        $this->assertEqual($result->expanded_url, '');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 106);
        $this->assertEqual($result->network, 'twitter');
        $this->assertFalse($result->is_image);

        // test regexp extraction of image link from html
        $api_call = $this->faux_data_path . "/instagr_am_p_oyQ6";
        $resp = file_get_contents($api_call);
        list($eurl, $is_image) = URLProcessor::extractInstagramImageURL($this->logger, $resp);
        $this->assertEqual($eurl,
        'http://distillery.s3.amazonaws.com/media/2010/12/20/f0f411210cc54353be07cf74ceb79f3b_7.jpg');
        $this->assertTrue($is_image);
    }
}
