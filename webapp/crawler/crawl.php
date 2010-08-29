<?php
chdir("..");
require_once 'init.php';

$controller = new CrawlerAuthController($argc, $argv);
echo $controller->go();
