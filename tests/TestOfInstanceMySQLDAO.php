<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/Loremipsum/LoremIpsum.class.php';

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.InstanceMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Owner.php';

class TestOfInstanceMySQLDAO extends ThinkTankUnitTestCase {
    protected $DAO;
    function TestOfInstanceMySQLDAO() {
        $this->UnitTestCase('InstanceDAO class test');
    }

    function setUp() {
        parent::setUp();
        $this->DAO = new InstanceMySQLDAO();
        $q  = "INSERT INTO tt_instances ";
        $q .= "(`network_user_id`, `network_username`, `network`, ";
        $q .= "`network_viewer_id`, `crawler_last_run`, `is_active`) VALUES ";
        $q .= "(10 , 'jack', 'twitter', 10, '1988-01-20 12:00:00', 1), ";
        $q .= "(12, 'jill', 'twitter', 12, '2010-01-20 12:00:00', 1), ";
        $q .= "(13 , 'stuart', 'twitter', 13, '2010-01-01 12:00:00', 0), ";
        $q .= "(15 , 'Jillian Dickerson', 'facebook', 15, '2010-01-01 12:00:01', 1), ";
        $q .= "(16 , 'Paul Clark', 'facebook', 16, '2010-01-01 12:00:02', 0) ";
        // $q .= "(17 , 'Jillian Micheals', 'facebook', 15, '2010-01-01 12:00:01', 1) ";
        PDODAO::$PDO->exec($q);

        $q  = "INSERT INTO  `tt_owner_instances` (`owner_id` , `instance_id`) ";
        $q .= "VALUES ('2',  '1'), ('2', '2');";
        PDODAO::$PDO->exec($q);
    }

    function tearDown() {
        parent::tearDown();
    }

    function testInsert() {
        $result = $this->DAO->insert(11, 'ev');
        $this->assertEqual($result, 6);
        $i = $this->DAO->getByUserId(11);
        $this->assertEqual($i->network_user_id, 11);
        $this->assertEqual($i->network_viewer_id, 11);
        $this->assertEqual($i->network_username, 'ev');
        $this->assertEqual($i->network, 'twitter');

        $result = $this->DAO->insert(14, 'The White House Facebook Page', 'facebook', 10);
        $this->assertEqual($result, 7);
        $i = $this->DAO->getByUserId(14);
        $this->assertEqual($i->network_user_id, 14);
        $this->assertEqual($i->network_viewer_id, 10);
        $this->assertEqual($i->network_username, 'The White House Facebook Page');
        $this->assertEqual($i->network, 'facebook');
    }

    function testGetFreshestByOwnerId(){
        //try one
        $result = $this->DAO->getFreshestByOwnerId(2);
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jill');
        $this->assertEqual($result->network_user_id, 12);
        $this->assertEqual($result->network_viewer_id, 12);

        //Try a non existant one
        $result = $this->DAO->getFreshestByOwnerId(3);
        $this->assertNull($result);
    }

    function testGetInstanceOneByLastRun(){
        //Try Newest
        $result = $this->DAO->getInstanceOneByLastRun('DESC');
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jill');
        $this->assertEqual($result->network_user_id, 12);
        $this->assertEqual($result->network_viewer_id, 12);

        //Try Oldest
        $result = $this->DAO->getInstanceOneByLastRun('ASC');
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);

        $q = "TRUNCATE TABLE tt_instances ";
        PDODAO::$PDO->exec($q);

