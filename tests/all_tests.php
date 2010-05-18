<?php 
require_once 'init.tests.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

$RUNNING_ALL_TESTS = true;

require_once $SOURCE_ROOT_PATH.'tests/all_model_tests.php';

require_once $SOURCE_ROOT_PATH.'tests/all_plugin_tests.php';

require_once $SOURCE_ROOT_PATH.'tests/all_frontend_tests.php';
?>
