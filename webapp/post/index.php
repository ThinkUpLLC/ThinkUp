<?php
chdir("..");
require_once 'init.php';

$controller = new PostController();
echo $controller->go();
