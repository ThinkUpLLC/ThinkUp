<?php
chdir(dirname(dirname(dirname(__FILE__))));
require_once 'webapp/install/version.php';

echo $THINKUP_VERSION;