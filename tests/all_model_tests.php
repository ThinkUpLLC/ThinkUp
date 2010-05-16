<?php
require_once "init.tests.php";
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

/* MODEL TESTS */
require_once $SOURCE_ROOT_PATH.'tests/config_test.php';
require_once $SOURCE_ROOT_PATH.'tests/database_test.php';
require_once $SOURCE_ROOT_PATH.'tests/followdao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/instancedao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/linkdao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/log_test.php';
require_once $SOURCE_ROOT_PATH.'tests/mysqldao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/ownerinstancedao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/plugindao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/postdao_test.php';
require_once $SOURCE_ROOT_PATH.'tests/userdao_test.php';

$modeltest = & new GroupTest('Model tests');

$modeltest->addTestCase(new TestOfConfig());
$modeltest->addTestCase(new TestOfDatabase());
$modeltest->addTestCase(new TestOfFollowDAO());
$modeltest->addTestCase(new TestOfInstanceDAO());
$modeltest->addTestCase(new TestOfLinkDAO());
$modeltest->addTestCase(new TestOfLogging());
$modeltest->addTestCase(new TestOfMySQLDAO());
$modeltest->addTestCase(new TestOfOwnerInstanceDAO());
$modeltest->addTestCase(new TestOfPluginDAO());
$modeltest->addTestCase(new TestOfPostDAO());
$modeltest->addTestCase(new TestOfUserDAO());
$modeltest->run( new TextReporter());
?>
