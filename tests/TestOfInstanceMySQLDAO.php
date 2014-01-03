<?php
/**
 *
 * ThinkUp/tests/TestOfInstanceMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Guillaume Boudreau, Christoffer Viken, Mark Wilkie
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
 * @author Guillaume Boudreau <gboudreau[at]pommepause[dot]com>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Guillaume Boudreau, Christoffer Viken, Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInstanceMySQLDAO extends ThinkUpUnitTestCase {
    protected $DAO;

    public function setUp() {
        parent::setUp();
        $this->DAO = new InstanceMySQLDAO();
        $this->builders = $this->buildData();
    }

    protected function buildData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>10, 'network_username'=>'jack',
        'network'=>'twitter', 'network_viewer_id'=>10, 'crawler_last_run'=>'1988-01-20 12:00:00', 'is_active'=>1,
        'is_public'=>0, 'posts_per_day'=>11, 'posts_per_week'=>77));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>12, 'network_username'=>'jill',
        'network'=>'twitter', 'network_viewer_id'=>12, 'crawler_last_run'=>'2010-01-20 12:00:00', 'is_active'=>1,
        'is_public'=>0, 'posts_per_day'=>11, 'posts_per_week'=>77));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>13, 'network_username'=>'stuart',
        'network'=>'twitter', 'network_viewer_id'=>13, 'crawler_last_run'=>'2010-01-01 12:00:00', 'is_active'=>0,
        'is_public'=>1, 'posts_per_day'=>11, 'posts_per_week'=>77));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>15,
        'network_username'=>'Jillian Dickerson', 'network'=>'facebook', 'network_viewer_id'=>15,
        'crawler_last_run'=>'2010-01-01 12:00:01', 'is_active'=>1, 'is_public'=>1, 'posts_per_day'=>11,
        'posts_per_week'=>77));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>16, 'network_username'=>'Paul Clark',
        'network'=>'facebook', 'network_viewer_id'=>16, 'crawler_last_run'=>'2010-01-01 12:00:02', 'is_active'=>0,
        'is_public'=>1, 'posts_per_day'=>11, 'posts_per_week'=>77));

        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>1,
        'auth_error'=>"There has been an error."));

        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>2,
        'auth_error'=>''));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testDeleteInstance() {
        $i = $this->DAO->getByUsernameOnNetwork('jack', 'twitter');
        $this->assertNotNull($i);
        $result = $this->DAO->delete('jack', 'twitter');
        $this->assertEqual($result, 1);
        $i = $this->DAO->getByUsernameOnNetwork('jack', 'twitter');
        $this->assertNull($i);

        $result = $this->DAO->delete('idontexist', 'somenonexistentnetwork');
        $this->assertEqual($result, 0);
    }

    public function testGet() {
        $i = $this->DAO->get(1);
        $this->assertIsA($i, 'Instance');
        $this->assertEqual($i->id, 1);
        $this->assertEqual($i->network_user_id, 10);
        $this->assertEqual($i->network_username, 'jack');
        $this->assertEqual($i->network, 'twitter');

        $i = $this->DAO->get(100);
        $this->assertNull($i);
    }

    public function testGetHoursSinceLastCrawlerRun() {
        $dao = new InstanceMySQLDAO();
        //set all existing instances to inactive first
        $dao->setActive(1, 0);
        $dao->setActive(2, 0);
        $dao->setActive(3, 0);
        $dao->setActive(4, 0);
        $dao->setActive(5, 0);

        $builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-3h', 'is_active'=>1));
        $hours = $dao->getHoursSinceLastCrawlerRun();
        $this->assertEqual($hours, 3);

        $builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-2h', 'is_active'=>1));
        $hours = $dao->getHoursSinceLastCrawlerRun();
        $this->assertEqual($hours, 3);

        // test that it ignores inactive instances
        $builders[] = FixtureBuilder::build('instances', array('crawler_last_run'=>'-1h', 'is_active' => 0));
        $hours = $dao->getHoursSinceLastCrawlerRun();
        $this->assertEqual($hours, 3);
    }

    public function testInsert() {
        $result = $this->DAO->insert(11, 'ev');
        $this->assertEqual($result, 6);
        $i = $this->DAO->getByUserIdOnNetwork(11, 'twitter');
        $this->assertEqual($i->network_user_id, 11);
        $this->assertEqual($i->network_viewer_id, 11);
        $this->assertEqual($i->network_username, 'ev');
        $this->assertEqual($i->network, 'twitter');

        $result = $this->DAO->insert(14, 'The White House Facebook Page', 'facebook', 10);
        $this->assertEqual($result, 7);
        $i = $this->DAO->getByUserIdOnNetwork(14, 'facebook');
        $this->assertEqual($i->network_user_id, 14);
        $this->assertEqual($i->network_viewer_id, 10);
        $this->assertEqual($i->network_username, 'The White House Facebook Page');
        $this->assertEqual($i->network, 'facebook');
    }

    public function testGetFreshestByOwnerId(){
        $instance_builder = FixtureBuilder::build('instances', array('network_username'=>'julie',
        'network'=>'twitter', 'crawler_last_run'=>'-1d', 'is_activated'=>'1', 'is_public'=>'1'));
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array(
        'instance_id'=>$instance_builder->columns['last_insert_id'], 'owner_id'=>'2'));

        //try one
        $instance = $this->DAO->getFreshestByOwnerId(2);
        $this->assertIsA($instance, "Instance");
        $this->assertEqual($instance->id, $instance_builder->columns['last_insert_id']);
        $this->assertEqual($instance->network_username, 'julie');
        $this->assertEqual($instance->network_user_id, $instance_builder->columns['network_user_id']);
        $this->assertEqual($instance->network_viewer_id, $instance_builder->columns['network_viewer_id']);

        //Try a non existent one
        $result = $this->DAO->getFreshestByOwnerId(3);
        $this->assertNull($result);
    }

    public function testGetInstanceOneByLastRun(){
        //Try Newest
        $result = $this->DAO->getInstanceFreshestOne();
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jill');
        $this->assertEqual($result->network_user_id, 12);
        $this->assertEqual($result->network_viewer_id, 12);

        //Try Newest Public
        $result = $this->DAO->getInstanceFreshestPublicOne();
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'Paul Clark');
        $this->assertEqual($result->network_user_id, 16);
        $this->assertEqual($result->network_viewer_id, 16);

        //Try Oldest
        $result = $this->DAO->getInstanceStalestOne();
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);

        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $q = "TRUNCATE TABLE " . $config_array['table_prefix'] . "instances ";
        PDODAO::$PDO->exec($q);

        //Try empty
        $result = $this->DAO->getInstanceStalestOne();
        $this->assertNull($result);
    }

    public function testGetByUsername() {
        //try one user
        $result = $this->DAO->getByUsername('jill');
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jill');
        $this->assertEqual($result->network_user_id, 12);
        $this->assertEqual($result->network_viewer_id, 12);

        //try another one
        $result = $this->DAO->getByUsername('jack');
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);

        //try non-existing one
        $result = $this->DAO->getByUsername('no one');
        $this->assertNull($result);
    }

    public function testGetByUserId() {
        // data do exist
        $result = $this->DAO->getByUserIdOnNetwork(10, 'twitter');
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);

        // data do not exist
        $result = $this->DAO->getByUserIdOnNetwork(11, 'twitter');
        $this->assertNull($result);
    }

    public function testGetAllInstances(){
        //getAllInstances($order = "DESC", $only_active = false, $network = "twitter")
        // Test, default settings
        $result = $this->DAO->getAllInstances();
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 3);
        $users = array('jill','stuart','jack');
        $uID = array(12,13,10);
        $vID = array(12,13,10);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
        }

        // Test ASC
        $result = $this->DAO->getAllInstances("ASC");
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 3);
        $users = array('jack','stuart','jill');
        $uID = array(10,13,12);
        $vID = array(10,13,12);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
        }

        // Test ASC Only Active
        $result = $this->DAO->getAllInstances("ASC", true);
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $users = array('jack','jill');
        $uID = array(10,12);
        $vID = array(10,12);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
        }

        // Test ASC Facebook
        $result = $this->DAO->getAllInstances("ASC", false, 'facebook');
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $users = array('Jillian Dickerson','Paul Clark');
        $uID = array(15,16);
        $vID = array(15,16);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
        }

        // Test ASC only active Facebook
        $result = $this->DAO->getAllInstances("ASC", true, 'facebook');
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $users = array('Jillian Dickerson');
        $uID = array(15);
        $vID = array(15);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
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
            $this->assertEqual(count($result), 5);
            $users = array('jill','Paul Clark','Jillian Dickerson','stuart','jack');
            $uID = array(12,16,15,13,10);
            $vID = array(12,16,15,13,10);
            foreach($result as $id=>$i){
                $this->assertIsA($i, "Instance");
                $this->assertEqual($i->network_username, $users[$id]);
                $this->assertEqual($i->network_user_id, $uID[$id]);
                $this->assertEqual($i->network_viewer_id, $vID[$id]);
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

    public function testGetByOwnerWithStatus(){
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

        $result = $this->DAO->getByOwnerWithStatus($owner);
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $users = array('jack','jill');
        $user_ids = array(10, 12);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $user_ids[$id]);
            if ($i->network_user_id == 10) {
                $this->assertEqual($i->auth_error, 'There has been an error.');
            } else {
                $this->assertEqual($i->auth_error, '');
            }
        }
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

        // Test is-admin twitter
        $result = $this->DAO->getByOwnerAndNetwork($owner, 'twitter');
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 3);
        $users = array('jill','stuart','jack');
        $uID = array(12,13,10);
        $vID = array(12,13,10);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
        }

        // Test is-admin twitter, active only
        $result = $this->DAO->getByOwnerAndNetwork($owner, 'twitter', true, true);
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2); //jill and jack active, stuart is not
        $users = array('jill','jack');
        $uID = array(12,10);
        $vID = array(12,10);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
        }

        // Test is-admin facebook
        $result = $this->DAO->getByOwnerAndNetwork($owner, 'facebook');
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $users = array('Paul Clark','Jillian Dickerson');
        $uID = array(16,15);
        $vID = array(16,15);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
        }

        // Test is-admin Twitter, forced not
        $result = $this->DAO->getByOwnerAndNetwork($owner, 'twitter', true);
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $users = array('jill','jack');
        $uID = array(12,10);
        $vID = array(12,10);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
        }

        // Test not admin twitter
        $owner->is_admin = false;
        $result = $this->DAO->getByOwnerAndNetwork($owner, 'twitter');
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $users = array('jill','jack');
        $uID = array(12,10);
        $vID = array(12,10);
        foreach($result as $id=>$i){
            $this->assertIsA($i, "Instance");
            $this->assertEqual($i->network_username, $users[$id]);
            $this->assertEqual($i->network_user_id, $uID[$id]);
            $this->assertEqual($i->network_viewer_id, $vID[$id]);
        }

        $owner->id = 3;
        //Try empty
        $result = $this->DAO->getByOwnerAndNetwork($owner, 'twitter');;
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 0);
    }

    public function testSetPublic(){
        $result = $this->DAO->setPublic(1, true);
        $this->assertEqual($result, 1, "Count UpdateToTrue (%s)");
        //Testing if it really works
        $result = $this->DAO->getByUsername('jack');
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertTrue($result->is_public);

        $result = $this->DAO->setPublic(1, false);
        $this->assertEqual($result, 1, "Count UpdateToFalse (%s)");
        //Testing if it really works
        $result = $this->DAO->getByUsername('jack');
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertFalse($result->is_public);
    }

    public function testSetActive(){
        $result = $this->DAO->setActive(1, false);
        $this->assertEqual($result, 1, "Count UpdateToFalse (%s)");
        //Testing if it really works
        $result = $this->DAO->getByUsername('jack');
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertFalse($result->is_active);

        $result = $this->DAO->setActive(1, true);
        $this->assertEqual($result, 1, "Count UpdateToTrue (%s)");
        //Testing if it really works
        $result = $this->DAO->getByUsername('jack');
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertTrue($result->is_active);
    }

    public function testSave(){
        //First we need to generate some more TestData(tm)
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
                $reply_to = 'NULL';
            }
            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_id, 'post_id'=>$post_id,
            'author_user_id'=>$sender, 'post_text'=>$data, 'pub_date'=>'-'.$number.'h',
            'in_reply_to_user_id'=>$reply_to));
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
                'follower_id'=>$follow['follower']));
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
        array('id'=>10, 'user_name'=>'jack'),
        array('id'=>12, 'user_name'=>'jill'),
        array('id'=>13, 'user_name'=>'stuart'),
        array('id'=>15, 'user_name'=>'Jillian Dickerson'),
        array('id'=>16, 'user_name'=>'Paul Clark')
        );
        foreach($users as $user){
            $builders[] = FixtureBuilder::build('users', $user);
        }

        //Now load the instance in question
        $i = $this->DAO->getByUsername('jack');

        //Edit it.
        $i->last_post_id = 512;
        $i->is_archive_loaded_follows = 1;
        $i->is_archive_loaded_replies = 1;

        //First make sure that last run data is correct before we start.
        $result = $this->DAO->getInstanceFreshestOne();
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jill');
        $this->assertEqual($result->network_user_id, 12);
        $this->assertEqual($result->network_viewer_id, 12);

        //Save it
        $count = $this->DAO->save($i, 1024);

        $this->assertEqual($count, 1);

        //Load it for testing
        $result = $this->DAO->getByUsername('jack');
        $this->assertEqual($result->total_posts_by_owner, 1024);
        $this->assertEqual($result->last_post_id, 512);
        $this->assertNull($result->total_replies_in_system);
        $this->assertEqual($result->total_follows_in_system, $follows);
        $this->assertEqual($result->total_posts_in_system, $posts);
        $this->assertTrue($result->is_archive_loaded_follows);
        $this->assertTrue($result->is_archive_loaded_replies);

        //Check if it is the update updated last Run.
        $result = $this->DAO->getInstanceFreshestOne();
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);

        // Check if the stats were correctly calculated and saved
        // post per are limited to a max of 25, see getInstanceUserStats()
        $posts_per = ($posts > 25) ? 25 : $posts;
        //        $this->assertEqual(round($result->posts_per_day), $posts_per);
        $this->assertEqual($result->posts_per_week, $posts_per);
        $this->assertEqual($result->percentage_replies, round($replies / $posts * 100, 2));
        $this->assertEqual($result->percentage_links, round($links / $posts * 100, 2));

        //Still needs tests for:
        //earliest_reply_in_system
        //earliest_post_in_system
    }

    public function testSaveNoPosts(){
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('id'=>10, 'user_name'=>'jack'));

        //Load the instance
        $instance = $this->DAO->getByUsername('jack');

        // This will make the test fail if PHP warnings are generated when an instance has no posts
        $this->DAO->save($instance, 1024);
    }

    public function testUpdateLastRun(){
        //First make sure that the data is correct before we start.
        $result = $this->DAO->getInstanceFreshestOne();
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jill');
        $this->assertEqual($result->network_user_id, 12);
        $this->assertEqual($result->network_viewer_id, 12);

        //preform the update, and check the result.
        $result = $this->DAO->updateLastRun(1);
        $this->assertEqual($result, 1);

        //Check if it is the update.
        $result = $this->DAO->getInstanceFreshestOne();
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);
    }

    public function testIsUserConfigured(){
        // Test user that is Configured
        $result = $this->DAO->isUserConfigured("jack", "twitter");
        $this->assertTrue($result);

        // Test non-existing user
        $result = $this->DAO->isUserConfigured("no one", "facebook");
        $this->assertFalse($result);
    }

    public function testIsInstancePublic(){
        // Test private instance
        $result = $this->DAO->isInstancePublic("jack", "twitter");
        $this->assertFalse($result);

        // Test public instance
        $result = $this->DAO->isInstancePublic("stuart", "twitter");
        $this->assertTrue($result);

        // Test non-existent instance
        $result = $this->DAO->isInstancePublic("no one", "facebook");
        $this->assertFalse($result);
    }

    public function testGetByUserAndViewerId() {
        $this->DAO = new InstanceMySQLDAO();
        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>17,
        'network_username'=>'Jillian Micheals', 'network'=>'facebook', 'network_viewer_id'=>15,
        'crawler_last_run'=>'2010-01-01 12:00:01', 'is_active'=>1));

        $result = $this->DAO->getByUserAndViewerId(10, 10, 'twitter');
        $this->assertEqual($result->network_username, 'jack');

        $result = $this->DAO->getByUserAndViewerId(17, 15, 'facebook');
        $this->assertEqual($result->network_username, 'Jillian Micheals');
    }

    public function testGetByViewerId() {
        $this->DAO = new InstanceMySQLDAO();
        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>17,
        'network_username'=>'Jillian Micheals', 'network'=>'facebook', 'network_viewer_id'=>15,
        'crawler_last_run'=>'2010-01-01 12:00:01', 'is_active'=>1));

        $result = $this->DAO->getByViewerId(15);
        $this->assertEqual($result[0]->network_username, 'Jillian Dickerson');
        $this->assertEqual($result[1]->network_username, 'Jillian Micheals');
    }

    public function testGetByUsernameOnNetwork() {
        $this->DAO = new InstanceMySQLDAO();
        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>17,
        'network_username'=>'salma', 'network'=>'facebook', 'network_viewer_id'=>15,
        'crawler_last_run'=>'2010-01-01 12:00:01', 'is_active'=>1));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>18,
        'network_username'=>'salma', 'network'=>'facebook page', 'network_viewer_id'=>15,
        'crawler_last_run'=>'2010-01-01 12:00:01', 'is_active'=>1));

        $result = $this->DAO->getByUsernameOnNetwork('salma', 'facebook');
        $this->assertEqual($result->network_username, 'salma');
        $this->assertEqual($result->network, 'facebook');
        $this->assertEqual($result->network_user_id, 17);

        $result = $this->DAO->getByUsernameOnNetwork('salma', 'facebook page');
        $this->assertEqual($result->network_username, 'salma');
        $this->assertEqual($result->network, 'facebook page');
        $this->assertEqual($result->network_user_id, 18);
    }

    public function testGetInstanceFreshestPublicOne() {
        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>'501',
        'network_username'=>'mememe', 'is_public'=>'1', 'is_activated'=>'1', 'crawler_last_run'=>'-1h'));
        //try one
        $result = $this->DAO->getInstanceFreshestPublicOne();
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'mememe');
        $this->assertEqual($result->network_user_id, 501);

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>'502',
        'network_username'=>'mememetoo', 'is_public'=>'1', 'is_activated'=>'1', 'crawler_last_run'=>'-30m'));
        //try one
        $result = $this->DAO->getInstanceFreshestPublicOne();
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'mememetoo');
        $this->assertEqual($result->network_user_id, 502);
    }

    public function testGetPublicInstances() {
        $result = $this->DAO->getPublicInstances();
        $this->assertIsA($result, "Array");
        $this->assertEqual(sizeof($result), 1);
        $this->assertIsA($result[0], "Instance");
        $this->assertEqual($result[0]->network_username, "Jillian Dickerson" );
    }

    public function testUpdateInstanceUsername() {
        $this->DAO = new InstanceMySQLDAO();
        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>17,
        'network_username'=>'johndoe', 'network'=>'twitter', 'network_viewer_id'=>15,
        'crawler_last_run'=>'2010-01-01 12:00:01', 'is_active'=>1));

        $instance = $this->DAO->getByUsername('johndoe');
        $update_cnt = $this->DAO->updateUsername($instance->id, 'johndoe2');
        $this->assertEqual(1, $update_cnt);
        $instance = $this->DAO->getByUsername('johndoe');
        $this->assertNull($instance);
        $instance = $this->DAO->getByUsername('johndoe2');
        $this->assertEqual($instance->network_username, "johndoe2" );
    }

    public function testGetActiveInstancesStalestFirstForOwnerByNetworkNoAuthError() {
        $this->builders[] = FixtureBuilder::build('instances', array('network_user_id'=>17, 'network_username'=>'yaya',
        'network'=>'twitter', 'network_viewer_id'=>17, 'crawler_last_run'=>'2010-01-21 12:00:00', 'is_active'=>1,
        'is_public'=>0));

        $this->builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>3, 'instance_id'=>6,
        'auth_error'=>''));

        $this->DAO = new InstanceMySQLDAO();
        $owner = new Owner();
        $owner->id = 2;

        //Owner isn't an admin
        $owner->is_admin = false;

        //Should only return 1 result
        $result = $this->DAO->getActiveInstancesStalestFirstForOwnerByNetworkNoAuthError($owner, 'twitter');
        $this->assertEqual(sizeof($result), 1);
        $this->assertEqual($result[0]->id, 2);
        $this->assertEqual($result[0]->network_username, "jill");

        //Owner is an admin
        $owner->is_admin = true;

        //Should return 2 results
        $result = $this->DAO->getActiveInstancesStalestFirstForOwnerByNetworkNoAuthError($owner, 'twitter');
        $this->assertEqual(sizeof($result), 2);
        $this->assertEqual($result[0]->id, 2);
        $this->assertEqual($result[0]->network_username, "jill");
        $this->assertEqual($result[1]->id, 6);
        $this->assertEqual($result[1]->network_username, "yaya");
    }

    public function testSetPostArchiveLoaded() {
        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>17,
        'network_username'=>'johndoe', 'network'=>'twitter', 'network_viewer_id'=>15,
        'crawler_last_run'=>'2010-01-01 12:00:01', 'is_active'=>1, 'is_archive_loaded_posts'=>0));

        $this->DAO->setPostArchiveLoaded(17, 'twitter');
        $result = $this->DAO->getByUsername('johndoe');

        $this->assertTrue($result->is_archive_loaded_posts);
    }

}
