<?php
chdir("..");
require_once 'init.php';

$controller = new RSSController();
echo $controller->go();
