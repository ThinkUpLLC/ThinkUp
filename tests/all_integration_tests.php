<?php
/**
 *
 * ThinkUp/tests/all_integration_tests.php
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

/* INTEGRATION TESTS */
$web_tests = new TestSuite('Integration tests');
$web_tests->add(new WebTestOfApplicationSettings());
$web_tests->add(new WebTestOfCaptchaImage());
$web_tests->add(new WebTestOfChangePassword());
$web_tests->add(new WebTestOfCrawlerRun());
$web_tests->add(new WebTestOfCSRFToken());
$web_tests->add(new WebTestOfDeleteInstance());
$web_tests->add(new WebTestOfLogin());
$web_tests->add(new WebTestOfLogout());
$web_tests->add(new WebTestOfRegistration());

$tr = new TextReporter();
$start =  ((float)$usec + (float)$sec);
$web_tests->run( $tr );

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
