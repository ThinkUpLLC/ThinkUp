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
