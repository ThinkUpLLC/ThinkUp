<?php
require_once 'init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/mock_objects.php';

/* CONTROLLER TESTS */
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
