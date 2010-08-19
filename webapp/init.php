<?php
//Before we do anything, make sure we've got PHP 5
$version = explode('.', PHP_VERSION);
if ($version[0] < 5) {
    echo "ERROR: ThinkUp requires PHP 5. The current version of PHP is ".phpversion().".";
    die();
}

//Register our lazy class loader
require_once 'model/class.Loader.php';
Loader::register();

//Initialize config
$config = Config::getInstance();
if ($config->getValue('time_zone')) {
    putenv($config->getValue('time_zone'));
}
if ($config->getValue('debug')) {
    ini_set("display_errors", 1);
    ini_set("error_reporting", E_ALL);
}

//Init plugins
$pdao = DAOFactory::getDAO('PluginDAO');
$active_plugins = $pdao->getActivePlugins();
foreach ($active_plugins as $ap) {
    foreach (glob($config->getValue('source_root_path').'webapp/plugins/'.$ap->folder_name."/model/*.php") as
    $include_file) {
        require_once $include_file;
    }
    foreach (glob($config->getValue('source_root_path').'webapp/plugins/'.$ap->folder_name."/controller/*.php") as
    $include_file) {
        require_once $include_file;
    }
}