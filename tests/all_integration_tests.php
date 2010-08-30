<?php
require_once 'init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/mock_objects.php';

Loader::register(array(
THINKUP_ROOT_PATH . 'tests' . DS,
THINKUP_ROOT_PATH . 'tests' . DS . 'classes' . DS,
THINKUP_ROOT_PATH . 'tests' . DS . 'fixtures' .DS
));

/* INTEGRATION TESTS */
$web_tests = & new GroupTest('Integration tests');
$web_tests->addTestCase(new TestOfChangePassword());
$web_tests->addTestCase(new TestOfPrivateDashboard());
$web_tests->addTestCase(new TestOfPublicTimeline());
$web_tests->addTestCase(new TestOfSignIn());
$web_tests->run( new TextReporter());
