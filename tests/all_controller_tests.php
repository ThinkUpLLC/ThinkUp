<?php
/**
 *
 * ThinkUp/tests/all_controller_tests.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 */
require_once 'init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/mock_objects.php';

/* CONTROLLER TESTS */
$controller_test = & new GroupTest('Controller tests');
$controller_test->addTestCase(new TestOfAccountConfigurationController());
$controller_test->addTestCase(new TestOfActivateAccountController());
$controller_test->addTestCase(new TestOfAppConfigController());
$controller_test->addTestCase(new TestOfBackupController());
$controller_test->addTestCase(new TestOfCheckCrawlerController());
$controller_test->addTestCase(new TestOfCrawlerAuthController());
$controller_test->addTestCase(new TestOfDashboardController());
$controller_test->addTestCase(new TestOfExportController());
$controller_test->addTestCase(new TestOfForgotPasswordController());
$controller_test->addTestCase(new TestOfGridController());
$controller_test->addTestCase(new TestOfGridExportController());
$controller_test->addTestCase(new TestOfInstallerController());
$controller_test->addTestCase(new TestOfLoginController());
$controller_test->addTestCase(new TestOfLogoutController());
$controller_test->addTestCase(new TestOfPasswordResetController());
$controller_test->addTestCase(new TestOfMarkParentController());
$controller_test->addTestCase(new TestOfPostController());
$controller_test->addTestCase(new TestOfRegisterController());
$controller_test->addTestCase(new TestOfTestController());
$controller_test->addTestCase(new TestOfTestAuthController());
$controller_test->addTestCase(new TestOfTestAdminController());
$controller_test->addTestCase(new TestOfToggleActiveInstanceController());
$controller_test->addTestCase(new TestOfToggleActiveOwnerController());
$controller_test->addTestCase(new TestOfToggleActivePluginController());
$controller_test->addTestCase(new TestOfTogglePublicInstanceController());
$controller_test->addTestCase(new TestOfUserController());
$controller_test->addTestCase(new TestOfPluginOptionController());
$controller_test->addTestCase(new TestOfTestAuthAPIController());
$controller_test->addTestCase(new TestOfRSSController());
$controller_test->addTestCase(new TestOfUpgradeController());



$tr = new TextReporter();
$controller_test->run( $tr );
if (isset($RUNNING_ALL_TESTS) && $RUNNING_ALL_TESTS) {
    $TOTAL_PASSES = $TOTAL_PASSES + $tr->getPassCount();
    $TOTAL_FAILURES = $TOTAL_FAILURES + $tr->getFailCount();
}
