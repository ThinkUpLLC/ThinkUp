<?php
/**
 *
 * ThinkUp/tests/all_model_tests.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * @copyright 2009-2010 Gina Trapani
 */
require_once 'init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/mock_objects.php';

/* MODEL TESTS */
$model_tests = & new GroupTest('Model tests');
$model_tests->addTestCase(new TestOfLogger());
$model_tests->addTestCase(new TestOfPDODAO());
$model_tests->addTestCase(new TestOfDAOFactory());
$model_tests->addTestCase(new TestOfConfig());
$model_tests->addTestCase(new TestOfCrawler());
$model_tests->addTestCase(new TestOfFollowMySQLDAO());
$model_tests->addTestCase(new TestOfFollowerCountMySQLDAO());
$model_tests->addTestCase(new TestOfInstanceMySQLDAO());
$model_tests->addTestCase(new TestOfInstaller());
$model_tests->addTestCase(new TestOfInstallerMySQLDAO());
$model_tests->addTestCase(new TestOfLinkMySQLDAO());
$model_tests->addTestCase(new TestOfLoader());
$model_tests->addTestCase(new TestOfLocationMySQLDAO());
$model_tests->addTestCase(new TestOfOptionMySQLDAO());
$model_tests->addTestCase(new TestOfOwnerMySQLDAO());
$model_tests->addTestCase(new TestOfOwnerInstanceMySQLDAO());
$model_tests->addTestCase(new TestOfPluginMySQLDAO());
$model_tests->addTestCase(new TestOfPluginOptionMySQLDAO());
$model_tests->addTestCase(new TestOfPluginHook());
$model_tests->addTestCase(new TestOfPost());
$model_tests->addTestCase(new TestOfPostMySQLDAO());
$model_tests->addTestCase(new TestOfPostErrorMySQLDAO());
$model_tests->addTestCase(new TestOfProfiler());
$model_tests->addTestCase(new TestOfSession());
$model_tests->addTestCase(new TestOfSmartyThinkUp());
$model_tests->addTestCase(new TestOfUserMySQLDAO());
$model_tests->addTestCase(new TestOfUserErrorMySQLDAO());
$model_tests->addTestCase(new TestOfUtils());
$model_tests->addTestCase(new TestOfWebapp());
$model_tests->addTestCase(new TestOfMenuItem());
$model_tests->addTestCase(new TestOfDataset());
$model_tests->addTestCase(new TestOfPostIterator());
$model_tests->addTestCase(new TestOfMutexMySQLDAO());
$model_tests->addTestCase(new TestOfBackupMySQLDAO());
$model_tests->run( new TextReporter());
