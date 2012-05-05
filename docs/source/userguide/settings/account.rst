Account
=======

In the account area of ThinkUp's settings, you can change your ThinkUp account password, automate ThinkUp crawls,
and reset your ThinkUp API key.

Password
--------

To change your ThinkUp user password, enter your current password, and your new password (once more to confirm). Then
click on the "Change password" button.

Remember, your ThinkUp password must be at least 5 characters long.

Automate ThinkUp Crawls
-----------------------

Instead of manually clicking the "Capture Data" link in ThinkUp's status bar, you can set up ThinkUp to automatically
update its data. You can do so in one of two ways: using a special secret RSS feed subscription in your favorite 
newsreader, or by scheduling a cron job to run on your web server.

You can get your RSS feed URL and the cron job command here. 

Find out more about :doc:`how to capture data in ThinkUp </userguide/datacapture>`.


Reset Your API Key
------------------

External applications use your ThinkUp API key for authentication via a special, secret URL which contains the key. 

For example, RSS news readers can update your ThinkUp data using a special URL which contains this key. If the URL
does not contain the right key, ThinkUp will not update.

If you've accidentally published or shared a URL which contains your ThinkUp API key, change it and update any places
you've used the URL. For example, once you've reset your API key, you'll have to update your RSS feed subscription 
URL to the new key.

To change your API key, click on the "Reset Your API Key" button. Then, visit the 
:doc:`Capture Data page </userguide/datacapture>` to copy and paste the updated feed URL into your newsreader.

