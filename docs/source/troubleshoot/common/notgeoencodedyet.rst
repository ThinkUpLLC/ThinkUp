"This post has not been geoencoded yet; cannot display map"
===========================================================

The :doc:`GeoEncoder plugin </userguide/settings/plugins/geoencoder>` uses the Google Maps API to store latitude and
longitude points for each post and user.

The Google Maps API imposes `a daily API request limit of 2,500 requests
<http://code.google.com/apis/maps/documentation/geocoding/#Limits>`_. If you enable the plugin when you have more than
2,500 posts and users in your ThinkUp database, it may take several weeks to back-encode your ThinkUp data. If your
ThinkUp crawler has run today, but you still get this error message, chances are the GeoEncoder plugin has used up its
allocation of Google Maps API requests for the day. Check back in 24 hours.
