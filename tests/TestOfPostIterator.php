<?php
/**
 *
 * ThinkUp/tests/TestOfPostIterator.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfPostIterator extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'posts';
    const TEST_TABLE_LINKS = 'links';

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testNullStmt() {
        $post_it = new PostIterator(null);
        $cnt = 0;
        foreach($post_it as $key => $value) {
            $cnt++;
        }
        $this->assertEqual(0, $cnt, 'count should be zero');
    }

    public function testStmtNoResults() {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $sql = "select * from " . $this->table_prefix . self::TEST_TABLE;
        $stmt = PostMySQLDAO::$PDO->query($sql);
        $post_it = new PostIterator($stmt);
        $cnt = 0;
        foreach($post_it as $key => $value) {
            $cnt++;
        }
        $this->assertEqual(0, $cnt, 'count should be zero');
    }

    public function testValidResults() {
        // one result
        $builders = $this->buildOptions(1);
        $post_dao = DAOFactory::getDAO('PostDAO');
        $post_it = $post_dao->getAllPostsByUsernameIterator('mojojojo', 'twitter');
        $posts = $post_dao->getAllPostsByUsername('mojojojo', 'twitter');
        $cnt = 0;
        foreach($post_it as $key => $value) {
            $this->assertEqual($value->post_text, $posts[$cnt]->post_text);
            $this->assertIsA($value, 'Post');
            $cnt++;
        }
        $this->assertEqual(1, $cnt);

        // 10 results
        $builders = null;
        $builders = $this->buildOptions(10);
        $post_it = $post_dao->getAllPostsByUsernameIterator('mojojojo', 'twitter');
        $posts = $post_dao->getAllPostsByUsername('mojojojo', 'twitter');
        $cnt = 0;
        foreach($post_it as $key => $value) {
            $this->assertEqual($value->post_text, $posts[$cnt]->post_text);
            $this->assertIsA($value, 'Post');
            $cnt++;
        }
        $this->assertEqual(10, $cnt);

    }

    /**
     * build some posts
     */
    public function buildOptions($count) {
        $builders = array();
        for($i = 1; $i <= $count; $i++) {
            $builder = FixtureBuilder::build(self::TEST_TABLE,
            array('post_id' => $i, 'pub_date' => '-1h', 'author_username' => 'mojojojo', 'author_user_id' => 1) );
            array_push($builders, $builder);
            $post_id = $builder->columns[ 'last_insert_id' ];
            $link_builder = FixtureBuilder::build(self::TEST_TABLE_LINKS, array('post_id' => $post_id) );
            array_push($builders, $link_builder);

        }
        return $builders;
    }

}