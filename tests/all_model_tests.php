<?php
require_once 'init.tests.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/mock_objects.php';

/* MODEL TESTS */
require_once $SOURCE_ROOT_PATH.'tests/TestOfConfig.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfDatabase.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfFollowMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfInstanceMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfLinkMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfLogger.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfOwnerInstanceDAO.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPluginDAO.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPluginHook.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPostMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPostErrorMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfSmartyThinkTank.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfUserMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfUtils.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfPDODAO.php';
require_once $SOURCE_ROOT_PATH.'tests/TestOfDAOFactory.php';

$modeltest = & new GroupTest('Model tests');
$modeltest->addTestCase(new TestOfLogging());
$modeltest->addTestCase(new TestOfPDODAO());
$modeltest->addTestCase(new TestOfDAOFactory());
$modeltest->addTestCase(new TestOfConfig());
$modeltest->addTestCase(new TestOfDatabase());
$modeltest->addTestCase(new TestOfFollowMySQLDAO());
$modeltest->addTestCase(new TestOfInstanceMySQLDAO());
$modeltest->addTestCase(new TestOfLinkMySQLDAO());
$modeltest->addTestCase(new TestOfMySQLDAO());
$modeltest->addTestCase(new TestOfOwnerInstanceDAO());
$modeltest->addTestCase(new TestOfPluginDAO());
$modeltest->addTestCase(new TestOfPluginHook());
$modeltest->addTestCase(new TestOfPostMySQLDAO());
$modeltest->addTestCase(new TestOfPostErrorMySQLDAO());
$modeltest->addTestCase(new TestOfSmartyThinkTank());
$modeltest->addTestCase(new TestOfUserMySQLDAO());
$modeltest->addTestCase(new TestOfUtils());
$modeltest->run( new TextReporter());
?>
