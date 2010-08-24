<?php
require_once 'init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/mock_objects.php';

/* MODEL TESTS */
$model_tests = & new GroupTest('Model tests');
$model_tests->addTestCase(new TestOfLogger());
$model_tests->addTestCase(new TestOfPDODAO());
$model_tests->addTestCase(new TestOfDAOFactory());
$model_tests->addTestCase(new TestOfConfig());
$model_tests->addTestCase(new TestOfCrawler());
$model_tests->addTestCase(new TestOfFollowMySQLDAO());
$model_tests->addTestCase(new TestOfFollowerCountMySQLDAO());
$model_tests->addTestCase(new TestOfInstanceMySQLDAO());
$model_tests->addTestCase(new TestOfInstaller());
$model_tests->addTestCase(new TestOfInstallerMySQLDAO());
$model_tests->addTestCase(new TestOfLinkMySQLDAO());
$model_tests->addTestCase(new TestOfLoader());
$model_tests->addTestCase(new TestOfLocationMySQLDAO());
$model_tests->addTestCase(new TestOfOwnerMySQLDAO());
$model_tests->addTestCase(new TestOfOwnerInstanceMySQLDAO());
$model_tests->addTestCase(new TestOfPluginMySQLDAO());
$model_tests->addTestCase(new TestOfPluginOptionMySQLDAO());
$model_tests->addTestCase(new TestOfPluginHook());
$model_tests->addTestCase(new TestOfPostMySQLDAO());
$model_tests->addTestCase(new TestOfPostErrorMySQLDAO());
$model_tests->addTestCase(new TestOfProfiler());
$model_tests->addTestCase(new TestOfSession());
$model_tests->addTestCase(new TestOfSmartyThinkUp());
$model_tests->addTestCase(new TestOfUserMySQLDAO());
$model_tests->addTestCase(new TestOfUserErrorMySQLDAO());
$model_tests->addTestCase(new TestOfUtils());
$model_tests->addTestCase(new TestOfWebapp());
$model_tests->addTestCase(new TestOfWebappTab());
$model_tests->addTestCase(new TestOfWebappTabDataset());
$model_tests->addTestCase(new TestOfPostIterator());
$model_tests->run( new TextReporter());
