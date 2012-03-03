<?php
/**
 *
 * ThinkUp/tests/all_plugin_tests.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 * @copyright 2009-2012 Gina Trapani
 */
include 'init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/mock_objects.php';

$RUNNING_ALL_TESTS = true;

$plugin_tests = new TestSuite('Plugin tests');
$plugin_tests->add(new TestOfExpandURLsPlugin());
$plugin_tests->add(new TestOfExpandURLsPluginConfigurationController());
$plugin_tests->add(new TestOfFlickrAPIAccessor());
$plugin_tests->add(new TestOfFacebookCrawler());
$plugin_tests->add(new TestOfFacebookPlugin());
$plugin_tests->add(new TestOfFacebookPluginConfigurationController());
$plugin_tests->add(new TestOfGeoEncoderPlugin());
$plugin_tests->add(new TestOfGeoEncoderPluginConfigurationController());
$plugin_tests->add(new TestOfMapController());
$plugin_tests->add(new TestOfTwitterAPIAccessorOAuth());
$plugin_tests->add(new TestOfTwitterAuthController());
$plugin_tests->add(new TestOfTwitterCrawler());
$plugin_tests->add(new TestOfTwitterInstanceMySQLDAO());
$plugin_tests->add(new TestOfTwitterOAuth());
$plugin_tests->add(new TestOfTwitterPlugin());
$plugin_tests->add(new TestOfTwitterPluginConfigurationController());
$plugin_tests->add(new TestOfCrawlerTwitterAPIAccessorOAuth());
$plugin_tests->add(new TestOfRetweetDetector());
$plugin_tests->add(new TestOfHelloThinkUpPluginConfigurationController());
$plugin_tests->add(new TestOfHelloThinkUpPlugin());
$plugin_tests->add(new TestOfSmartyModifierLinkUsernames());
$plugin_tests->add(new TestOfTwitterJSONStreamParser());
$plugin_tests->add(new TestOfTwitterRealtimePluginConfigurationController());
$plugin_tests->add(new TestOfTwitterRealtimePlugin());
$plugin_tests->add(new TestOfStreamMessageQueueMySQL());
$plugin_tests->add(new TestOfConsumerUserStream());
$plugin_tests->add(new TestOfConsumerStreamProcess());
$plugin_tests->add(new TestOfStreamMessageQueueFactory());
$plugin_tests->add(new TestOfGooglePlusPlugin());
$plugin_tests->add(new TestOfGooglePlusCrawler());
$plugin_tests->add(new TestOfGooglePlusPluginConfigurationController());
$plugin_tests->add(new TestOfFoursquarePlugin());
$plugin_tests->add(new TestOfFoursquareCrawler());
$plugin_tests->add(new TestOfFoursquarePluginConfigurationController());
$version = explode('.', PHP_VERSION); //dont run redis test for php less than 5.3
if ($version[0] >= 5 && $version[1] >= 3) { //only run Redis tests if PHP 5.3
    $plugin_tests->add(new TestOfStreamMessageQueueRedis());
}

$tr = new TextReporter();
$start =  ((float)$usec + (float)$sec);
$plugin_tests->run( $tr );

if (getenv("TEST_TIMING")=="1") {
    list($usec, $sec) = explode(" ", microtime());
    $finish =  ((float)$usec + (float)$sec);
    $runtime = round($finish - $start);
    printf("Tests completed run in $runtime seconds\n");
}

if (isset($RUNNING_ALL_TESTS) && $RUNNING_ALL_TESTS) {
    if (isset($TOTAL_PASSES) && isset($TOTAL_FAILURES)) {
        $TOTAL_PASSES = $TOTAL_PASSES + $tr->getPassCount();
        $TOTAL_FAILURES = $TOTAL_FAILURES + $tr->getFailCount();
    }
}
