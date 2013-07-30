YouTube
=======

ThinkUp's YouTube plugin collects videos, comments, likes, dislikes and view counts from YouTube for an authorized user.


Configure the YouTube Plugin (Admin only)
-----------------------------------------

To use the YouTube plugin, you'l need to `create a Google APIs project on google.com
<http://code.google.com/apis/console#access>`_. Click "Services" and switch the YouTube Analytics API and YouTube Data API v3 to "On." Next, click on
"API Access" then "Create an OAuth 2.0 client ID." Edit the settings for your new Client ID then click "Next."
Make sure "Application Type" is set to "Web Application" and set the first line of Authorized Redirect URIs as
directed.

Plugin Settings
---------------

**Client ID** (required) is the Client ID provided when you `create a Google APIs project on Google.com
<http://code.google.com/apis/console#access>`_ for use with ThinkUp.

**Client secret** (required) is the Client secret provided when you `create a Google APIs project on Google.com
<http://code.google.com/apis/console#access>`_ for use with ThinkUp.

Add a YouTube user to ThinkUp
---------------------------

Click on the "Authorize ThinkUp on YouTube" button to add your YouTube user account to ThinkUp. This button will only
appear if the YouTube plugin is configured.
