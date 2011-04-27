ThinkUp is currently in the process of upgrading
================================================

When you visit a ThinkUp installation and you see the message, "ThinkUp
is currently in the process of upgrading. Please try back again in a
little while." that means that ThinkUp administrators need to complete
the upgrade process.

If you are that installation's administrator: check your email.
---------------------------------------------------------------

A message with a link to complete the upgrade process should be in your
inbox.

If your ThinkUp server's email function doesn't work, do the following:

Open the \_lib/view/compiled\_view/upgrade\_token on your web server and
copy the upgrade token to your clipboard.

Visit the following URL, but substitute http://yourserver.com/thinkup/
with your installations URL and path, and XXXXXX with the token you just
copied:

http://yourserver.com/thinkup/install/upgrade.php?upgrade\_token=XXXXXX
