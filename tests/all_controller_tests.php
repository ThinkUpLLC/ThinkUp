<?php
/**
 *
 * ThinkUp/tests/all_controller_tests.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Mark Wilkie, Dwi Widiastuti, Guillaume Boudreau, Michael Louis Thaler, ekansh
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Guillaume Boudreau <gboudreau[at]pommepause[dot]com>
 * @author Michael Louis Thaler <michael[dot]louis[dot]thaler[at]gmail[dot]com>
 * @author ekansh <ekanshpreet[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie, Dwi Widiastuti, Guillaume Boudreau, Michael Louis Thaler, ekansh
*/
require_once 'init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/mock_objects.php';

/* CONTROLLER TESTS */
$controller_test = & new GroupTest('Controller tests');
$controller_test->addTestCase(new TestOfAccountConfigurationController());
$controller_test->addTestCase(new TestOfActivateAccountController());
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
$controller_test->addTestCase(new TestOfMapController());
$controller_test->addTestCase(new TestOfMarkParentController());
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
$controller_test->addTestCase(new TestOfTestAuthAPIController());
$controller_test->addTestCase(new TestOfRSSController());
$controller_test->run( new TextReporter());
