<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilder.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.PublicTimelineController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkTank.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';


/**
 * Test of PublicTimelineController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfPublicTimelineController extends ThinkTankUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('PublicTimelineController class test');
    }

    public function setUp(){
        parent::setUp();

        $config = Config::getInstance();
        $config->setValue('cache_pages', false);

        //Add instance_owner
        $q = "INSERT INTO tt_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);

        //Insert test data into test table
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev',
        'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);

        //Make public
        $q = "INSERT INTO tt_instances (id, network_user_id, network_username, is_public) VALUES (1, 13, 'ev', 1);";
        $this->db->exec($q);

        //Add a bunch of posts
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES ($counter, 13, 'ev', 
            'Ev Williams', 'avatar.jpg', 'This is post $counter', 'web', 
            '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5);";
            $this->db->exec($q);
            $counter++;
        }

    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new PublicTimelineController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    public function testControlNoParams() {
        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        $this->assertTrue(strpos( $results, "Latest public posts and public replies") > 0, "default timeline");

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Public Timeline');
        $this->assertEqual($v_mgr->getTemplateDataItem('logo_link'), 'public.php');

        $this->assertEqual($controller->getCacheKeyString(), 'public.tpl-timeline-1', 'Cache key');
    }

    public function testControlNoParamsLoggedIn() {
        $_SESSION['user'] = 'me@example.com';

        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        $this->assertTrue(strpos( $results, "Latest public posts and public replies") > 0, "default timeline");

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Public Timeline');
        $this->assertEqual($v_mgr->getTemplateDataItem('logo_link'), 'public.php');

        $this->assertEqual($controller->getCacheKeyString(), 'public.tpl-me@example.com-timeline-1', 'Cache key');
    }

    public function testControlPage2DefaultList() {
        $_GET["page"] = '2';

        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        $this->assertTrue(strpos( $results, "Latest public posts and public replies") > 0, "default timeline, page 2");
        $this->assertTrue(strpos( $results, "Page 2") > 0, "default timeline, page 2");

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('current_page'), '2');
        $this->assertEqual($v_mgr->getTemplateDataItem('prev_page'), '1');

        $this->assertEqual($controller->getCacheKeyString(), 'public.tpl-2-timeline', 'Cache key');
    }

    public function testControlMostReplies() {
        $_GET["v"] = 'mostreplies';

        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        $this->assertTrue(strpos( $results, "Posts that have been replied to most often") > 0, "most replies list");

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'Most replied to');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), 'Posts that have been replied to most often');

        $this->assertEqual($controller->getCacheKeyString(), 'public.tpl-mostreplies-1', 'Cache key');
    }

    public function testControlMostRetweets() {
        $_GET["v"] = 'mostretweets';
        $_GET["page"] = 2;

        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        $this->assertTrue(strpos( $results, "Posts that have been forwarded most often") > 0, "most replies list");

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'Most forwarded');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), 'Posts that have been forwarded most often');

        $this->assertEqual($controller->getCacheKeyString(), 'public.tpl-mostretweets-2', 'Cache key');
    }

    public function testControlSinglePostExists() {
        $_GET['t'] = 39;
        $_GET['n'] = 'twitter';

        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Public Post Replies');
        $this->assertEqual($v_mgr->getTemplateDataItem('post')->post_text, 'This is post 39');
    }

    public function testControlSinglePostDoesNotExist() {
        $_GET['t'] = 51;
        $_GET['n'] = 'twitter';

        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Public Post Replies');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Post 51 on Twitter is not in ThinkTank.');
    }

    public function testControlUserDashboardPrivateInstance() {
        $_GET["u"] = 'ginatrapani';
        $_GET["n"] = 'twitter';

        $instance_builder = FixtureBuilder::build('instances', array(
            'network_username'=>'ginatrapani',
            'network_user_id'=>'930061',
            'network'=>'twitter',
            'is_public'=>0)
        );

        $user_builder = FixtureBuilder::build('users', array(
            'user_name'=>'ginatrapani',
            'user_id'=>'930061',
            'network'=>'twitter')
        );

        $id = 100;
        $counter = 0;
        $builders = array();
        while ($counter < 10) {
            $id += $counter;
            if ($counter <= 5) {
                $builders[] = FixtureBuilder::build('posts', array(
                   'id'=>$id, 
                   'post_id'=>(144+$counter),
                   'author_user_id'=>930061,
                   'author_username'=>'ginatrapani',
                   'pub_date'=>'-'.$counter.'d',
                   'reply_count_cache'=>$counter));
            } else {
                $builders[] = FixtureBuilder::build('posts', array(
                   'id'=>$id, 
                    'post_id'=>(144+$counter),
                    'author_user_id'=>930061,
                    'author_username'=>'ginatrapani',
                    'pub_date'=>'-'.$counter.'d',
                    'retweet_count_cache'=>$counter));
            }
            $counter++;
        }

        //first, add some people
        $user1_builder = FixtureBuilder::build('users', array(
            'user_name'=>'jack',
            'user_id'=>'2001',
            'network'=>'twitter',
            'follower_count'=>'10050',
            'friend_count'=>'10')
        );
        $user2_builder = FixtureBuilder::build('users', array(
            'user_name'=>'anildash',
            'user_id'=>'123456',
            'network'=>'twitter',
            'follower_count'=>'11111',
            'friend_count'=>'12')
        );

        $follower_builders = array();
        $follower_builders[] = FixtureBuilder::build('follows', array('user_id'=>'930061', 'follower_id'=>'2001',
        'active'=>1, 'network'=>'twitter'));
        $follower_builders[] = FixtureBuilder::build('follows', array('user_id'=>'930061', 'follower_id'=>'123456',
        'active'=>1, 'network'=>'twitter'));


        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        $this->assertTrue(strpos( $results, "ginatrapani") > 0);

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'),
        "ginatrapani on Twitter isn't set up on this ThinkTank installation.");
    }

    public function testControlUserDashboardUserDoesntExist() {
        $_GET["u"] = 'idontexist';
        $_GET["n"] = 'somenetwork';

        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'),
        "idontexist on Somenetwork isn't set up on this ThinkTank installation.");
    }

    public function testControlUserDashboard() {
        $_GET["u"] = 'ginatrapani';
        $_GET["n"] = 'twitter';

        $instance_builder = FixtureBuilder::build('instances', array(
            'network_username'=>'ginatrapani',
            'network_user_id'=>'930061',
            'network'=>'twitter',
            'is_public'=>1)
        );

        $user_builder = FixtureBuilder::build('users', array(
            'user_name'=>'ginatrapani',
            'user_id'=>'930061',
            'network'=>'twitter')
        );

        $id = 100;
        $counter = 0;
        $builders = array();
        while ($counter < 10) {
            $id += $counter;
            if ($counter <= 5) {
                $builders[] = FixtureBuilder::build('posts', array(
                   'id'=>$id, 
                   'post_id'=>(144+$counter),
                   'author_user_id'=>930061,
                   'author_username'=>'ginatrapani',
                   'pub_date'=>'-'.$counter.'d',
                   'reply_count_cache'=>$counter));
            } else {
                $builders[] = FixtureBuilder::build('posts', array(
                   'id'=>$id, 
                    'post_id'=>(144+$counter),
                    'author_user_id'=>930061,
                    'author_username'=>'ginatrapani',
                    'pub_date'=>'-'.$counter.'d',
                    'retweet_count_cache'=>$counter));
            }
            $counter++;
        }

        //first, add some people
        $user1_builder = FixtureBuilder::build('users', array(
            'user_name'=>'jack',
            'user_id'=>'2001',
            'network'=>'twitter',
            'follower_count'=>'10050',
            'friend_count'=>'10')
        );
        $user2_builder = FixtureBuilder::build('users', array(
            'user_name'=>'anildash',
            'user_id'=>'123456',
            'network'=>'twitter',
            'follower_count'=>'11111',
            'friend_count'=>'12')
        );

        $follower_builders = array();
        $follower_builders[] = FixtureBuilder::build('follows', array('user_id'=>'930061', 'follower_id'=>'2001',
        'active'=>1, 'network'=>'twitter'));
        $follower_builders[] = FixtureBuilder::build('follows', array('user_id'=>'930061', 'follower_id'=>'123456',
        'active'=>1, 'network'=>'twitter'));


        $controller = new PublicTimelineController(true);
        $results = $controller->control();
        $this->assertTrue(strpos( $results, "ginatrapani") > 0);

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('user_details'), 'User');
        $this->assertIsA($v_mgr->getTemplateDataItem('most_replied_to_alltime'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('most_replied_to_alltime')), 5);
        $this->assertIsA($v_mgr->getTemplateDataItem('most_replied_to_1wk'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('most_replied_to_1wk')), 5);
        $this->assertIsA($v_mgr->getTemplateDataItem('most_retweeted_alltime'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('most_retweeted_1wk')), 2);
        $this->assertIsA($v_mgr->getTemplateDataItem('least_likely_followers'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('least_likely_followers')), 2);

        $this->assertEqual($controller->getCacheKeyString(), 'public.tpl-ginatrapani-twitter', 'Cache key');
    }
}
