<?php
chdir("..");
require_once 'model/class.Loader.php';
Loader::register();

//Define constants
if ( !defined('DS') ) {
    define('DS', DIRECTORY_SEPARATOR);
}
if ( !defined('THINKUP_ROOT_PATH') ) {
    define('THINKUP_ROOT_PATH', dirname(dirname(__FILE__)) . DS);
}

if ( !defined('THINKUP_WEBAPP_PATH') ) {
    define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp' . DS);
}

if ( !defined('THINKUP_BASE_URL') ) {
    // Define base URL, the same as $THINKUP_CFG['site_root_path']
    $current_script_path = explode('/', $_SERVER['PHP_SELF']);
    array_pop($current_script_path);
    if ( in_array($current_script_path[count($current_script_path)-1],
    array('account', 'post', 'session', 'user', 'install')) ) {
        array_pop($current_script_path);
    }
    $current_script_path = implode('/', $current_script_path) . '/';
    define('THINKUP_BASE_URL', $current_script_path);
}

$controller = new InstallerController();
echo $controller->go();