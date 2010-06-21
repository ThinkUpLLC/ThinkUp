<?php
require_once 'init.tests.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

/* CONTROLLER TESTS */
require_once $SOURCE_ROOT_PATH.'tests/TestOfAccountConfigurationController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfInlineViewController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPublicTimelineController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPrivateDashboardController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPostController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfTestController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfTestAuthController.php';

$controller_test = & new GroupTest('Controller tests');
$controller_test->addTestCase(new TestOfAccountConfigurationController());
$controller_test->addTestCase(new TestOfInlineViewController());
$controller_test->addTestCase(new TestOfPublicTimelineController());
$controller_test->addTestCase(new TestOfPrivateDashboardController());
$controller_test->addTestCase(new TestOfPostController());
$controller_test->addTestCase(new TestOfTestController());
$controller_test->addTestCase(new TestOfTestAuthController());
$controller_test->run( new TextReporter());
