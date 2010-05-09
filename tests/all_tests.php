<?php 
require_once "init.tests.php";
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

$RUNNING_ALL_TESTS = true;

/* CORE CODE TESTS */
require_once $SOURCE_ROOT_PATH.'tests/channeldao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/config_test.php';
require_once $SOURCE_ROOT_PATH.'tests/database_test.php';
require_once $SOURCE_ROOT_PATH.'tests/followdao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/frontend_test.php';
require_once $SOURCE_ROOT_PATH.'tests/instancedao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/instancechanneldao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/linkdao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/log_test.php';
require_once $SOURCE_ROOT_PATH.'tests/mysqldao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/ownerinstancedao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/plugindao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/postdao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/userdao_test.php';

$coretest = & new GroupTest('Core tests');

$coretest->addTestCase(new TestOfChannelDAO());
$coretest->addTestCase(new TestOfConfig());
$coretest->addTestCase(new TestOfDatabase());
$coretest->addTestCase(new TestOfFollowDAO());
$coretest->addTestCase(new TestOfThinkTankFrontEnd());
$coretest->addTestCase(new TestOfInstanceDAO());
$coretest->addTestCase(new TestOfInstanceChannelDAO());
$coretest->addTestCase(new TestOfLinkDAO());
$coretest->addTestCase(new TestOfLogging());
$coretest->addTestCase(new TestOfMySQLDAO());
$coretest->addTestCase(new TestOfOwnerInstanceDAO());
$coretest->addTestCase(new TestOfPluginDAO());
$coretest->addTestCase(new TestOfPostDAO());
$coretest->addTestCase(new TestOfUserDAO());
$coretest->run( new TextReporter());


/* PLUGIN TESTS */
require_once $SOURCE_ROOT_PATH.'webapp/plugins/expandurls/tests/expandurlsplugin_test.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/tests/facebookcrawler_test.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/flickrapi_test.php';
//TODO: Figure out why this test runs individually but not in a group
//require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/flickrplugin_test.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/twitterapiaccessoroauth_test.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/twittercrawler_test.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/twitteroauth_test.php';


$plugintest = & new GroupTest('Plugin tests');

$plugintest->addTestCase(new TestOfExpandURLsPlugin());
$plugintest->addTestCase(new TestOfFacebookCrawler());
$plugintest->addTestCase(new TestOfFlickrAPIAccessor());
//TODO: Figure out why this test runs individually but not in a group
//$plugintest->addTestCase(new TestOfFlickrPlugin());
$plugintest->addTestCase(new TestOfTwitterCrawler());
$plugintest->addTestCase(new TestOfTwitterAPIAccessorOAuth());
$plugintest->addTestCase(new TestOfTwitterOAuth());

$plugintest->run( new TextReporter());


?>
