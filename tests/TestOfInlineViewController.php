<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';

/**
 * Test of InlineViewController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfInlineViewController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('InlineViewController class test');
    }

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');

        //Add owner
        $q = "INSERT INTO tu_owners SET id=1, full_name='ThinkUp J. User', email='me@example.com', is_activated=1,
        pwd='XXX', activation_code='8888'";
        $this->db->exec($q);

        //Add instance_owner
        $q = "INSERT INTO tu_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);

        //Insert test data into test table
        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev',
        'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, last_updated) VALUES (12, 'jack',
        'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);

        //Make public
        $q = "INSERT INTO tu_instances (id, network_user_id, network_username, is_public) VALUES (1, 13, 'ev', 1);";
        $this->db->exec($q);

        //Add a bunch of posts
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES ($counter, 13, 'ev', 
            'Ev Williams', 'avatar.jpg', 'This is post $counter', 'web', 
            '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5);";
            $this->db->exec($q);
            $counter++;
        }

        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_post_id, 
        in_reply_to_user_id, network) 
        VALUES (41, 13, 'ev', 'Ev Williams', 'avatar.jpg', 'This post is in reply to jacks post 50', 'web', 
        '2006-01-01 00:00:00', ".rand(0, 4).", 5, 50, 12, 'twitter');";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, network) 
        VALUES (50, 12, 'jack', 'Jack', 'avatar.jpg', 'Ev replied to this post', 'web', 
        '2006-01-01 00:00:00', ".rand(0, 4).", 5, 'twitter');";
        $this->db->exec($q);

    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new InlineViewController(true);
        $this->assertTrue(isset($controller), 'constructor test');
        $this->assertIsA($controller, 'InlineViewController');
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Inline View');
    }

    public function testControlNotLoggedIn() {
        $controller = new InlineViewController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testControlLoggedInWithOutReqdParams() {
        $this->simulateLogin('me@example.com');
        $controller = new InlineViewController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), '', 'Header not set');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), '', 'Description not set');
        $this->assertEqual($v_mgr->getTemplateDataItem('infomsg'), 'No user to retrieve.',
        'Error re: missing param set');
    }

    public function testControlLoggedInWithReqdParams() {
        //must be logged in
        $this->simulateLogin('me@example.com');
        //required params
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        //no d param specified
        $controller = new InlineViewController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'All', 'Header');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), 'All tweets', 'Description');
        $this->assertIsA($v_mgr->getTemplateDataItem('all_tweets'), 'array', 'Array of tweets');
        $this->assertTrue($v_mgr->getTemplateDataItem('is_searchable'));
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('all_tweets')), 15, '15 posts in listing');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(),
        $config->getValue('source_root_path').
       'webapp/plugins/twitter/view/twitter.inline.view.tpl-me@example.com-ev-twitter-tweets-all', 'Cache key');
    }

    public function testControlLoggedInPosts() {
        //must be logged in
        $this->simulateLogin('me@example.com');
        //required params
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['d'] = 'tweets-all';
        $controller = new InlineViewController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'All', 'Header');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), 'All tweets', 'Description');
        $this->assertIsA($v_mgr->getTemplateDataItem('all_tweets'), 'array', 'Array of tweets');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('all_tweets')), 15, '15 posts in listing');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(),
        $config->getValue('source_root_path').
       'webapp/plugins/twitter/view/twitter.inline.view.tpl-me@example.com-ev-twitter-tweets-all', 'Cache key');
    }

    public function testControlLoggedInConversations() {
        //must be logged in
        $this->simulateLogin('me@example.com');
        //required params
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['d'] = 'tweets-convo';
        $controller = new InlineViewController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'Conversations', 'Header');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), '', 'Description');
        $this->assertIsA($v_mgr->getTemplateDataItem('author_replies'), 'array', 'Array of tweets');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('author_replies')), 1, '1 posts in listing');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(),
        $config->getValue('source_root_path').
       'webapp/plugins/twitter/view/twitter.inline.view.tpl-me@example.com-ev-twitter-tweets-convo', 'Cache key');
    }

    public function testControlLoggedInPeople() {
        //first, add some people
        $user1_builder = FixtureBuilder::build('users', array(
            'user_name'=>'ginatrapani',
            'user_id'=>'930061',
            'network'=>'twitter')
        );
        $user2_builder = FixtureBuilder::build('users', array(
            'user_name'=>'anildash',
            'user_id'=>'123456',
            'network'=>'twitter')
        );

        $follower_builders = array();
        $follower_builders[] = FixtureBuilder::build('follows', array('user_id'=>'930061', 'follower_id'=>'13'));
        $follower_builders[] = FixtureBuilder::build('follows', array('user_id'=>'123456', 'follower_id'=>'13'));

        //must be logged in
        $this->simulateLogin('me@example.com');
        //required params
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['d'] = 'friends-mostactive';
        $controller = new InlineViewController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('header'), 'Chatterboxes', 'Header');
        $this->assertEqual($v_mgr->getTemplateDataItem('description'), '', 'Description');
        $this->assertIsA($v_mgr->getTemplateDataItem('people'), 'array', 'Array of users');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('people')), 2, '2 users in listing');

        $config = Config::getInstance();
        $this->assertEqual($controller->getCacheKeyString(),
        $config->getValue('source_root_path').
       'webapp/plugins/twitter/view/twitter.inline.view.tpl-me@example.com-ev-twitter-friends-mostactive', 'Cache key');
    }
}