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
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

class TestOfURLProcessor extends ThinkUpUnitTestCase {
    /**
     * @var Logger
     */
    var $logger;
    /**
     * @var str
     */
    var $faux_data_path;

    public function setUp() {
        $this->logger = Logger::getInstance();
        $this->faux_data_path = THINKUP_ROOT_PATH. 'tests/data/URLProcessor';
        parent::setUp();
    }

    public function tearDown() {
        $this->logger->close();
        parent::tearDown();
    }

    public function testProcessPostURLs() {
        $network = 'twitter';
        //Twitpic
        $post_id = 100;
        $post_text = "This is a Twitpic post http://twitpic.com/blah Yay!";
        URLProcessor::processPostURLs($post_text, $post_id, $network, $this->logger);

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://twitpic.com/blah');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://twitpic.com/blah');
        $this->assertEqual($result->expanded_url, 'http://twitpic.com/blah');
        $this->assertEqual($result->image_src, 'http://twitpic.com/show/thumb/blah');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 100);
        $this->assertEqual($result->network, 'twitter');

        //Yfrog
        $post_id = 101;
        $post_text = "This is a Yfrog post http://yfrog.com/blah Yay!";
        URLProcessor::processPostURLs($post_text, $post_id, $network, $this->logger);

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://yfrog.com/blah');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://yfrog.com/blah');
        $this->assertEqual($result->expanded_url, 'http://yfrog.com/blah');
        $this->assertEqual($result->image_src, 'http://yfrog.com/blah.th.jpg');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 101);
        $this->assertEqual($result->network, 'twitter');

        //Twitgoo
        $post_id = 102;
        $post_text = "This is a Twitgoo post http://twitgoo.com/blah Yay!";
        URLProcessor::processPostURLs($post_text, $post_id, $network, $this->logger);

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://twitgoo.com/blah');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://twitgoo.com/blah');
        $this->assertEqual($result->expanded_url, 'http://twitgoo.com/blah');
        $this->assertEqual($result->image_src, 'http://twitgoo.com/show/thumb/blah');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 102);
        $this->assertEqual($result->network, 'twitter');

        //test facebook
        $network = 'facebook';

        //Picplz
        $post_id = 103;
        $post_text = "This is a Picplz post http://picplz.com/blah Yay!";
        URLProcessor::processPostURLs($post_text, $post_id, $network, $this->logger);

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://picplz.com/blah');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://picplz.com/blah');
        $this->assertEqual($result->expanded_url, 'http://picplz.com/blah');
        $this->assertEqual($result->image_src, 'http://picplz.com/blah/thumb/');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 103);
        $this->assertEqual($result->network, 'facebook');

        // instagr.am
        // check first with ending slash in URL (which the URLs 'should' include)
        $post_id = 104;
        $post_text = "This is an instagram post http:/instagr.am/blah/ Yay!";
        URLProcessor::processPostURLs($post_text, $post_id, $network, $this->logger);
        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://instagr.am/blah/');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://instagr.am/blah/');
        $this->assertEqual($result->expanded_url, 'http://instagr.am/blah/');
        $this->assertEqual($result->image_src, 'http://instagr.am/blah/media/');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 104);
        $this->assertEqual($result->network, 'facebook');

        // check w/out ending slash also just in case
        $post_id = 105;
        $post_text = "This is an instagram post http:/instagr.am/blah Yay!";
        URLProcessor::processPostURLs($post_text, $post_id, $network, $this->logger);
        $result = $link_dao->getLinkByUrl('http://instagr.am/blah');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://instagr.am/blah');
        $this->assertEqual($result->expanded_url, 'http://instagr.am/blah');
        $this->assertEqual($result->image_src, 'http://instagr.am/blah/media/');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 105);
        $this->assertEqual($result->network, 'facebook');

        //Flic.kr
        $post_id = 106;
        $post_text = "This is a Flickr post http://flic.kr/blah Yay!";
        URLProcessor::processPostURLs($post_text, $post_id, $network, $this->logger);

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://flic.kr/blah');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://flic.kr/blah');
        //still need to expand the flic.kr link
        $this->assertEqual($result->expanded_url, '');
        $this->assertEqual($result->image_src, '');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 106);
        $this->assertEqual($result->network, 'facebook');

        $post_id = 107;
        $post_text = "This is a post with a curly quote closing the link http://t.co/2JVSpi5Ó yo";
        URLProcessor::processPostURLs($post_text, $post_id, $network, $this->logger);

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://t.co/2JVSpi5');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://t.co/2JVSpi5');
        $this->assertEqual($result->expanded_url, '');
        $this->assertEqual($result->image_src, '');
        $this->assertEqual($result->title, '');
        $this->assertEqual($result->post_id, 107);
        $this->assertEqual($result->network, 'facebook');
    }
}
