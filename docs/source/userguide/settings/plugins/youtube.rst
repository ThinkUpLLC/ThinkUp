YouTube
=======

ThinkUp's YouTube plugin collects videos, comments, likes, dislikes and view counts from YouTube for an authorized user.

The user must have their YouTube account linked to their Google+ account in order to use the plugin as the latest,
YouTube API requires an account holders Google+ ID to retrive the data ThinkUp requires.


Configure the YouTube Plugin (Admin only)
-----------------------------------------

To use the YouTube plugin, you'l need to `create a Google APIs project on google.com
<http://code.google.com/apis/console#access>`_. Click "Services" and switch the YouTube Analytics API, YouTube Data API
v3 and Google+ API to "On." Next, click on "API Access" then "Create an OAuth 2.0 client ID." Edit the settings for your
new Client ID then click "Next."

Make sure "Application Type" is set to "Web Application" and set the first line of Authorized Redirect URIs as
directed.

Plugin Settings
---------------

**Client ID** (required) is the Client ID provided when you `create a Google APIs project on Google.com
<http://code.google.com/apis/console#access>`_ for use with ThinkUp.

**Client secret** (required) is the Client secret provided when you `create a Google APIs project on Google.com
<http://code.google.com/apis/console#access>`_ for use with ThinkUp.

**Maximum Crawl Time** (optional) Optionally set the maximum amount of time ThinkUp should spend crawling your YouTube
data. Defaults to 20 minutes.

 **YouTube Developer Key** (optional) If you are an active YouTuber setting the developer key will allow ThinkUp to retrieve
 comments on your videos more quickly. Get a developer key from here: <https://code.google.com/apis/youtube/dashboard/gwt/index.html#settings>

 **Maximum Comments to Collect** (optional) Comment collection can take a long time for active YouTubers and may prevent
 the plugin from collecting data on older videos. So you can set this to limit the number of comments the plugin
 collects and increase the number of older videos the plugin collects data on.

Add a YouTube user to ThinkUp
-----------------------------

Click on the "Authorize ThinkUp on YouTube" button to add your YouTube user account to ThinkUp. This button will only
appear if the YouTube plugin is configured.
