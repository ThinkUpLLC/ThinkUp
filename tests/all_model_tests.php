<?php
/**
 *
 * ThinkUp/tests/all_model_tests.php
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
include dirname(__FILE__) . '/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/mock_objects.php';

/* MODEL TESTS */
$model_tests = new TestSuite('Model tests');
$model_tests->add(new TestOfLogger());
$model_tests->add(new TestOfPDOCorePluginDAO());
$model_tests->add(new TestOfDAOFactory());
$model_tests->add(new TestOfConfig());
$model_tests->add(new TestOfFileDataManager());
$model_tests->add(new TestOfCrawler());
$model_tests->add(new TestOfFollowMySQLDAO());
$model_tests->add(new TestOfCountHistoryMySQLDAO());
$model_tests->add(new TestOfGroupMySQLDAO());
$model_tests->add(new TestOfGroupMemberMySQLDAO());
$model_tests->add(new TestOfInsightBaselineMySQLDAO());
$model_tests->add(new TestOfInsightMySQLDAO());
$model_tests->add(new TestOfInsightTerms());
$model_tests->add(new TestOfInstanceMySQLDAO());
$model_tests->add(new TestOfDashboardModuleCacher());
$model_tests->add(new TestOfInsight());
$model_tests->add(new TestOfInstaller());
$model_tests->add(new TestOfInstallerMySQLDAO());
$model_tests->add(new TestOfInviteMySQLDAO());
$model_tests->add(new TestOfJSONDecoder());
$model_tests->add(new TestOfLinkMySQLDAO());
$model_tests->add(new TestOfLoader());
$model_tests->add(new TestOfLocationMySQLDAO());
$model_tests->add(new TestOfMailer());
$model_tests->add(new TestOfOptionMySQLDAO());
$model_tests->add(new TestOfOwner());
$model_tests->add(new TestOfOwnerMySQLDAO());
$model_tests->add(new TestOfOwnerInstanceMySQLDAO());
$model_tests->add(new TestOfReporter());
$model_tests->add(new TestOfPlugin());
$model_tests->add(new TestOfPluginMySQLDAO());
$model_tests->add(new TestOfPluginOptionMySQLDAO());
$model_tests->add(new TestOfPluginRegistrar());
$model_tests->add(new TestOfPost());
$model_tests->add(new TestOfPostMySQLDAO());
$model_tests->add(new TestOfExportMySQLDAO());
$model_tests->add(new TestOfPostErrorMySQLDAO());
$model_tests->add(new TestOfProfiler());
$model_tests->add(new TestOfSession());
$model_tests->add(new TestOfSessionMySQLDAO());
$model_tests->add(new TestOfSessionCache());
$model_tests->add(new TestOfViewManager());
$model_tests->add(new TestOfUserMySQLDAO());
$model_tests->add(new TestOfUserErrorMySQLDAO());
$model_tests->add(new TestOfUtils());
$model_tests->add(new TestOfWebapp());
$model_tests->add(new TestOfMenuItem());
$model_tests->add(new TestOfDataset());
$model_tests->add(new TestOfPostIterator());
$model_tests->add(new TestOfMutexMySQLDAO());
$model_tests->add(new TestOfBackupMySQLDAO());
$model_tests->add(new TestOfFavoritePostMySQLDAO());
$model_tests->add(new TestOfStreamDataMySQLDAO());
$model_tests->add(new TestOfStreamProcMySQLDAO());
$model_tests->add(new TestOfHashtagMySQLDAO());
$model_tests->add(new TestOfMentionMySQLDAO());
$model_tests->add(new TestOfPlaceMySQLDAO());
$model_tests->add(new TestOfPDODAO());
$model_tests->add(new TestOfURLProcessor());
$model_tests->add(new TestOfTableStatsMySQLDAO());
$model_tests->add(new TestOfShortLinkMySQLDAO());
$model_tests->add(new TestOfHashtagPostMySQLDAO());
$model_tests->add(new TestOfInstanceHashtagMySQLDAO());
$model_tests->add(new TestOfVideoMySQLDAO());
$model_tests->add(new TestOfPhotoMySQLDAO());
$model_tests->add(new TestOfCookieMySQLDAO());
$model_tests->add(new TestOfUserVersionsMySQLDAO());

$tr = new TextReporter();
list($usec, $sec) = explode(" ", microtime());
$start =  ((float)$usec + (float)$sec);
$model_tests->run( $tr );

if (getenv("TEST_TIMING")=="1") {
    list($usec, $sec) = explode(" ", microtime());
    $finish =  ((float)$usec + (float)$sec);
    $runtime = round($finish - $start);
    printf("Tests completed run in $runtime seconds\n");
}
if (isset($RUNNING_ALL_TESTS) && $RUNNING_ALL_TESTS) {
    $TOTAL_PASSES = $TOTAL_PASSES + $tr->getPassCount();
    $TOTAL_FAILURES = $TOTAL_FAILURES + $tr->getFailCount();
}
