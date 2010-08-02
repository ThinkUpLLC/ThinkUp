<?php
require_once 'init.php';
require_once 'controller/class.CheckCrawlerController.php';

$controller = new CheckCrawlerController();
echo $controller->go();
