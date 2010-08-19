<?php
chdir('..');
require_once 'init.php';

$controller = new CaptchaImageController();
return $controller->go();
