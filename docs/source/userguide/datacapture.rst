Update Your Data
================

Once you've set up service users like your Twitter or Facebook account, you want to start capturing data from those
networks. Here's how.

Manually in Your Browser
------------------------

The simplest (and most manual) way to update your data is to click on the "Update now" link on the left side of
ThinkUp's status bar. This will run ThinkUp's data crawler and show you its activity as it runs right in your web
browser. Once you've begun a manual update on this page, keep your browser tab open until it's complete. Then, go
to your ThinkUp dashboard to see the data ThinkUp has collected.

Once you've determined that ThinkUp's crawler is successfully capturing your data, set up an automatic update schedule
using either RSS or cron.

Automatically via RSS
---------------------

Use your RSS newsreader to capture social media data on a regular basis. At the bottom of the "Update now" page, you'll
see a Hint which reads "You can automate ThinkUp crawls by subscribing to this RSS feed in your favorite RSS reader."

Copy and paste the "this RSS feed" link into your favorite newsreader, and refresh the subscription in order to kick
off a ThinkUp update.

Automatically via Cron
----------------------

Alternately, advanced users can add a command to the server's `crontab <http://en.wikipedia.org/wiki/Cron>`_ which
runs hourly (or whatever interval you prefer) to update ThinkUp data. Copy and paste the command from the Hint text
at the bottom of the "Update now" page. Just be sure to change ``yourpassword`` to your real password!