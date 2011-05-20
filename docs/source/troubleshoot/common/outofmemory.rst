"Fatal error: Allowed memory size of XXXX bytes exhausted (tried to allocate 16 bytes)"
=======================================================================================

ThinkUp's crawler script can require a lot of memory due to all the data certain APIs return; at times, more memory
than PHP is allocated. If you run into this error, set ThinkUp to allow scripts to use more memory. To do so, add the
following line anywhere in your config.inc.php file:

``ini_set('memory_limit', '32M');``
