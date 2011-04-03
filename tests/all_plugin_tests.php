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
require_once 'init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/mock_objects.php';

$RUNNING_ALL_TESTS = true;

Loader::register(array(
THINKUP_ROOT_PATH . 'tests/',
THINKUP_ROOT_PATH . 'tests/classes/',
THINKUP_ROOT_PATH . 'tests/fixtures/'
));

/* PLUGIN TESTS */
require_once THINKUP_ROOT_PATH.'webapp/plugins/expandurls/tests/TestOfFlickrAPIAccessor.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/expandurls/tests/TestOfExpandURLsPluginConfigurationController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/expandurls/tests/TestOfExpandURLsPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/embedthread/tests/TestOfThinkUpEmbedController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/embedthread/tests/TestOfThreadJSController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/tests/TestOfFacebookCrawler.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/tests/TestOfFacebookPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/tests/TestOfFacebookPluginConfigurationController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfRetweetDetector.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterAPIAccessorOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterAuthController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterCrawler.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterInstanceMySQLDAO.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfTwitterPluginConfigurationController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/TestOfURLProcessor.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/geoencoder/tests/TestOfGeoEncoderPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/geoencoder/tests/TestOfGeoEncoderPluginConfigurationController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/geoencoder/tests/TestOfMapController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/hellothinkup/tests/TestOfHelloThinkUpPluginConfigurationController.php';
require_once THINKUP_ROOT_PATH.'tests/TestOfSmartyModifierLinkUsernames.php';

$plugin_tests = & new GroupTest('Plugin tests');
$plugin_tests->addTestCase(new TestOfExpandURLsPlugin());
$plugin_tests->addTestCase(new TestOfExpandURLsPluginConfigurationController());
$plugin_tests->addTestCase(new TestOfFlickrAPIAccessor());
$plugin_tests->addTestCase(new TestOfThinkUpEmbedController());
$plugin_tests->addTestCase(new TestOfThreadJSController());
$plugin_tests->addTestCase(new TestOfFacebookCrawler());
$plugin_tests->addTestCase(new TestOfFacebookPlugin());
$plugin_tests->addTestCase(new TestOfFacebookPluginConfigurationController());
$plugin_tests->addTestCase(new TestOfGeoEncoderPlugin());
$plugin_tests->addTestCase(new TestOfGeoEncoderPluginConfigurationController());
$plugin_tests->addTestCase(new TestOfMapController());
$plugin_tests->addTestCase(new TestOfTwitterAPIAccessorOAuth());
$plugin_tests->addTestCase(new TestOfTwitterAuthController());
$plugin_tests->addTestCase(new TestOfTwitterCrawler());
$plugin_tests->addTestCase(new TestOfTwitterInstanceMySQLDAO());
$plugin_tests->addTestCase(new TestOfTwitterOAuth());
$plugin_tests->addTestCase(new TestOfTwitterPlugin());
$plugin_tests->addTestCase(new TestOfTwitterPluginConfigurationController());
$plugin_tests->addTestCase(new TestOfURLProcessor());
$plugin_tests->addTestCase(new TestOfRetweetDetector());
$plugin_tests->addTestCase(new TestOfHelloThinkUpPluginConfigurationController());
$plugin_tests->addTestCase(new TestOfSmartyModiferLinkUsernames());

$tr = new TextReporter();
$plugin_tests->run( $tr );
if (isset($RUNNING_ALL_TESTS) && $RUNNING_ALL_TESTS) {
    if (isset($TOTAL_PASSES) && isset($TOTAL_FAILURES)) {
        $TOTAL_PASSES = $TOTAL_PASSES + $tr->getPassCount();
        $TOTAL_FAILURES = $TOTAL_FAILURES + $tr->getFailCount();
    }
}
