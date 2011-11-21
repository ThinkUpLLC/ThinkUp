Repeated errors: "Warning: require_once(): Unable to allocate memory for pool."
===============================================================================

ThinkUp has a conflict with the Alternative PHP Cache (APC) which can trigger this error. To work around this problem, 
disable APC for ThinkUp by adding the following line anywhere in your config.inc.php file:

``ini_set('apc.cache_by_default',0);``
