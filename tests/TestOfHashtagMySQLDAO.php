<?php
/**
 *
 * ThinkUp/tests/TestOfHashtagMySQLDAO.php
 *
 * Copyright (c) 2011 Amy Unruh
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
 * @copyright 2011  Amy Unruh
 * @author Amy Unruh
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfHashtagMySQLDAO extends ThinkUpUnitTestCase {
    /**
     *
     * @var HashtagMySQLDAO
     */
    protected $dao;

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
        $this->dao = new HashtagMySQLDAO();
    }

    protected function buildData() {
        //random test data to check basic retrieval
        for ($i = 1; $i < 3; $i++) {
            $ht = 'hashtag' . ($i +10);
            $builders[] = FixtureBuilder::build('hashtags',
            array('hashtag' => $ht, 'network'=>'twitter', 'count_cache' => $i + 3));
            $builders[] = FixtureBuilder::build('hashtags_posts',
            array('post_id' => 1000 + $i,'hashtag_id' => $i, 'network' => 'twitter'));
            $builders[] = FixtureBuilder::build('hashtags_posts',
            array('post_id' => 1000 + $i, 'hashtag_id' => $i+100, 'network' => 'twitter'));
        }
        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    /**
     * The contents of the mentions tables are not actively used by the app at the moment.  When they are, more tests
     * will presumably need to be added.
     */
    public function testGetHashtagInfo() {
        $res = $this->dao->getHashtagInfoForTag('hashtag11');
        $this->assertEqual($res['count_cache'], 4);
    }

    public function testGetHashtagPostData() {
        $res = $this->dao->getHashtagsForPost(1001);
        $this->assertEqual(sizeof($res), 2);
        $this->assertEqual($res[1]['hashtag_id'], 101);
    }

    public function testInsertHashtag() {
        $ht = 'bob';
        $this->dao->insertHashtag($ht, '39089424330978176', 'twitter');
        $res = $this->dao->getHashtagsForPost('39089424330978176');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]['post_id'],'39089424330978176');
        $this->assertEqual($res[0]['hashtag_id'],3); //setup has generated 2 prev hashtags
    }

    public function testInsertHashtags() {
        $ht = array('bob', 'dole');
        $this->dao->insertHashtags($ht, '39089424330978176', 'twitter');
        $this->dao->insertHashtags($ht, '39089424330978177', 'twitter');
        $res1 = $this->dao->getHashtagsForPost('39089424330978176');
        $res2 = $this->dao->getHashtagsForPost('39089424330978177');
        $this->assertEqual(sizeof($res1), 2);
        $this->assertEqual(sizeof($res2), 2);
        $this->assertEqual($res2[1]['hashtag_id'],4);
        $res3 = $this->dao->getHashtagInfoForTag('dole');
        $this->assertEqual($res3['count_cache'], 2);
        $res4 = $this->dao->getHashtagsForPostHID(3);
        $this->assertEqual(sizeof($res4), 2);
        $this->assertEqual($res4[0]['post_id'], '39089424330978176');
    }

    //See TestOfPostMySQLDAO for more HashtagMySQLDAO tests
}
