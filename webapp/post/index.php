<?php
chdir("..");
require_once 'init.php';
require_once 'controller/class.PostController.php';
require_once 'controller/class.PublicTimelineController.php';

$controller = new PostController();
echo $controller->go();
