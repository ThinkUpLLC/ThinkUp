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
include 'init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/mock_objects.php';

/* CONTROLLER TESTS */
$controller_test = new TestSuite('Controller tests');
$controller_test->add(new TestOfAccountConfigurationController());
$controller_test->add(new TestOfActivateAccountController());
$controller_test->add(new TestOfAppConfigController());
$controller_test->add(new TestOfBackupController());
$controller_test->add(new TestOfCheckCrawlerController());
$controller_test->add(new TestOfCrawlerAuthController());
$controller_test->add(new TestOfDashboardController());
$controller_test->add(new TestOfThinkUpEmbedController());
$controller_test->add(new TestOfThreadJSController());
$controller_test->add(new TestOfExportController());
$controller_test->add(new TestOfExportServiceUserDataController());
$controller_test->add(new TestOfForgotPasswordController());
$controller_test->add(new TestOfGridController());
$controller_test->add(new TestOfGridExportController());
$controller_test->add(new TestOfInstallerController());
$controller_test->add(new TestOfLoginController());
$controller_test->add(new TestOfLogoutController());
$controller_test->add(new TestOfPasswordResetController());
$controller_test->add(new TestOfPostController());
$controller_test->add(new TestOfRegisterController());
$controller_test->add(new TestOfTestController());
$controller_test->add(new TestOfTestAuthController());
$controller_test->add(new TestOfTestAdminController());
$controller_test->add(new TestOfToggleActiveInstanceController());
$controller_test->add(new TestOfToggleActiveOwnerController());
$controller_test->add(new TestOfToggleActivePluginController());
$controller_test->add(new TestOfTogglePublicInstanceController());
$controller_test->add(new TestOfUserController());
$controller_test->add(new TestOfPluginOptionController());
$controller_test->add(new TestOfTestAuthController());
$controller_test->add(new TestOfTestAuthAPIController());
$controller_test->add(new TestOfRSSController());
$controller_test->add(new TestOfUpgradeController());
$controller_test->add(new TestOfPostAPIController());
$controller_test->add(new TestOfStreamerAuthController());
$controller_test->add(new TestOfUpdateNowController());

$tr = new TextReporter();
$controller_test->run( $tr );
if (isset($RUNNING_ALL_TESTS) && $RUNNING_ALL_TESTS) {
    $TOTAL_PASSES = $TOTAL_PASSES + $tr->getPassCount();
    $TOTAL_FAILURES = $TOTAL_FAILURES + $tr->getFailCount();
}
