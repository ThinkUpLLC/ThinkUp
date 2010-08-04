<?php
require_once 'init.tests.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkUpAuthController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.PluginConfigurationController.php';

/* CONTROLLER TESTS */
require_once $SOURCE_ROOT_PATH.'tests/TestOfAccountConfigurationController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfActivateAccountController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfCheckCrawlerController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfExportController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfForgotPasswordController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfInlineViewController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfLoginController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfLogoutController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPasswordResetController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfMapController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPublicTimelineController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPrivateDashboardController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPostController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfRegisterController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfTestController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfTestAuthController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfTestAdminController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfToggleActiveInstanceController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfToggleActivePluginController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfTogglePublicInstanceController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfUserController.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPluginOptionController.php';


$controller_test = & new GroupTest('Controller tests');
$controller_test->addTestCase(new TestOfAccountConfigurationController());
$controller_test->addTestCase(new TestOfActivateAccountController());
$controller_test->addTestCase(new TestOfCheckCrawlerController());
$controller_test->addTestCase(new TestOfExportController());
$controller_test->addTestCase(new TestOfForgotPasswordController());
$controller_test->addTestCase(new TestOfInlineViewController());
$controller_test->addTestCase(new TestOfLoginController());
$controller_test->addTestCase(new TestOfLogoutController());
$controller_test->addTestCase(new TestOfPasswordResetController());
$controller_test->addTestCase(new TestOfMapController());
$controller_test->addTestCase(new TestOfPublicTimelineController());
$controller_test->addTestCase(new TestOfPrivateDashboardController());
$controller_test->addTestCase(new TestOfPostController());
$controller_test->addTestCase(new TestOfRegisterController());
$controller_test->addTestCase(new TestOfTestController());
$controller_test->addTestCase(new TestOfTestAuthController());
$controller_test->addTestCase(new TestOfTestAdminController());
$controller_test->addTestCase(new TestOfToggleActiveInstanceController());
$controller_test->addTestCase(new TestOfToggleActivePluginController());
$controller_test->addTestCase(new TestOfTogglePublicInstanceController());
$controller_test->addTestCase(new TestOfUserController());
$controller_test->addTestCase(new TestOfPluginOptionController());
$controller_test->run( new TextReporter());
