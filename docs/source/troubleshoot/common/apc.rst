Repeated errors: "Warning: require_once(): Unable to allocate memory for pool."
===============================================================================

ThinkUp 0.9 has a known compatibility issue with the Alternative PHP Cache (APC).  A known workaround is to disable
APC for ThinkUp by adding the following line anywhere in your config.inc.php file:

``ini_set('apc.cache_by_default',0);``


