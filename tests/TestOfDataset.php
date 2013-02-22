<?php
/**
 *
 * ThinkUp/tests/TestOfDataset.php
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
 * Test of Dataset
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfDataset extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test constructor with allowed DAO name
     */
    public function testConstructorAllowedDAO() {
        $dataset = new Dataset('all-posts', 'PostDAO', 'getAllPosts');
        $this->assertTrue(isset($dataset));
        $this->assertEqual($dataset->dao_name, 'PostDAO');
        $this->assertEqual($dataset->dao_method_name, 'getAllPosts');
        $this->assertIsA($dataset->method_params, 'array');
        $this->assertFalse( $dataset->isSearchable() );
    }

    /**
     * Test constructor with optiona iterator names
     */
    public function testConstructorAllowedDAOSearchIterators() {
        $dataset = new Dataset('all-posts', 'PostDAO', 'getAllPosts', array(), 'getAllPostsByUsernameIterator');
        $this->assertTrue(isset($dataset));
        $this->assertEqual($dataset->dao_name, 'PostDAO');
        $this->assertEqual($dataset->dao_method_name, 'getAllPosts');
        $this->assertIsA($dataset->method_params, 'array');
        $this->assertTrue( $dataset->isSearchable() );
        $this->assertIsA($dataset->iterator_method_params, 'array');
        $this->assertEqual($dataset->iterator_method_name, 'getAllPostsByUsernameIterator');
    }


    /**
     * Test constructor with disallowed DAO name
     */
    public function testConstructorDisallowedDAO() {
        $this->expectException(new Exception('BadDAO is not one of the allowed DAOs'));
        $dataset = new Dataset('all-posts', 'BadDAO', 'getAllPosts');
    }

    /**
     * Test retrieveData with an existing method
     */
    public function testRetrieveDataMethodExists() {
        $dataset = new Dataset('all-posts', 'PostDAO', 'getAllPosts', array(930061, 'twitter', 15));
        $data = $dataset->retrieveDataset();
        $this->assertTrue(isset($data));
        $this->assertIsA($data, 'array');
    }

    /**
     * Test retrieve Iterator with an existing methods
     */
    public function testRetrieveIteratorMethodExists() {
        $build_data = $this->buildData();

        // getAllPostsByUsernameIterator
        $dataset = new Dataset('all-posts', 'PostDAO', 'getAllPosts', array(930061, 'twitter', 15),
            'getAllPostsByUsernameIterator', array('someuser2', 'twitter', 10) );
        $iterator = $dataset->retrieveIterator();
        $this->assertTrue(isset($iterator));
        $this->assertIsA($iterator, 'Iterator');
        $cnt = 0;
        foreach($iterator as $key => $value) {
            $cnt++;
        }
        $this->assertEqual(2, $cnt, 'count should be 2');

        // getAllPostsByUsernameIterator with a limit of 1
        $dataset = new Dataset('all-posts', 'PostDAO', 'getAllPosts', array(930061, 'twitter', 15),
            'getAllPostsByUsernameIterator', array('someuser2', 'twitter', 1) );
        $iterator = $dataset->retrieveIterator();
        $this->assertTrue(isset($iterator));
        $this->assertIsA($iterator, 'Iterator');
        $cnt = 0;
        foreach($iterator as $key => $value) {
            $cnt++;
        }
        $this->assertEqual(1, $cnt, 'count should be 1');

        // getAllMentionsIterator
        $dataset = new Dataset('tweets-mostreplies', 'PostDAO', 'getAllPosts', array(930061, 'twitter', 15),
            'getAllMentionsIterator', array('someuser1', 10, 'twitter') );
        $iterator = $dataset->retrieveIterator();
        $this->assertTrue(isset($iterator));
        $this->assertIsA($iterator, 'Iterator');
        $cnt = 0;
        foreach($iterator as $key => $value) {
            $cnt++;
        }
        $this->assertEqual(2, $cnt, 'count should be 2');
    }

    /**
     * Test retrieveData with an existing method AND page number
     */
    public function testRetrieveDataMethodExistsWithPage() {
        $dataset = new Dataset('all-posts', 'PostDAO', 'getAllPosts', array(930061, 'twitter', 15,
        '#page_number#'));
        //        $dataset = new Dataset('all-posts', 'PostDAO', 'getAllPosts', array(930061, 'twitter', 15));
        $data = $dataset->retrieveDataset();
        $this->assertTrue(isset($data));
        $this->assertIsA($data, 'array');
    }

    /**
     * Test retrieveData with a non-existing method
     */
    public function testRetrieveDataMethodDoesNotExist() {
        $dataset = new Dataset('all-posts', 'PostDAO', 'getAllPostsIDontExist', array(930061, 'twitter', 15));
        $this->expectException(new Exception('PostDAO does not have a getAllPostsIDontExist method.'));
        $data = $dataset->retrieveDataset();
    }

    public function testAddGetHelp() {
        $dataset = new Dataset('all-posts', 'PostDAO', 'getAllPostsIDontExist', array(930061, 'twitter', 15));
        $this->assertNull($dataset->getHelp());
        $dataset->addHelp('userguide/twitter/allposts');
        $this->assertEqual($dataset->getHelp(), 'userguide/twitter/allposts');
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com'));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>123, 'user_name'=>'someuser2',
        'network'=>'twitter'));
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'someuser1',
        'network'=>'twitter'));
        $instance1_builder = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'someuser2',
        'network'=>'twitter'));
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('instance_id'=>1, 'owner_id'=>1));
        $posts1_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser2','author_user_id' => 123,
        'post_text'=>'@someuser1 My first post', 'network'=>'twitter', 
        'retweet_count_cache' => 1, 'pub_date' => '+1d' ));
        $posts2_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser2','author_user_id' => 123,
        'post_text'=>'My second @someuser1 post', 'network'=>'twitter'));
        return array($owner_builder, $instance_builder, $instance1_builder, $owner_instance_builder, $posts1_builder,
        $posts2_builder, $user_builder);
    }
}