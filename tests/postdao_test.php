<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("class.MySQLDAO.php");
require_once ("class.Database.php");
require_once ("class.Post.php");
require_once ("config.inc.php");


class TestOfPostDAO extends UnitTestCase {
    function TestOfPostDAO() {
        $this->UnitTestCase('PostDAO class test');
    }
    
    function setUp() {
        global $THINKTANK_CFG;
        
        //Override default CFG values
        $THINKTANK_CFG['db_name'] = "thinktank_tests";
        
        $this->logger = new Logger($THINKTANK_CFG['log_location']);
        $this->db = new Database($THINKTANK_CFG);
        $this->conn = $this->db->getConnection();
        
        //Create all the tables based on the build script
        $create_db_script = file_get_contents($THINKTANK_CFG['source_root_path']."sql/build-db_mysql.sql");
        $create_db_script = str_replace("ALTER DATABASE thinktank", "ALTER DATABASE thinktank_tests", $create_db_script);
        $create_statements = split(";", $create_db_script);
        foreach ($create_statements as $q) {
            if (trim($q) != '') {
                $this->db->exec($q.";");
            }
        }
        
        //TODO: Insert test data into post table
        //$this->db->exec($q);
        
    }
    
    function tearDown() {
        //Delete test data
        $q = "DROP TABLE `tt_follows`, `tt_instances`, `tt_links`, `tt_owners`, `tt_owner_instances`, `tt_users`, `tt_user_errors`, `tt_plugins`, `tt_plugin_options`, `tt_posts`, `tt_post_errors`, `tt_replies`;";
        $this->db->exec($q);
        
        //Clean up
        $this->db->closeConnection($this->conn);
        
    }
    
    function testIsRetweet() {
    
        $startwithcolon = "RT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $nostartnocolon = "Agreed: RT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight people, mid-conversation";
        $startwithcolonspaces = "RT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $startwithcoloncutoff = "RT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $lowwercase = "rt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        
        $o = 'ginatrapani';
        $this->assertTrue(RetweetDetector::isRetweet($startwithcolon, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($nostartnocolon, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($startwithcolonspaces, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($startwithcoloncutoff, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($lowwercase, 'ginatrapani'));
    }
    
    function testDetectRetweets() {
        $recent_tweets = array( new Post(array('post_id'=>9021481076, 'post_text'=>'guilty pleasure: dropping the "my wife" bomb on unsuspecting straight people, mid-conversation')), new Post(array('post_id'=>9020176425, 'post_text'=>"a Google fangirl's take: no doubt Buzz's privacy issues are seriously problematic, but at least they're iterating quickly and openly.")), new Post(array('post_id'=>9031523906, 'post_text'=>"one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx @voiceofsandiego, @dagnysalas, & @samuelhodgson")), new Post(array('post_id'=>8925077246, 'post_text'=>"how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH")));
        
        $startwithcolon = "RT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $nostartnocolon = "Agreed: RT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight people, mid-conversation";
        $startwithcolonspaces = "RT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $startwithcoloncutoff = "RT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $lowwercase = "rt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $nonexistent = "rt @ginatrapani this is a non-existent tweet";

        
        $this->assertTrue(RetweetDetector::detectOriginalTweet($nostartnocolon, $recent_tweets) == 9021481076);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($startwithcolonspaces, $recent_tweets) == 8925077246);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($startwithcoloncutoff, $recent_tweets) == 9031523906);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($startwithcolon, $recent_tweets) == 8925077246);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($nonexistent, $recent_tweets) === false);
    }
    
}
?>
