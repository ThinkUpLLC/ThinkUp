<?php
require_once 'init.tests.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

/* FRONTEND TESTS */
require_once $SOURCE_ROOT_PATH.'tests/TestOfChangePassword.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPrivateDashboard.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPublicTimeline.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfSignIn.php';

$web_tests = & new GroupTest('Frontend tests');

$web_tests->addTestCase(new TestOfChangePassword());
$web_tests->addTestCase(new TestOfPrivateDashboard());
$web_tests->addTestCase(new TestOfPublicTimeline());
$web_tests->addTestCase(new TestOfSignIn());
$web_tests->run( new TextReporter());
