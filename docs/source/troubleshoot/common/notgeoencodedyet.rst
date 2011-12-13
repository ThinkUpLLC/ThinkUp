The GeoEncoder plugin is activated and set up, but there are no post maps
==========================================================================

The :doc:`GeoEncoder plugin </userguide/settings/plugins/geoencoder>` uses the Google Maps API to store latitude and
longitude points for each post and user. There are three possible reasons why you're not seeing mapped posts:

* The GeoEncoder plugin isn't set up correctly.
* The GeoEncoder exceeded its allocation of API calls for the time it's been running and needs more time to catch up.
* Post replies don't have locations associated with them.

Here's how to troubleshoot the GeoEncoder plugin.

Check the crawler log and database table
----------------------------------------

If the GeoEncoder plugin is activated , click the "Update Now" button in ThinkUp and look for the GeoEncoder's entries
in the crawl progress log. These lines will tell you how many locations the plugin has geoencoded and their status.

To see what the GeoEncoder plugin has completed, :doc:`go into your ThinkUp database
</troubleshoot/common/advanced/directdb>` and take a look at the encoded_locations table. If there's data in that
table, the plugin is working.

If there is data in that table but you're not seeing maps in ThinkUp, likely it's because there just aren't locations
associated with your original post and/or the replies. ThinkUp uses the individual post location, and if that's not
set, then uses the user's location as specified in their profile as a second resort. That doesn't always work, though.

On Twitter, the user profile location field is just an open text field, and often people put data in there like
"the planet Earth" or "your computer screen" and the GeoEncoder plugin obviously can't find latitude and longitude
points for those values.

Exceeded API call allocation
----------------------------

The Google Maps API imposes `a daily API request limit of 2,500 requests
<http://code.google.com/apis/maps/documentation/geocoding/#Limits>`_. If you enable the plugin when you have more than
2,500 posts and users in your ThinkUp database, it may take several weeks to back-encode your ThinkUp data. If your
ThinkUp crawler has run today, but you still get this error message, chances are the GeoEncoder plugin has used up its
allocation of Google Maps API requests for the day. Check back in 24 hours.
