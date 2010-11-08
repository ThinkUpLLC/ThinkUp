GeoEncoder ThinkUp Plugin
============================

The GeoEncoder ThinkUp plugin geocodes location data available in the database to point to a neighborhood from
where a particular post has been made.

Crawler Plugin
--------------
During the crawl process, GeoEncoder scans through the database to select posts that have not yet been processed
by the plugin earlier and hence do not contain reliable geo-location information, geocodes them and stores the
updated information in the database. It then updates the geoencoding status of each post.
The limit for geoencoding posts per crawl has been set at 500 posts per crawl. This has been done to avoid hitting
the API limit of 2500 requests per day.


## Credits
MarkerClusterer Google Maps Library v1.0 (http://gmaps-utility-library.googlecode.com/svn/trunk/markerclusterer/1.0).
Plugin icon provided by [Clckr](http://www.clker.com/clipart-15787.html).
Map icon provided by [dryicons](http://dryicons.com/images/icon_sets/colorful_stickers_part_6_icons_set).