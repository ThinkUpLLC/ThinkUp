<?php
require_once 'init.php';
require_once 'controller/class.InlineViewController.php';

$controller = new InlineViewController();
echo $controller->go();
