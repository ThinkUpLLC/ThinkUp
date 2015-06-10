<?php
/**
 *
 * ThinkUp/webapp/plugins/instagram/tests/TestOfInstagramInstanceMySQLDAO.php
 *
 * Copyright (c) 2015 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2015 Gina Trapani
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/model/class.InstagramInstanceMySQLDAO.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/model/class.InstagramInstance.php';

class TestOfInstagramInstanceMySQLDAO extends ThinkUpUnitTestCase {
    protected $DAO;

    public function setUp() {
        parent::setUp();
        $this->DAO = new InstagramInstanceMySQLDAO();
        $this->builders = $this->buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    protected function buildData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>10, 'network_username'=>'jack',
        'network'=>'instagram', 'network_viewer_id'=>10, 'crawler_last_run'=>'1988-01-20 12:00:00', 'is_active'=>1,
        'is_public'=>0));

        $builders[] = FixtureBuilder::build('instances_instagram',
            array(
                'followed_by_next_cursor'=>'12345',
                'follows_next_cursor'=>'asdfasdf',
                'next_max_like_id'=>'dddddddd'
            ));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>12, 'network_username'=>'jill',
        'network'=>'instagram', 'network_viewer_id'=>12, 'crawler_last_run'=>'2010-01-20 12:00:00', 'is_active'=>1,
        'is_public'=>0));

        $builders[] = FixtureBuilder::build('instances_instagram',
            array(
                'followed_by_next_cursor'=>'678900-908',
                'follows_next_cursor'=>'asdfasdf',
                'next_max_like_id'=>'dddddddd'
            ));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>13, 'network_username'=>'stuart',
        'network'=>'instagram', 'network_viewer_id'=>13, 'crawler_last_run'=>'2010-01-01 12:00:00', 'is_active'=>0,
        'is_public'=>1));

        $builders[] = FixtureBuilder::build('instances_instagram',
            array(
                'followed_by_next_cursor'=>'asdfasdfasdfa',
                'follows_next_cursor'=>'asdfasdf',
                'next_max_like_id'=>'dddddddd'
            ));

        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>1));

        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>2));

        return $builders;
    }

    public function testGet() {
        $i = $this->DAO->get(1);
        $this->assertIsA($i, 'InstagramInstance');
        $this->assertEqual($i->id, 1);
        $this->assertEqual($i->network_user_id, 10);
        $this->assertEqual($i->network_username, 'jack');
        $this->assertEqual($i->network, 'instagram');
        $this->assertEqual($i->followed_by_next_cursor, '12345');
        $this->assertEqual($i->follows_next_cursor, 'asdfasdf');
        $this->assertEqual($i->next_max_like_id, 'dddddddd');

        $i = $this->DAO->get(100);
        $this->assertNull($i);
    }

    public function testGetFreshestByOwnerId(){
        $instance_builder = FixtureBuilder::build('instances', array('network_username'=>'julie',
        'network'=>'instagram', 'crawler_last_run'=>'-1d', 'is_activated'=>'1', 'is_public'=>'1'));

        $instagram_instance_builder= FixtureBuilder::build('instances_instagram',
        array('id'=> $instance_builder->columns['last_insert_id'],'last_reply_id'=>'10'));

        $owner_instance_builder = FixtureBuilder::build('owner_instances', array(
        'instance_id'=>$instance_builder->columns['last_insert_id'], 'owner_id'=>'2'));

        //try one
        $instance = $this->DAO->getFreshestByOwnerId(2);
        $this->assertIsA($instance, "InstagramInstance");
        $this->assertEqual($instance->id, $instance_builder->columns['last_insert_id']);
        $this->assertEqual($instance->network_username, 'julie');
        $this->assertEqual($instance->network_user_id, $instance_builder->columns['network_user_id']);
        $this->assertEqual($instance->network_viewer_id, $instance_builder->columns['network_viewer_id']);
        $this->assertEqual($instance->followed_by_next_cursor,
            $instagram_instance_builder->columns['followed_by_next_cursor']);
        $this->assertEqual($instance->follows_next_cursor,
            $instagram_instance_builder->columns['follows_next_cursor']);
        $this->assertEqual($instance->next_max_like_id,
            $instagram_instance_builder->columns['next_max_like_id']);

        //Try a non existent one
        $result = $this->DAO->getFreshestByOwnerId(3);
        $this->assertNull($result);
    }

    public function testGetInstanceOneByLastRun(){
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        //Try Newest
        $result = $this->DAO->getInstanceFreshestOne();
        $this->assertIsA($result, "Instance");
        $this->assertIsA($result, "InstagramInstance");
        $this->assertEqual($result->network_username, 'jill');
        $this->assertEqual($result->network_user_id, 12);
        $this->assertEqual($result->network_viewer_id, 12);
        $this->assertEqual($result->followed_by_next_cursor, '678900-908');
        $this->assertEqual($result->follows_next_cursor, 'asdfasdf');
        $this->assertEqual($result->next_max_like_id, 'dddddddd');

        //Try Newest Public
        $result = $this->DAO->getInstanceFreshestPublicOne();
        $this->assertIsA($result, "Instance");
        $this->assertIsA($result, "InstagramInstance");
        $this->assertEqual($result->network_username, 'stuart');
        $this->assertEqual($result->network_user_id, 13);
        $this->assertEqual($result->network_viewer_id, 13);
        $this->assertEqual($result->followed_by_next_cursor, 'asdfasdfasdfa');
        $this->assertEqual($result->follows_next_cursor, 'asdfasdf');
        $this->assertEqual($result->next_max_like_id, 'dddddddd');

        //Try Oldest
        $result = $this->DAO->getInstanceStalestOne();
        $this->assertIsA($result, "Instance");
        $this->assertIsA($result, "InstagramInstance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);
        $this->assertEqual($result->followed_by_next_cursor, '12345');
        $this->assertEqual($result->follows_next_cursor, 'asdfasdf');
        $this->assertEqual($result->next_max_like_id, 'dddddddd');

        $q = "TRUNCATE TABLE " . $config_array['table_prefix'] . "instances ";
        PDODAO::$PDO->exec($q);

        //Try empty
        $result = $this->DAO->getInstanceStalestOne();
        $this->assertNull($result);
    }

    public function testGetByUsername() {
        //try one user
        $result = $this->DAO->getByUsername('jill','instagram');
        $this->assertIsA($result, "Instance");
        $this->assertIsA($result, "InstagramInstance");
        $this->assertEqual($result->network_username, 'jill');
        $this->assertEqual($result->network_user_id, 12);
        $this->assertEqual($result->network_viewer_id, 12);
        $this->assertEqual($result->followed_by_next_cursor, '678900-908');
        $this->assertEqual($result->follows_next_cursor, 'asdfasdf');
        $this->assertEqual($result->next_max_like_id, 'dddddddd');

        //try another one
        $result = $this->DAO->getByUsername('jack','instagram');
        $this->assertIsA($result, "Instance");
        $this->assertIsA($result, "InstagramInstance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);
        $this->assertEqual($result->followed_by_next_cursor, '12345');
        $this->assertEqual($result->follows_next_cursor, 'asdfasdf');
        $this->assertEqual($result->next_max_like_id, 'dddddddd');

        //try non-existing one
        $result = $this->DAO->getByUsername('no one');
        $this->assertNull($result);
    }

    public function testGetByUsernameOnNetwork() {
        $this->DAO = new InstagramInstanceMySQLDAO();
        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>17, 'network_username'=>'salma',
        'network'=>'instagram', 'network_viewer_id'=>15, 'crawler_last_run'=>'2010-01-01 12:00:01', 'is_active'=>1));

        $builders[] = FixtureBuilder::build('instances_instagram', array(
                'followed_by_next_cursor'=>'asdfasdfasdfa',
                'follows_next_cursor'=>'asdfasdf',
                'next_max_like_id'=>'dddddddd'
            ));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>18, 'network_username'=>'salma',
        'network'=>'instagram', 'network_viewer_id'=>15, 'crawler_last_run'=>'2010-01-01 12:00:01',
        'is_active'=>1));

        $result = $this->DAO->getByUsernameOnNetwork('salma', 'instagram');
        $this->assertIsA($result, "Instance");
        $this->assertIsA($result, "InstagramInstance");
        $this->assertEqual($result->network_username, 'salma');
        $this->assertEqual($result->network, 'instagram');
        $this->assertEqual($result->network_user_id, 17);
        $this->assertEqual($result->followed_by_next_cursor, 'asdfasdfasdfa');
        $this->assertEqual($result->follows_next_cursor, 'asdfasdf');
        $this->assertEqual($result->next_max_like_id, 'dddddddd');
    }

    public function testGetByUserId() {
        // data do exist
        $result = $this->DAO->getByUserIdOnNetwork(10, 'instagram');
        $this->assertIsA($result, "Instance");
        $this->assertIsA($result, "InstagramInstance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);
        $this->assertEqual($result->followed_by_next_cursor, '12345');
        $this->assertEqual($result->follows_next_cursor, 'asdfasdf');
        $this->assertEqual($result->next_max_like_id, 'dddddddd');

        // data do not exist
        $result = $this->DAO->getByUserIdOnNetwork(11, 'instagram');
        $this->assertNull($result);
    }

    public function testGetAllInstancesNoMetaData(){
        //test get instances when there's no metadata
        $instance_builder = FixtureBuilder::build('instances', array('network_username'=>'susie',
        'network_user_id'=>59, 'network'=>'instagram', 'crawler_last_run'=>'-8d', 'is_activated'=>'1', 'is_public'=>'1',
        'network_viewer_id'=>47));
        $result = $this->DAO->getAllInstances("ASC", true, "instagram");
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 3);
        $users = array('jack','jill','susie');
        $uID = array(10,12,59);
        $vID = array(10,12,47);
        $followed_by_next_cursor_updates = array('12345','678900-908', null);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertIsA($i, "InstagramInstance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
            $this->assertEqual($i->followed_by_next_cursor, $followed_by_next_cursor_updates[$id]);
        }
    }

    public function testSaveInstanceNoMetaData(){
        //test get instances when there's no metadata
        $instance_builder = FixtureBuilder::build('instances', array('id'=>101, 'network_username'=>'susie',
        'network_user_id'=>59, 'network'=>'instagram', 'crawler_last_run'=>'-8d', 'is_activated'=>'1', 'is_public'=>'1',
        'network_viewer_id'=>47));
        $result = $this->DAO->getByUserIdOnNetwork(59, 'instagram');
        $this->assertIsA($result, "InstagramInstance");
        $this->assertNull($result->last_favorite_id);
        $this->assertFalse($this->DAO->doesMetaDataExist(101));

        $logger = Logger::getInstance();
        $result->last_reply_id = '1';
        $this->DAO->save($result, 500, $logger);
        $updated_result = $this->DAO->getByUserIdOnNetwork(59, 'instagram');
        $this->assertIsA($updated_result, "InstagramInstance");
        $this->assertNull($updated_result->last_favorite_id);
        $this->assertNull($updated_result->last_reply_id);
        $this->assertNull($updated_result->followed_by_next_cursor);

        $result->followed_by_next_cursor = '19780322';
        $this->DAO->save($result, 500, $logger);

        $updated_result = $this->DAO->getByUserIdOnNetwork(59, 'instagram');
        $this->assertIsA($updated_result, "InstagramInstance");
        $this->assertNull($updated_result->last_favorite_id);
        $this->assertNull($updated_result->last_reply_id);
        $this->assertNotNull($updated_result->followed_by_next_cursor);
        $this->assertEqual($updated_result->followed_by_next_cursor, '19780322');
    }

    public function testGetAllInstances() {
        $result = $this->DAO->getAllInstances('DESC', false, 'instagram');
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 3);
        $users = array('jill','stuart','jack');
        $uID = array(12,13,10);
        $vID = array(12,13,10);
        $followed_by_next_cursor_updates = array('678900-908','asdfasdfasdfa', '12345');
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertIsA($i, "InstagramInstance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
            $this->assertEqual($i->last_reply_id, null);
            $this->assertEqual($i->followed_by_next_cursor, $followed_by_next_cursor_updates[$id]);
        }

        // Test ASC
        $result = $this->DAO->getAllInstances("ASC",false,'instagram');
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 3);
        $users = array('jack','stuart','jill');
        $uID = array(10,13,12);
        $vID = array(10,13,12);
        $followed_by_next_cursor_updates = array('12345','asdfasdfasdfa', '678900-908');
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertIsA($i, "InstagramInstance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
            $this->assertEqual($i->last_reply_id, null);
            $this->assertEqual($i->followed_by_next_cursor, $followed_by_next_cursor_updates[$id]);
        }

        // Test ASC Only Active
        $result = $this->DAO->getAllInstances("ASC", true,'instagram');
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $users = array('jack','jill');
        $uID = array(10,12);
        $vID = array(10,12);
        $followed_by_next_cursor_updates = array('12345','678900-908');
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertIsA($i, "InstagramInstance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
            $this->assertEqual($i->last_reply_id, null);
            $this->assertEqual($i->followed_by_next_cursor, $followed_by_next_cursor_updates[$id]);
        }
    }
    public function testGetByOwner(){
        $data = array(
            'id'=>2,
            'user_name'=>'steven',
            'full_name'=>'Steven Warren',
            'email'=>'me@example.com',
            'last_login'=>'Yesterday',
            'is_admin'=>1,
            'is_activated'=>1,
            'failed_logins'=>0,
            'account_status'=>''
            );
            $owner = new Owner($data);

            // Test is-admin
            $result = $this->DAO->getByOwner($owner);
            $this->assertIsA($result, "array");
            $this->assertEqual(count($result), 3);
            $users = array('jill','stuart','jack');
            $uID = array(12,13,10);
            $vID = array(12,13,10);
            $followed_by_next_cursor_updates = array('678900-908','asdfasdfasdfa', '12345');
            foreach($result as $id=>$i){
                $this->assertIsA($i, "Instance");
                $this->assertIsA($i, "InstagramInstance");
                $this->assertEqual($i->network_username, $users[$id]);
                $this->assertEqual($i->network_user_id, $uID[$id]);
                $this->assertEqual($i->network_viewer_id, $vID[$id]);
                $this->assertEqual($i->followed_by_next_cursor, $followed_by_next_cursor_updates[$id]);
            }

            // Test Is Admin - Forced Not
            $result = $this->DAO->getByOwner($owner, true);
            $this->assertIsA($result, "array");
            $this->assertEqual(count($result), 2);
            $users = array('jill','jack');
            $uID = array(12,10);
            $vID = array(12,10);
            foreach($result as $id=>$i){
                $this->assertIsA($i, "Instance");
                $this->assertIsA($i, "InstagramInstance");
                $this->assertEqual($i->network_username, $users[$id]);
                $this->assertEqual($i->network_user_id, $uID[$id]);
                $this->assertEqual($i->network_viewer_id, $vID[$id]);
            }

            // Test not admin
            $owner->is_admin = false;
            $result = $this->DAO->getByOwner($owner);
            $this->assertIsA($result, "array");
            $this->assertEqual(count($result), 2);
            $users = array('jill','jack');
            $uID = array(12,10);
            $vID = array(12,10);
            foreach($result as $id=>$i){
                $this->assertIsA($i, "Instance");
                $this->assertIsA($i, "InstagramInstance");
                $this->assertEqual($i->network_username, $users[$id]);
                $this->assertEqual($i->network_user_id, $uID[$id]);
                $this->assertEqual($i->network_viewer_id, $vID[$id]);
            }

            $owner->id = 3;
            //Try empty
            $result = $this->DAO->getByOwner($owner);
            $this->assertIsA($result, "array");
            $this->assertEqual(count($result), 0);
    }

    public function testGetPublicInstances() {
        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>13, 'network_username'=>'sam',
        'network'=>'instagram', 'network_viewer_id'=>13, 'crawler_last_run'=>'2010-01-01 12:00:00', 'is_active'=>1,
        'is_public'=>1));

        $builders[] = FixtureBuilder::build('instances_instagram', array('last_reply_id'=>'12'));

        $result = $this->DAO->getPublicInstances();
        $this->assertIsA($result, "Array");
        $this->assertEqual(sizeof($result), 1);
        $this->assertIsA($result[0], "Instance");
        $this->assertIsA($result[0], "InstagramInstance");
        $this->assertEqual($result[0]->network_username, "sam" );
    }

    public function testGetByOwnerAndNetwork(){
        $data = array(
            'id'=>2,
            'user_name'=>'steven',
            'full_name'=>'Steven Warren',
            'email'=>'me@example.com',
            'last_login'=>'Yesterday',
            'is_admin'=>1,
            'is_activated'=>1,
            'failed_logins'=>0,
            'account_status'=>''
            );
            $owner = new Owner($data);

            // Test is-admin facebook
            $result = $this->DAO->getByOwnerAndNetwork($owner, 'instagram');
            $this->assertIsA($result, "array");
            $this->assertEqual(count($result), 3);
            $users = array('jill','stuart','jack');
            $uID = array(12,13,10);
            $vID = array(12,13,10);
            $last_page_replies = array(11, 12, 10);
            $followed_by_next_cursor_updates = array('678900-908','asdfasdfasdfa', '12345');
            foreach($result as $id=>$i){
                $this->assertIsA($i, "Instance");
                $this->assertEqual($i->network_username, $users[$id]);
                $this->assertEqual($i->network_user_id, $uID[$id]);
                $this->assertEqual($i->network_viewer_id, $vID[$id]);
                $this->assertEqual($i->followed_by_next_cursor, $followed_by_next_cursor_updates[$id]);
            }

            // Test is-admin Facebook, forced not
            $result = $this->DAO->getByOwnerAndNetwork($owner, 'instagram', true);
            $this->assertIsA($result, "array");
            $this->assertEqual(count($result), 2);
            $users = array('jill','jack');
            $uID = array(12,10);
            $vID = array(12,10);
            foreach($result as $id=>$i){
                $this->assertIsA($i, "Instance");
                $this->assertIsA($i, "InstagramInstance");
                $this->assertEqual($i->network_username, $users[$id]);
                $this->assertEqual($i->network_user_id, $uID[$id]);
                $this->assertEqual($i->network_viewer_id, $vID[$id]);
            }

            // Test not admin facebook
            $owner->is_admin = false;
            $result = $this->DAO->getByOwnerAndNetwork($owner, 'instagram');
            $this->assertIsA($result, "array");
            $this->assertEqual(count($result), 2);
            $users = array('jill','jack');
            $uID = array(12,10);
            $vID = array(12,10);
            foreach($result as $id=>$i){
                $this->assertIsA($i, "Instance");
                $this->assertIsA($i, "InstagramInstance");
                $this->assertEqual($i->network_username, $users[$id]);
                $this->assertEqual($i->network_user_id, $uID[$id]);
                $this->assertEqual($i->network_viewer_id, $vID[$id]);
            }

            $owner->id = 3;
            //Try empty
            $result = $this->DAO->getByOwnerAndNetwork($owner, 'instagram');;
            $this->assertIsA($result, "array");
            $this->assertEqual(count($result), 0);
    }

    public function testInsert() {
        $result = $this->DAO->insert(11, 'ev');
        $this->assertEqual($result, 4);
        $i = $this->DAO->getByUserIdOnNetwork(11, 'instagram');
        $this->assertIsA($i, "InstagramInstance");
        $this->assertEqual($i->network_user_id, 11);
        $this->assertEqual($i->network_viewer_id, 11);
        $this->assertEqual($i->network_username, 'ev');
        $this->assertEqual($i->network, 'instagram');
        $this->assertEqual($i->last_reply_id, '');
    }

    public function testDeleteInstance() {
        $i = $this->DAO->getByUsernameOnNetwork('jack', 'instagram');
        $this->assertNotNull($i);
        $result = $this->DAO->delete('jack', 'instagram');
        $this->assertEqual($result, 1);
        $i = $this->DAO->getByUsernameOnNetwork('jack', 'instagram');
        $this->assertNull($i);

        $result = $this->DAO->delete('idontexist', 'somenonexistentnetwork');
        $this->assertEqual($result, 0);
    }

    public function testSave(){
        //First in line is some posts 250 Randomly generated ones, some with mentions.
        $mentions = 0;
        $posts = 0;
        $replies = 0;
        $links = 0;
        $builders = array();
        for($i=0; $i <= 250; $i++){
            $sender = rand(5,16);
            $data = 'asdf qwerty flakes meep';
            $post_id = rand(1000, 1000000);
            while(isset($pic[$post_id])){
                $post_id = rand(1000, 1000000);
            }
            $pic[$post_id] = true;

            $number = rand(1,8);
            if ($number == 1 or $number == 2){
                $data = "@jack ".$data;
                $mentions++;
            }
            elseif ($number == 3){
                $data = "@jill ".$data;
            }
            if ($number % 2 == 0) {
                $reply_to = '11';
                if ($sender == 10){
                    $replies++;
                }
            } else {
                $reply_to = null;
            }
            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_id, 'post_id'=>$post_id,
            'author_user_id'=>$sender, 'post_text'=>$data, 'pub_date'=>'-'.$number.'h',
            'in_reply_to_user_id'=>$reply_to, 'network' => 'instagram', 'in_reply_to_post_id'=>null));
            if ($sender == 10){
                $posts++;
            }

            if ($number % 2 == 1) {
                $builders[] = FixtureBuilder::build('links', array('url'=>$data, 'post_key'=>$post_id));
                if ($sender == 10){
                    $links++;
                }
            }
        }
        unset($pic);
        //Then generate some follows
        $follows = 0;
        for($i=0; $i<= 150; $i++){
            $follow = array("follower"=>rand(5,25), "following"=>rand(5,25));
            if (!isset($fd[$follow['following']."-".$follow['follower']])){
                $fd[$follow['following']."-".$follow['follower']] = true;
                $builders[] = FixtureBuilder::build('follows', array('user_id'=>$follow['following'],
                'follower_id'=>$follow['follower'], 'network' => 'instagram'));
                if ($follow['following'] == 10){
                    $follows++;
                }
            }
            else{
                $i = $i-1;
            }
        }

        //Lastly generate some users
        $users = array(
            array('id'=>10, 'user_name'=>'jack', 'network' => 'instagram'),
            array('id'=>12, 'user_name'=>'jill', 'network' => 'instagram'),
            array('id'=>13, 'user_name'=>'stuart', 'network' => 'instagram'),
            array('id'=>15, 'user_name'=>'Jillian Dickerson', 'network' => 'instagram'),
            array('id'=>16, 'user_name'=>'Paul Clark', 'network' => 'instagram')
        );
        foreach($users as $user){
            $builders[] = FixtureBuilder::build('users', $user);
        }

        //Now load the instance in question
        $i = $this->DAO->getByUsername('jack', 'instagram');

        //Edit it.
        $i->followed_by_next_cursor = '9876543210';
        $i->last_post_id = 42;

        //First make sure that last run data is correct before we start.
        $result = $this->DAO->getInstanceFreshestOne();
        $this->assertIsA($result, "InstagramInstance");
        $this->assertEqual($result->network_username, 'jill');
        $this->assertEqual($result->network_user_id, 12);
        $this->assertEqual($result->network_viewer_id, 12);

        //Save it
        $count = $this->DAO->save($i, 1024);

        $this->assertEqual($count, 1);

        //Load it for testing
        $result = $this->DAO->getByUsername('jack','instagram');
        $this->assertEqual($result->total_posts_by_owner, 1024);
        $this->assertEqual($result->last_post_id, 42);
        $this->assertNull($result->total_replies_in_system);
        $this->assertEqual($result->total_follows_in_system, $follows);
        $this->assertEqual($result->total_posts_in_system, $posts);
        $this->assertEqual($result->followed_by_next_cursor, '9876543210');

        //Check if it is the update updated last Run.
        $result = $this->DAO->getInstanceFreshestOne();
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);

        // Check if the stats were correctly calculated and saved
        $posts_per = ($posts > 25) ? 25 : $posts; // post per are limited to a max of 25, see getInstanceUserStats()
        $this->assertEqual($result->posts_per_day, $posts_per);
        $this->assertEqual($result->posts_per_week, $posts_per);
        $this->assertEqual($result->percentage_replies, round($replies / $posts * 100, 2));
        $this->assertEqual($result->percentage_links, round($links / $posts * 100, 2));
    }
}
