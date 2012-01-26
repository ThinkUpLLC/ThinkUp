Understanding ThinkUp's Folder Permissions
==========================================

In order for ThinkUp to run, it must be able to write caches, template compilations, and sometimes plugin data
to a specific data directory defined in $THINKUP_CFG['datadir_path'].  By default this is ``/your_path_to_thinkup/webapp/data``.

The recommended and most secure way to grant ThinkUp write access to these folders is to change the owner of this
folder to the web server user. The command for doing this is:

``chown -R apache your_datadir_path``

Where your_datadir_path is your installation path and apache is the name of the web server user.
(Note that this username could vary depending on your server.)

If you are unable to change owner (chown) the folder, a less secure but just as effective method is to make the folder
writable by the world. To do that, you can run this command:

``chmod -R 777 your_datadir_path``

If possible, change the folder's owner to the web server user or group instead of setting its permissions to world-writable.
