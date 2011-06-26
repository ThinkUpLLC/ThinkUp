<?php
/**
 *
 * ThinkUp/tests/all_plugin_tests.php
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
$plugin_tests->add(new TestOfURLProcessor());
$plugin_tests->add(new TestOfRetweetDetector());
$plugin_tests->add(new TestOfHelloThinkUpPluginConfigurationController());
$plugin_tests->add(new TestOfSmartyModifierLinkUsernames());
$plugin_tests->add(new TestOfTwitterJSONStreamParser());
$plugin_tests->add(new TestOfTwitterRealtimePluginConfigurationController());
$plugin_tests->add(new TestOfStreamMessageQueueMySQL());
$plugin_tests->add(new TestOfConsumerUserStream());
$plugin_tests->add(new TestOfConsumerStreamProcess());
$plugin_tests->add(new TestOfStreamMessageQueueFactory());
$version = explode('.', PHP_VERSION); //dont run redis test for php less than 5.3
if ($version[0] >= 5 && $version[1] >= 3) { //only run Redis tests if PHP 5.3
    $plugin_tests->add(new TestOfStreamMessageQueueRedis());
}

$tr = new TextReporter();
$plugin_tests->run( $tr );
if (isset($RUNNING_ALL_TESTS) && $RUNNING_ALL_TESTS) {
    if (isset($TOTAL_PASSES) && isset($TOTAL_FAILURES)) {
        $TOTAL_PASSES = $TOTAL_PASSES + $tr->getPassCount();
        $TOTAL_FAILURES = $TOTAL_FAILURES + $tr->getFailCount();
    }
}
