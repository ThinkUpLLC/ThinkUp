Error starting crawler; another crawl is already in progress
============================================================

ThinkUp's crawler won't start if a previous crawl process is still running. If you run into this error, make sure you
wait for the first crawl to complete and try again. 

If you constantly get this error always and ThinkUp's data isn't
updating, something is wrong. There are a few things you can try:

1. Run `ps -ax | grep crawl` on your server and manually kill any crawler process you see there. Then, delete the
crawl.pid file from the crawler/logs/ directory.
2. Restart your MySQL server to clear away any MUTEX locks which are being held.
3. More troubleshooting on `the mailing list 
<http://groups.google.com/group/thinkupapp/browse_thread/thread/cb5c3c8b9a98bef6/04c2f1e6ee24f59f>`_.