        //Try empty
        $result = $this->DAO->getInstanceOneByLastRun('ASC');
        $this->assertNull($result);
    }

    function testGetByUsername() {
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

    function testGetByUserId() {
        // data do exist
        $result = $this->DAO->getByUserId(10);
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);

        // data do not exist
        $result = $this->DAO->getByUserId(11);
        $this->assertNull($result);
    }

    function testGetAllInstances(){
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

    function testGetByOwner(){
        $data = array(
            'id'=>2,
            'user_name'=>'steven',
            'full_name'=>'Steven Warren',
            'user_email'=>'me@example.com',
            'last_login'=>'Yesterday',
            'is_admin'=>1
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

    function testGetByOwnerAndNetwork(){
        $data = array(
            'id'=>2,
            'user_name'=>'steven',
            'full_name'=>'Steven Warren',
            'user_email'=>'me@example.com',
            'last_login'=>'Yesterday',
            'is_admin'=>1
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

    function testSetPublic(){
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

    function testSetActive(){
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

    function testSave(){
        //First we need to generate some more TestData(tm)
        $loremIpsum = new LoremIpsumGenerator();
        //First in line is some posts 250 Randomly generated ones, some with mentions.
        $mentions = 0;
        $posts = 0;
        for($i=0; $i <= 250; $i++){
            $sender = rand(5,16);
            $data = $loremIpsum->getContent(17, 'plain');
            $postid = rand(1000, 1000000);
            while(isset($pic[$postid])){
                $postid = rand(1000, 1000000);
            }
            $pic[$postid] = true;

            $number = rand(1,8);
            if ($number == 1 or $number == 2){
                $data = "@jack ".$data;
                $mentions++;
            }
            elseif ($number == 3){
                $data = "@jill ".$data;
            }
            $q  = "INSERT INTO `tt_posts` (`post_id`, `author_user_id`, `post_text`) ";
            $q .= " VALUES ('".$postid."', '".$sender."', '".$data."');\n";
            PDODAO::$PDO->exec($q);
            if($sender == 10){
                $posts++;
            }
        }
        unset($pic);
        //Then generate some follows
        $follows = 0;
        for($i=0; $i<= 150; $i++){
            $follow = array("follower"=>rand(5,25), "following"=>rand(5,25));
            if(!isset($fd[$follow['following']."-".$follow['follower']])){
                $fd[$follow['following']."-".$follow['follower']] = true;
                $q  = "INSERT INTO `tt_follows` (`user_id`, `follower_id`) ";
                $q .= "VALUES ( '".$follow['following']."', '".$follow['follower']."');\n";
                PDODAO::$PDO->exec($q);
                if($follow['following'] == 10){
                    $follows++;
                }
            }
            else{
                $i = $i-1;
            }
        }

        //Lastly generate some users
        $users = array(
        array('id'=>10, 'name'=>'jack'),
        array('id'=>12, 'name'=>'jill'),
        array('id'=>13, 'name'=>'stuart'),
        array('id'=>15, 'name'=>'Jillian Dickerson'),
        array('id'=>16, 'name'=>'Paul Clark')
        );
        foreach($users as $user){
            $q  = "INSERT INTO `tt_users` (`user_id`, `user_name`) ";
            $q .= " VALUES ('".$user['id']."', '".$user['name']."') ";
            PDODAO::$PDO->exec($q);
        }

        //Now load the instance in question
        $i = $this->DAO->getByUsername('jack');

        //Edit it.
        $i->last_status_id = 512;
        $i->last_page_fetched_replies = 2;
        $i->last_page_fetched_tweets = 17;
        $i->is_archive_loaded_follows = 1;
        $i->is_archive_loaded_replies = 1;

        //First make sure that last run data is correct before we start.
        $result = $this->DAO->getInstanceOneByLastRun('DESC');
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
        $this->assertEqual($result->last_page_fetched_replies, 2);
        $this->assertEqual($result->last_status_id, 512);
        $this->assertEqual($result->last_page_fetched_tweets, 17);
        $this->assertEqual($result->total_replies_in_system, $mentions);
        $this->assertEqual($result->total_follows_in_system, $follows);
        $this->assertEqual($result->total_posts_in_system, $posts);
        $this->assertEqual($result->total_users_in_system, 5);
        $this->assertTrue($result->is_archive_loaded_follows);
        $this->assertTrue($result->is_archive_loaded_replies);

        //Check if it is the update updated last Run.
        $result = $this->DAO->getInstanceOneByLastRun('DESC');
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);

        //Still needs tests for:
        //earliest_reply_in_system
        //earliest_post_in_system
    }

    function testUpdateLastRun(){
        //First make sure that the data is correct before we start.
        $result = $this->DAO->getInstanceOneByLastRun('DESC');
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jill');
        $this->assertEqual($result->network_user_id, 12);
        $this->assertEqual($result->network_viewer_id, 12);

        //preform the update, and check the result.
        $result = $this->DAO->updateLastRun(1);
        $this->assertEqual($result, 1);

        //Check if it is the update.
        $result = $this->DAO->getInstanceOneByLastRun('DESC');
        $this->assertIsA($result, "Instance");
        $this->assertEqual($result->network_username, 'jack');
        $this->assertEqual($result->network_user_id, 10);
        $this->assertEqual($result->network_viewer_id, 10);
    }

    function testIsUserConfigured(){
        // Test user that is Configured
        $result = $this->DAO->isUserConfigured("jack");
        $this->assertTrue($result);

        // Test non-existing user
        $result = $this->DAO->isUserConfigured("no one");
        $this->assertFalse($result);
    }

    function testGetByUserAndViewerId() {
        $this->DAO = new InstanceMySQLDAO();
        $q  = "INSERT INTO tt_instances ";
        $q .= "(`network_user_id`, `network_username`, `network`, ";
        $q .= "`network_viewer_id`, `crawler_last_run`, `is_active`) VALUES ";
        $q .= "(17 , 'Jillian Micheals', 'facebook', 15, '2010-01-01 12:00:01', 1) ";
        PDODAO::$PDO->exec($q);

        $result = $this->DAO->getByUserAndViewerId(10, 10);
        $this->assertEqual($result->network_username, 'jack');

        $result = $this->DAO->getByUserAndViewerId(17, 15);
        $this->assertEqual($result->network_username, 'Jillian Micheals');
    }

    function testGetByViewerId() {
        $this->DAO = new InstanceMySQLDAO();
        $q  = "INSERT INTO tt_instances ";
        $q .= "(`network_user_id`, `network_username`, `network`, ";
        $q .= "`network_viewer_id`, `crawler_last_run`, `is_active`) VALUES ";
        $q .= "(17 , 'Jillian Micheals', 'facebook', 15, '2010-01-01 12:00:01', 1) ";
        PDODAO::$PDO->exec($q);

        $result = $this->DAO->getByViewerId(15);
        $this->assertEqual($result[0]->network_username, 'Jillian Dickerson');
        $this->assertEqual($result[1]->network_username, 'Jillian Micheals');
    }

    function testGetByUsernameOnNetwork() {
        $this->DAO = new InstanceMySQLDAO();
        $q  = "INSERT INTO tt_instances ";
        $q .= "(`network_user_id`, `network_username`, `network`, ";
        $q .= "`network_viewer_id`, `crawler_last_run`, `is_active`) VALUES ";
        $q .= "(17 , 'salma', 'facebook', 15, '2010-01-01 12:00:01', 1), ";
        $q .= "(18 , 'salma', 'facebook page', 15, '2010-01-01 12:00:01', 1) ";
        PDODAO::$PDO->exec($q);

        $result = $this->DAO->getByUsernameOnNetwork('salma', 'facebook');
        $this->assertEqual($result->network_username, 'salma');
        $this->assertEqual($result->network, 'facebook');
        $this->assertEqual($result->network_user_id, 17);

        $result = $this->DAO->getByUsernameOnNetwork('salma', 'facebook page');
        $this->assertEqual($result->network_username, 'salma');
        $this->assertEqual($result->network, 'facebook page');
        $this->assertEqual($result->network_user_id, 18);
    }
}