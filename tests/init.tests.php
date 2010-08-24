<?php
require 'config.tests.inc.php';

//set up 3 required constants
if ( !defined('DS') ) {
    define('DS', DIRECTORY_SEPARATOR);
}
if ( !defined('THINKUP_ROOT_PATH') ) {
    define('THINKUP_ROOT_PATH', dirname(dirname(__FILE__)) . DS);
}

if ( !defined('THINKUP_WEBAPP_PATH') ) {
    define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp' . DS);
}

if ( !defined('TESTS_RUNNING') ) {
    define('TESTS_RUNNING', true);
}

//Register our lazy class loader
require_once THINKUP_ROOT_PATH.'webapp/model/class.Loader.php';

Loader::register(array(
THINKUP_ROOT_PATH . 'tests' . DS,
THINKUP_ROOT_PATH . 'tests' . DS . 'classes' . DS,
THINKUP_ROOT_PATH . 'tests' . DS . 'fixtures' .DS
));
