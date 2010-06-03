<?php
chdir("..");
require_once 'init.php';
require_once 'controller/class.PostController.php';

$controller = new PostController();
echo $controller->go();
