<?php
/**
 *
 * ThinkUp/tests/TestOfPhotoMySQLDAO.php
 *
 * Copyright (c) 2013 Dimosthenis Nikoudis, Aaron Kalair, Gina Trapani
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
 * Test of PhotoMySQL DAO implementation
 * @author Dimosthenis Nikoudis, Aaron Kalair, Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Dimosthenis Nikoudis, Aaron Kalair, Gina Trapani
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/dao/class.PhotoMySQLDAO.php';

class TestOfPhotoMySQLDAO extends ThinkUpUnitTestCase {
        public function setUp() {
        parent::setUp();
        $config = Config::getInstance();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    protected function buildData() {
        $builders = array();
        // Add a photo to the database
        $builders[] = FixtureBuilder::build('posts', array(
            'id' => 5,
            'post_id' => '507648000295407216_180890738',
            'author_user_id' => '180890738',
            'author_username' => 'nilakshdas',
            'author_fullname' => 'Nilaksh Das',
            'author_avatar' => 'http://images.ak.instagram.com/profiles/profile_180890738_75sq_1374737148.jpg',
            'post_text' => '#LoseYourself, as usual.',
            'is_protected' => 0,
            'source' => 'undefined',
            'pub_date' => '2013-07-25 12:42:59',
            'network' => 'instagram'
        ));
        $builders[] = FixtureBuilder::build('photos', array(
            'id' => 3,
            'post_key' => 5,
            'filter' => 'Lo-fi',
            'standard_resolution_url' =>
            'http://distilleryimage0.s3.amazonaws.com/a2e8b5f0f4f911e2af6f22000a1f9a09_7.jpg',
            'low_resolution_url' => 'http://distilleryimage0.s3.amazonaws.com/a2e8b5f0f4f911e2af6f22000a1f9a09_6.jpg',
            'thumbnail_url' => 'http://distilleryimage0.s3.amazonaws.com/a2e8b5f0f4f911e2af6f22000a1f9a09_5.jpg'
        ));

        return $builders;
    }

    public function testConstructor() {
        $dao = new PhotoMySQLDAO();
        $this->assertTrue(isset($dao));
        $this->assertIsA($dao, 'PhotoMySQLDAO');
    }

    public function testGetPhotoExists() {
        $dao = new PhotoMySQLDAO();
        $result = $dao->getPhoto("507648000295407216_180890738", 'instagram');
        $this->debug(Utils::varDumpToString($result));
        $this->assertTrue(isset($result));
        $this->assertEqual($result->post_id, '507648000295407216_180890738');
        $this->assertEqual($result->author_user_id, '180890738');
        $this->assertEqual($result->author_username, 'nilakshdas');
        $this->assertEqual($result->author_fullname, 'Nilaksh Das');
        $avatar = 'http://images.ak.instagram.com/profiles/profile_180890738_75sq_1374737148.jpg';
        $this->assertEqual($result->author_avatar, $avatar);
        $this->assertEqual($result->post_text, '#LoseYourself, as usual.');
        $this->assertEqual($result->is_protected, false);
        $this->assertEqual($result->source, 'undefined');
        $this->assertEqual($result->pub_date, '2013-07-25 12:42:59');
        $this->assertEqual($result->network, 'instagram');
        $this->assertEqual($result->post_key, 5);
        $this->assertEqual($result->filter, 'Lo-fi');
        $srurl = 'http://distilleryimage0.s3.amazonaws.com/a2e8b5f0f4f911e2af6f22000a1f9a09_7.jpg';
        $this->assertEqual($result->standard_resolution_url, $srurl);
        $lrurl = 'http://distilleryimage0.s3.amazonaws.com/a2e8b5f0f4f911e2af6f22000a1f9a09_6.jpg';
        $this->assertEqual($result->low_resolution_url, $lrurl);
        $tnurl = 'http://distilleryimage0.s3.amazonaws.com/a2e8b5f0f4f911e2af6f22000a1f9a09_5.jpg';
        $this->assertEqual($result->thumbnail_url, $tnurl);
    }

    public function testGetPhotoDoesntExist() {
        $dao = new PhotoMySQLDAO();
        $result = $dao->getPhoto("45gtrter", 'instagram');
        $this->assertEqual(!isset($result));
    }

    public function testAddPhoto() {
        $dao = new PhotoMySQLDAO();
        $photo['post_id'] = '3454352543543543';
        $photo['author_user_id'] = '1';
        $photo['author_username'] = 'aaron';
        $photo['author_fullname'] = 'aaron kalair';
        $photo['author_avatar'] = 'http://www.avatarland.com';
        $photo['post_text'] = 'This is my amazing picture';
        $photo['source'] = 'web';
        $photo['is_protected'] = false;
        $photo['pub_date'] = '2013-12-09 12:00:00';
        $photo['network'] = 'instagram';
        $photo['filter'] = 'hipster';
        $photo['standard_resolution_url'] = 'http://distilleryimage0.s3.amazonaws.com/yhgfdh_7.jpg';
        $photo['low_resolution_url'] = 'http://distilleryimage0.s3.amazonaws.com/yhgfdh_6.jpg';
        $photo['thumbnail_url'] = 'http://distilleryimage0.s3.amazonaws.com/yhgfdh_5.jpg';
        $dao->addPhoto($photo);
        // Now check all the values were inserted
        $result = $dao->getPhoto('3454352543543543', 'instagram');
        $this->assertTrue(isset($result));
        $this->assertEqual($result->post_id, '3454352543543543');
        $this->assertEqual($result->author_user_id, '1');
        $this->assertEqual($result->author_username, 'aaron');
        $this->assertEqual($result->author_fullname, 'aaron kalair');
        $this->assertEqual($result->author_avatar, 'http://www.avatarland.com');
        $this->assertEqual($result->post_text, 'This is my amazing picture');
        $this->assertEqual($result->is_protected, false);
        $this->assertEqual($result->source, 'web');
        $this->assertEqual($result->pub_date, '2013-12-09 12:00:00');
        $this->assertEqual($result->network, 'instagram');
        $this->assertEqual($result->filter, 'hipster');
        $srurl = 'http://distilleryimage0.s3.amazonaws.com/yhgfdh_7.jpg';
        $this->assertEqual($result->standard_resolution_url, $srurl);
        $lrurl = 'http://distilleryimage0.s3.amazonaws.com/yhgfdh_6.jpg';
        $this->assertEqual($result->low_resolution_url, $lrurl);
        $tnurl = 'http://distilleryimage0.s3.amazonaws.com/yhgfdh_5.jpg';
        $this->assertEqual($result->thumbnail_url, $tnurl);
    }
}
