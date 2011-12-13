Failed to write session data (files)
====================================

ThinkUp requires that PHP can save session files successfully to maintain user sessions.

This error means that PHP isn't able to save session files because the directory where it's trying to do so isn't
writable or doesn't exist.

To fix this problem, set session.save_path to a writable directory in your php.ini file or contact your host asking
for help doing that. Here's more info about the session.save_path directive:

http://www.php.net/manual/en/session.configuration.php#ini.session.save-path

This problem can also manifest itself by constantly asking users to log in over and over again, or by showing this 
error text:  

Warning: session_start() [function.session-start]: open(/path/to/folder, O_RDWR) failed: No such file or directory

