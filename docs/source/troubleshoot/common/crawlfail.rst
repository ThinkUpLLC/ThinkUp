Something's going wrong during crawls, but the log on the updatenow.php page doesn't give enough information
============================================================================================================

To closely troubleshoot crawler activity, enable the crawler's verbose developer log,
which provides detailed information like memory usage, class and method names, and line numbers.

You can do that by logging in as an administrator. In "Settings" go to "Application" and check the "Developer log"
checkbox.

If you know that the problem is confined to a specific crawler, skip all the other service users crawls by deactivating
all the plugins except the one you're having trouble with.

Then, click on the "Update Now" link. The log output will contain more detailed debugging information.
