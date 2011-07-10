Understanding ThinkUp's Folder Permissions
==========================================

In order for ThinkUp to run, it must be able to write cached and compiled files to a specific folder within its own
installation, the ``/_lib/view/compiled_view/`` folder and its subfolders.

The recommended and most secure way to grant ThinkUp write access to these folders is to change the owner of this
folder to the web server user. The command for doing this is:

``chown -R apache /your/path/to/thinkup/_lib/view/compiled_view/``

Where /your/path/to/thinkup/ is your installation path and apache is the name of the web server user.
(Note that this username could vary depending on your server.)

If you are unable to change owner (chown) the folder, a less secure but just as effective method is to make the folder
writable by the world. To do that, you can run this command:

``chmod -R 777 /your/path/to/thinkup/_lib/view/compiled_view/``

If possible, change the folder's owner to the web server user instead of setting its permissions to world-writable.

