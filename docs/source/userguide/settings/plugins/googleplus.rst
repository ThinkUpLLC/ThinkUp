Google+
=======

ThinkUp's Google+ plugin collects posts, reply counts, and +1 counts from Google+ for an authorized user.

**Note**: The Google+ API is in its early stages and its capabilities are limited. Currently, the API only allows apps
like ThinkUp to capture public posts, comment counts, and +1 counts. Limited/private posts, the content of public post
comments, and names of those who +1'ed a post are not available yet.

Configure the Google+ Plugin (Admin only)
-----------------------------------------

To use the Google+ plugin, you'l need to `create a Google APIs project on google.com 
<http://code.google.com/apis/console#access>`_. Click "Services" and switch Google+ API to "On." Next, click on
"API Access" then "Create an OAuth 2.0 client ID." Edit the settings for your new Client ID then click "Next."
Make sure "Application Type" is set to "Web Application" and set the first line of Authorized Redirect URIs as 
directed.

Plugin Settings
---------------

**Client ID** (required) is the Client ID provided when you `create a Google APIs project on Google.com 
<http://code.google.com/apis/console#access>`_ for use with ThinkUp.

**Client secret** (required) is the Client secret provided when you `create a Google APIs project on Google.com 
<http://code.google.com/apis/console#access>`_ for use with ThinkUp.

Add a Google+ user to ThinkUp
-----------------------------

Click on the "Authorize ThinkUp on Google+" button to add your Google+ user account to ThinkUp. This button will only
appear if the Google+ plugin is configured.

Google+ Pages
-----------------------------

APIs for Google+ Pages are not yet open for public usage and hence not supported on ThinkUp. They are 
`granted to partners on a company by company basis <https://developers.google.com/+/api/pages-signup>`_.
