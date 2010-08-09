<?php
chdir('..');
require_once 'init.php';
require_once 'controller/class.CaptchaImageController.php';

$controller = new CaptchaImageController();
return $controller->go();
