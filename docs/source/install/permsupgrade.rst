Web-based Upgrader File Permissions
===================================

In order to use its web-based upgrader, ThinkUp must have the permssion able to write new application files.

The recommended and most secure way to enable the ThinkUp web-based upgrader is to change the owner of all of ThinkUp's
files and folders. The command to do this using sudo is:

``sudo chown -R apache your_thinkup_path``

Where your_thinkup_path is your installation's directory and apache is the name of the web server user.
(Note that this username could vary depending on your server.)

If you are unable to change owner (chown) the folder, a less secure but just as effective method is to make the folder
writable. To do that, you can run this command:

``chmod -R a+rw your_thinkup_path``

If possible, change the folder's owner to the web server user or group instead of setting its permissions to writable.