<?php
/**
 *
 * ThinkUp/tests/all_controller_tests.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * @copyright 2009-2013 Gina Trapani
 */
include 'init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/mock_objects.php';

/* CONTROLLER TESTS */
$controller_test_list = array(
"TestOfAccountConfigurationController",
"TestOfActivateAccountController",
"TestOfAppConfigController",
"TestOfBackupController",
"TestOfCheckCrawlerController",
"TestOfCheckVersionController",
"TestOfCrawlerAuthController",
"TestOfInsightStreamController",
"TestOfInsightAPIController",
"TestOfThinkUpEmbedController",
"TestOfThreadJSController",
"TestOfExportController",
"TestOfExportServiceUserDataController",
"TestOfForgotPasswordController",
"TestOfGridExportController",
"TestOfInstallerController",
"TestOfLoginController",
"TestOfLogoutController",
"TestOfPasswordResetController",
"TestOfRegisterController",
"TestOfTestController",
"TestOfTestAuthController",
"TestOfTestAdminController",
"TestOfToggleActiveInstanceController",
"TestOfToggleActiveOwnerController",
"TestOfToggleOwnerAdminController",
"TestOfTogglePublicInstanceController",
"TestOfPluginOptionController",
"TestOfTestAuthAPIController",
"TestOfRSSController",
"TestOfUpgradeDatabaseController",
"TestOfPostAPIController",
"TestOfSessionAPILoginController",
"TestOfSearchController",
"TestOfStreamerAuthController",
"TestOfUpdateNowController",
"TestOfUpgradeApplicationController"
);

if (!getenv("TEST_TIMING")=="1") {
    $controller_test = new TestSuite('Controller tests');
}

foreach ($controller_test_list as $test_name) {
    include THINKUP_ROOT_PATH.'tests/'.$test_name.'.php';
    if (getenv("TEST_TIMING")=="1") {
        $controller_test = new TestSuite($test_name);
    }
    $controller_test->add(new $test_name());
    if (getenv("TEST_TIMING")=="1") {
        $tr = new TextReporter();
        list($usec, $sec) = explode(" ", microtime());
        $start =  ((float)$usec + (float)$sec);
        $controller_test->run( $tr );

        list($usec, $sec) = explode(" ", microtime());
        $finish =  ((float)$usec + (float)$sec);
        $runtime = round($finish - $start);
        printf($runtime ." seconds\n");
    }
}

if (!getenv("TEST_TIMING")=="1") {
    $tr = new TextReporter();
    list($usec, $sec) = explode(" ", microtime());
    $start =  ((float)$usec + (float)$sec);
    $controller_test->run( $tr );
}

if (isset($RUNNING_ALL_TESTS) && $RUNNING_ALL_TESTS) {
    $TOTAL_PASSES = $TOTAL_PASSES + $tr->getPassCount();
    $TOTAL_FAILURES = $TOTAL_FAILURES + $tr->getFailCount();
}