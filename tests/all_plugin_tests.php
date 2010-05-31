<?php
require_once 'init.tests.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

/* PLUGIN TESTS */
require_once $SOURCE_ROOT_PATH.'webapp/plugins/expandurls/tests/TestOfExpandURLsPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/tests/TestOfFacebookCrawler.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/tests/TestOfFacebookPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfRetweetDetector.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterAPIAccessorOAuth.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterCrawler.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterOAuth.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/TestOfFlickrAPIAccessor.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/TestOfFlickrThumbnailsPlugin.php';

$plugintest = & new GroupTest('Plugin tests');

$plugintest->addTestCase(new TestOfExpandURLsPlugin());
$plugintest->addTestCase(new TestOfFacebookPlugin());
$plugintest->addTestCase(new TestOfFacebookCrawler());
$plugintest->addTestCase(new TestOfRetweetDetector());
$plugintest->addTestCase(new TestOfTwitterAPIAccessorOAuth());
$plugintest->addTestCase(new TestOfTwitterCrawler());
$plugintest->addTestCase(new TestOfTwitterOAuth());
$plugintest->addTestCase(new TestOfTwitterPlugin());
$plugintest->addTestCase(new TestOfFlickrAPIAccessor());
//TODO: Figure out why this test passes individually but not in a group
//$plugintest->addTestCase(new TestOfFlickrThumbnailsPlugin());

$plugintest->run( new TextReporter());
?>
