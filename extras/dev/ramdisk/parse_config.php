<?php
require_once 'webapp/config.inc.php';

array_shift($argv);
echo $THINKUP_CFG[$argv[0]];