Understanding ThinkUp's Folder Permissions
==========================================

In order to run, ThinkUp must be able to write files to a specific data directory defined in the
``config.inc.php`` file as ``$THINKUP_CFG['datadir_path']``. By default the data directory is located in the root of
the thinkup web-accessible directory and is named ``data``.

The recommended and most secure way to grant ThinkUp write access to this folder is to change the owner of this
folder to the web server user. The command for doing this is:

``chown -R apache your_datadir_path``

Where your_datadir_path is your installation's data directory path and apache is the name of the web server user.
(Note that this username could vary depending on your server.)

If you are unable to change owner (chown) the folder, a less secure but just as effective method is to make the folder
writable by the world. To do that, you can run this command:

``chmod -R 777 your_datadir_path``

If possible, change the folder's owner to the web server user or group instead of setting its permissions to
world-writable.

Change the Location of ThinkUp's Data Directory
-----------------------------------------------

For security-related or other reasons, you may not want ThinkUp's data directory to live in a web-accessible folder.
To change the location ThinkUp's data directory, open the ``config.inc.php`` in any text editor and set your
desired location in the ``$THINKUP_CFG['datadir_path']`` value. Note that ThinkUp's data directory must be writable
by the web server and MySQL user for ThinkUp to function. Grant ThinkUp those access permissions using the
instructions above.