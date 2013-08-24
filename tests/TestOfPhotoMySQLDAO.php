<?php
/**
 *
 * ThinkUp/tests/TestOfPhotoMySQLDAO.php
 *
 * Copyright (c) 2013 Nilaksh Das
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
 * @author Nilaksh Das <nilakshdas[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfPhotoMySQLDAO extends ThinkUpUnitTestCase {
    /**
     * PhotoMySQLDAO
     */
    protected $dao;

    public function setUp() {
        $this->dao = new PhotoMySQLDAO();
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
            'author_user_name' => 'nilakshdas',
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
            'internal_post_id' => 5,
            'photo_page' => 'http://instagram.com/p/cLhyqoi45w/',
            'filter' => 'Lo-fi',
            'standard_resolution_url' => 'http://distilleryimage0.s3.amazonaws.com/a2e8b5f0f4f911e2af6f22000a1f9a09_7.jpg',
            'low_resolution_url' => 'http://distilleryimage0.s3.amazonaws.com/a2e8b5f0f4f911e2af6f22000a1f9a09_6.jpg',
            'thumbnail_url' => 'http://distilleryimage0.s3.amazonaws.com/a2e8b5f0f4f911e2af6f22000a1f9a09_5.jpg'
        ));

        return $builders;
    }

    public function testConstructor() {
        $dao = new PhotoMySQLDAO();
        $this->assertTrue(isset($dao));
        sleep(120);
    }
}