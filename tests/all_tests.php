<?php

require_once('config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.tests.php");

require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/simpletest/mock_objects.php');

require_once('log_test.php');
require_once('config_test.php');
require_once('log_test.php');
require_once('database_test.php');
require_once('twitteroauth_test.php');
require_once('mysqldao_test.php');
require_once('userdao_test.php');
require_once('followdao_test.php');
require_once('frontend_test.php');

$test = &new GroupTest('All tests');
$test->addTestCase(new TestOfConfig());
$test->addTestCase(new TestOfLogging());
$test->addTestCase(new TestOfDatabase());
$test->addTestCase(new TestOfTwitterOAuth());
$test->addTestCase(new TestOfMySQLDAO());
$test->addTestCase(new TestOfUserDAO());
$test->addTestCase(new TestOfFollowDAO());
$test->addTestCase(new TestOfThinkTankFrontEnd());

//$test->run(new HtmlReporter());
$test->run(new TextReporter());

?>
