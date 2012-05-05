Capture Social Data
===================

Once you've set up service users like your Twitter, Facebook, or Google+ account, you want to start capturing data
from those networks. Here's how.

Manually in Your Browser
------------------------

The simplest (and most manual) way to update your data is to click on the "Capture Data" link on the left side of
ThinkUp's status bar. This will run ThinkUp's data crawler and show you its activity as it runs right in your web
browser. Once you've begun a manual update on this page, keep your browser tab open until it's complete. Then, go
to your ThinkUp dashboard to see the data ThinkUp has collected.

Once you've determined that ThinkUp's crawler is successfully capturing your data, set up an automatic update schedule
using either RSS or cron.

Automatically via RSS
---------------------

Use your RSS newsreader to capture social media data on a regular basis. In 
:doc:`Settings > Account </userguide/settings/account>`, you'll find a secret RSS URL button. Copy and paste the feed
link into your favorite newsreader, and refresh the subscription in order to kick off a ThinkUp update.

Anyone who knows your ThinkUp RSS URL can run a data update. If you've shared the URL with someone who should not
have it, you can reset it in :doc:`Settings > Account </userguide/settings/account>`. Resetting your API key will
disable any future updates from URLs which contain the old API key.

Automatically via Cron
----------------------

Alternately, advanced users can add a command to the server's `crontab <http://en.wikipedia.org/wiki/Cron>`_ which
runs hourly (or whatever interval you prefer) to update ThinkUp data. Copy and paste the command from the 
"Automate ThinkUp Crawls" section of the :doc:`Settings > Account </userguide/settings/account>` page. Just be sure to
change ``yourpassword`` to your real password!