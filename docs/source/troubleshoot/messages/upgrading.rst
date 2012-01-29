ThinkUp is currently in the process of upgrading
================================================

When you visit a ThinkUp installation and you see the message, "ThinkUp is currently in the process of upgrading.
Please try back again in a little while." that means that ThinkUp administrators need to complete the upgrade process.

If you are that installation's administrator: check your email.
---------------------------------------------------------------

A message with a link to complete the upgrade process should be in your inbox.

If your ThinkUp server's email function doesn't work, do the following:

Open the ``.htupgrade_token`` file in ThinkUp's data directory and copy its contents to your clipboard. By default,
the data directory is called ``data`` and is located in ThinkUp's root folder; if not it is specified in ThinkUp's
config.inc.php file as the value of $THINKUP_CFG['datadir_path'].

Then, enter the upgrade token into ThinkUp's form and click on the "Submit Token" button to continue the upgrade
process.
