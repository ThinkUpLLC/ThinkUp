<?php
require_once "init.tests.php";
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

/* PLUGIN TESTS */
require_once $SOURCE_ROOT_PATH.'webapp/plugins/expandurls/tests/expandurlsplugin_test.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/tests/facebookcrawler_test.php';
//TODO: Figure out why this test runs individually but not in a group
//require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/flickrapi_test.php';
//require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/flickrplugin_test.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/twitterapiaccessoroauth_test.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/twittercrawler_test.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/twitteroauth_test.php';

$plugintest = & new GroupTest('Plugin tests');

$plugintest->addTestCase(new TestOfExpandURLsPlugin());
$plugintest->addTestCase(new TestOfFacebookCrawler());
//TODO: Figure out why this test runs individually but not in a group
//$plugintest->addTestCase(new TestOfFlickrAPIAccessor());
//$plugintest->addTestCase(new TestOfFlickrPlugin());
$plugintest->addTestCase(new TestOfTwitterCrawler());
$plugintest->addTestCase(new TestOfTwitterAPIAccessorOAuth());
$plugintest->addTestCase(new TestOfTwitterOAuth());

$plugintest->run( new TextReporter());
?>
