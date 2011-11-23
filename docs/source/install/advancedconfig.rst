Advanced Configuration
======================

ThinkUp's configuration file, ``config.inc.php``, includes advanced settings for administrators to customize
their installation.

app_title_prefix
----------------

ThinkUp prepends this value to all instances of the application title. For example, to name your installation
``Jane Smith's ThinkUp``, set this value to ``"Jane Smith's "``. When you do, all of the page titles
and email notification copy will refer to your installation as Jane Smith's ThinkUp.

The default value is '', or no prefix at all.

datadir_path
------------

This is the path to ThinkUp's writable file cache directory. By default, this folder is located in ThinkUp's root. To
customize the location of the folder where ThinkUp writes its cache and data backup files, set it here.

The default value is ``$THINKUP_CFG['source_root_path'] . 'data/'``.