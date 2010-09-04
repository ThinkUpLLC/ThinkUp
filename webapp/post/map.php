<?php
chdir('..');
require_once 'init.php';

$controller = new MapController();
echo $controller->go();