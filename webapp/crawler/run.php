<?php
chdir("..");
require_once 'init.php';

$controller = new CrawlerWebController();
echo $controller->go();
