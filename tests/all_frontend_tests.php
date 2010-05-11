<?php
require_once "init.tests.php";
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

/* FRONTEND TESTS */
require_once $SOURCE_ROOT_PATH.'tests/frontend_test.php';

$webtest = & new GroupTest('Frontend tests');

$webtest->addTestCase(new TestOfThinkTankFrontEnd());
$webtest->run( new TextReporter());
?>
