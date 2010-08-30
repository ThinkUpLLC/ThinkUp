<?php
require_once 'init.php';

$controller = new CheckCrawlerController(true);
echo $controller->go();
