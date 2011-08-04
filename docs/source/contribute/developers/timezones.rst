How ThinkUp Handles Timezones
=============================

During :doc:`installation </install/install>`, ThinkUp prompts the user to set their local timezone. The timezone
value must be a `valid PHP timezone <http://php.net/manual/en/timezones.php>`_.

ThinkUp stores this timezone in the ``config.inc.php`` file in this line:

::

    $THINKUP_CFG['timezone']                  = 'America/Los_Angeles';

As of beta 14, on every connection to the database, ThinkUp explicitly sets the database timezone to that value. Any
date/times information for a given table should be stored in UTC as a 
`MySQL DATETIME field <http://dev.mysql.com/doc/refman/5.1/en/datetime.html>`_. When MySQL retrieves a date/time field
value, it automatically converts it to the local time in that timezone
