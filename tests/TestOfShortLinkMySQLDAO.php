<?php
/**
 *
 * ThinkUp/tests/TestOfShortLinkMySQLDAO.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfShortLinkMySQLDAO extends ThinkUpUnitTestCase {

    public function testCreateNewShortLinkDAO() {
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'ShortLinkMySQLDAO');
    }

    public function testInsert() {
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $result = $dao->insert(12, 'http://t.co/12');
        $this->assertEqual($result, 1);

        $sql = "SELECT * FROM " . $this->table_prefix . 'links_short';
        $stmt = ShortLinkMySQLDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        $this->assertEqual(count($data), 1);
        $data = $data[0];
        $this->assertEqual($data['id'], 1);
        $this->assertEqual($data['link_id'], 12);
        $this->assertEqual($data['short_url'], 'http://t.co/12');
        $this->assertEqual($data['click_count'], 0);
    }

    public function testGetLinksToUpdate() {
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $result = $dao->insert(12, 'http://bit.ly/12');
        $result = $dao->insert(11, 'http://bit.ly/11');
        $result = $dao->insert(10, 'http://t.co/10');

        $result = $dao->getLinksToUpdate('http://bit.ly');
        $this->assertIsA($result, 'Array');
        $this->assertEqual(sizeof($result), 2);
    }

    public function testSaveClickCount() {
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $result = $dao->insert(12, 'http://bit.ly/12');
        $result = $dao->insert(11, 'http://bit.ly/11');
        $result = $dao->insert(10, 'http://t.co/10');

        $result = $dao->saveClickCount('http://bit.ly/12', 100);
        $this->assertEqual($result, 1);
    }

    public function testGetRecentClickStats() {
        //build posts and links
        $counter = 1;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        while ($counter < 14) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
            'author_user_id'=>13, 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>'web', 'pub_date'=>'2006-01-01 00:'. $pseudo_minute.':00',
            'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter',
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

            $builders[] = FixtureBuilder::build('links', array('id'=>$counter, 'post_key'=>$counter,
            'short_url'=>'http://bit.ly/blah', 'expanded_url'=>'http://expandedurl.com/asfasdfadsf/adsfa'
            ));

            $builders[] = FixtureBuilder::build('links_short', array('id'=>$counter, 'link_id'=>$counter,
            'short_url'=>'http://bit.ly/blah'.$counter, 'click_count'=>$counter+2
            ));
            $counter++;
        }
        $instance = new Instance();
        $instance->network_username = 'ev';
        $instance->network = 'twitter';
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $result = $dao->getRecentClickStats($instance);
        $this->assertNotNull($result);
        $this->assertIsA($result, 'Array');
        $this->assertEqual(sizeof($result), 10);
    }

    public function testDoesHaveClicksSinceDateWithLinks() {
        //build posts and links
        $counter = 1;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        while ($counter < 14) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
            'author_user_id'=>13, 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>'web', 'pub_date'=>'-'.$counter.'d', 'reply_count_cache'=>0, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

            $builders[] = FixtureBuilder::build('links', array('id'=>$counter, 'post_key'=>$counter,
            'short_url'=>'http://bit.ly/blah', 'expanded_url'=>'http://expandedurl.com/asfasdfadsf/adsfa'
            ));

            $builders[] = FixtureBuilder::build('links_short', array('id'=>$counter, 'link_id'=>$counter,
            'short_url'=>'http://bit.ly/blah'.$counter, 'click_count'=>$counter+2
            ));
            $counter++;
        }
        $instance = new Instance();
        $instance->network_username = 'ev';
        $instance->network = 'twitter';
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $result = $dao->doesHaveClicksSinceDate($instance, 5);
        $this->assertTrue($result);

        $result = $dao->doesHaveClicksSinceDate($instance, 5, '2011-01-01');
        $this->assertFalse($result);
    }

    public function testDoesHaveClicksSinceDateNoLinks() {
        //build posts and links
        $counter = 1;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        while ($counter < 14) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
            'author_user_id'=>13, 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>'web', 'pub_date'=>'-'.$counter.'d', 'reply_count_cache'=>0, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }
        $instance = new Instance();
        $instance->network_username = 'ev';
        $instance->network = 'twitter';
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $result = $dao->doesHaveClicksSinceDate($instance, 5);
        $this->assertFalse($result);
    }

    public function testGetHighestClickCount() {
        $counter = 1;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        while ($counter < 14) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
            'author_user_id'=>13, 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>'web', 'pub_date'=>'-'.$counter.'d', 'reply_count_cache'=>0, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

            $builders[] = FixtureBuilder::build('links', array('id'=>$counter, 'post_key'=>$counter,
            'short_url'=>'http://bit.ly/blah', 'expanded_url'=>'http://expandedurl.com/asfasdfadsf/adsfa'
            ));

            $builders[] = FixtureBuilder::build('links_short', array('id'=>$counter, 'link_id'=>$counter,
            'short_url'=>'http://bit.ly/blah'.$counter, 'click_count'=>$counter+2
            ));
            $counter++;
        }
        $instance = new Instance();
        $instance->network_username = 'ev';
        $instance->network = 'twitter';
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $result = $dao->getHighestClickCount($instance, 7);
        $this->assertEqual($result, 9);

        $result = $dao->getHighestClickCount($instance, 14);
        $this->assertEqual($result, 15);
    }

    public function testGetHighestClickCountByLinkID() {
        $counter = 1;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        while ($counter < 14) {
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
            'author_user_id'=>13, 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>'web', 'pub_date'=>'-'.$counter.'d', 'reply_count_cache'=>0, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

            $builders[] = FixtureBuilder::build('links', array('id'=>$counter, 'post_key'=>$counter,
            'short_url'=>'http://bit.ly/blah', 'expanded_url'=>'http://expandedurl.com/asfasdfadsf/adsfa'
            ));

            $builders[] = FixtureBuilder::build('links_short', array('id'=>$counter, 'link_id'=>$counter,
            'short_url'=>'http://bit.ly/blah'.$counter, 'click_count'=>$counter+2
            ));
            $counter++;
        }
        $instance = new Instance();
        $instance->network_username = 'ev';
        $instance->network = 'twitter';
        $dao = DAOFactory::getDAO('ShortLinkDAO');
        $result = $dao->getHighestClickCountByLinkID(7);
        $this->assertEqual($result, 9);

        $result = $dao->getHighestClickCount(17);
        $this->assertNull($result);
    }
}