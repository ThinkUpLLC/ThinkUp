<?php
require_once 'init.php';

$controller = new PublicTimelineController();
echo $controller->go();
