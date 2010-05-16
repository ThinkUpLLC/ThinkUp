<?php
require_once "init.tests.php";
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

/* FRONTEND TESTS */
require_once $SOURCE_ROOT_PATH.'tests/TestOfChangePassword.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPrivateDashboard.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPublicTimeline.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfSignIn.php';

$webtest = & new GroupTest('Frontend tests');

$webtest->addTestCase(new TestOfChangePassword());
$webtest->addTestCase(new TestOfPrivateDashboard());
$webtest->addTestCase(new TestOfPublicTimeline());
$webtest->addTestCase(new TestOfSignIn());
$webtest->run( new TextReporter());
?>
