<?php
/**
 *
 * ThinkUp/tests/TestOfMentionMySQLDAO.php
 *
 * Copyright (c) 2011-2013 Amy Unruh
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
 * @copyright 2011-2013 Amy Unruh
 * @author Amy Unruh
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfMentionMySQLDAO extends ThinkUpUnitTestCase {
    /**
     *
     * @var MentionMySQLDAO
     */
    protected $dao;

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
        $this->dao = new MentionMySQLDAO();
    }

    protected function buildData() {
        //random test data to check basic retrieval
        for ($i = 1; $i < 3; $i++) {
            $user = 'user' . ($i +10);
            $builders[] = FixtureBuilder::build('mentions',
            array('user_id'=>$i + 10, 'user_name' => $user, 'network'=>'twitter', 'count_cache' => $i + 3));
            $builders[] = FixtureBuilder::build('mentions_posts',
            array('post_id' => 1000 + $i, 'author_user_id' => 2000 + $i, 'mention_id' => $i, 'network' => 'twitter'));
            $builders[] = FixtureBuilder::build('mentions_posts',
            array('post_id' => 1000 + $i, 'author_user_id' => 3000 + $i, 'mention_id' => $i+100,
            'network' => 'twitter'));
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
    public function testGetMentionInfoUserName() {
        $res = $this->dao->getMentionInfoUserName('user11');
        $this->assertEqual($res['user_id'], 11);
        $this->assertEqual($res['count_cache'], 4);
    }

    public function testGetMentionInfoUserID() {
        $res = $this->dao->getMentionInfoUserID(12);
        $this->assertEqual($res['user_name'], 'user12');
        $this->assertEqual($res['count_cache'], 5);
    }

    public function testGetMentionPostData() {
        $res = $this->dao->getMentionsForPost(1001);
        $this->assertEqual(sizeof($res), 2);
        $this->assertEqual($res[1]['author_user_id'], 3001);
        $this->assertEqual($res[0]['mention_id'], 1);
    }

    public function testInsertMention() {
        $this->dao->insertMention(1233445, 'bob', '39089424330978176', 65432, 'twitter');
        $res = $this->dao->getMentionsForPost('39089424330978176');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]['post_id'],'39089424330978176');
        $this->assertEqual($res[0]['author_user_id'],65432);
        $this->assertEqual($res[0]['mention_id'],3);
    }

    public function testInsertMentions() {
        $mentions = array(
        array('user_id' => 136881432,'user_name' => 'HeyJacquiDey',),
        array('user_id' => 1106501,'user_name' => 'joanwalsh',),
        array('user_id' => 1140451, 'user_name' => 'AntDeRosa' ),
        // To test for duplicate suppression
        array('user_id' => 1140451, 'user_name' => 'AntDeRosa' )
        );
        $this->dao->insertMentions($mentions, '39089424620978176', 123456, 'twitter');
        $this->dao->insertMentions($mentions, '39089424620978177', 123457, 'twitter');
        $res1 = $this->dao->getMentionsForPost('39089424620978176');
        $res2 = $this->dao->getMentionsForPost('39089424620978177');

        // there should be 3 mentions associated with each post
        $this->assertEqual(sizeof($res1), 3);
        $this->assertEqual(sizeof($res2), 3);
        $this->assertEqual($res2[2]['mention_id'],5);
        // for a given mention, there should be two posts
        $res3 = $this->dao->getMentionInfoUserName('joanwalsh');
        $this->assertEqual($res3['user_id'], 1106501);
        $this->assertEqual($res3['count_cache'], 2);
        $res4 = $this->dao->getMentionsForPostMID(3);
        $this->assertEqual(sizeof($res4), 2);
        $this->assertEqual($res4[0]['post_id'], '39089424620978176');
        $this->assertEqual($res4[1]['author_user_id'], 123457);

        // the duplicate mention for AntDeRosa should not have been counted
        $stmt = MentionMySQLDAO::$PDO->query('SELECT count_cache FROM ' . $this->table_prefix . 'mentions WHERE
        user_id = "1140451" AND network = "twitter"');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual(intval($row['count_cache']), 2);

    }

    //See TestOfPostMySQLDAO for more MentionMySQLDAO tests
}
