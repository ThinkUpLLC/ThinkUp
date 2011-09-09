Expand URLs
===========

The Expand URLs plugin captures the full-length URL which shortened links point to from tweets and other posts,
including images. Expand URLs currently expands short Flickr URLs to direct Flickr photo thumbnails. 

(Related: the Twitter plugin automatically stores direct link to image thumbnails from Yfrog, Twitpic, Twitgoo, and
Picplz.)

This plugin has a 5-second timeout on every shortened URL request. If it cannot get the expanded URL information back
in less than 5 seconds, it will save the error in the links table and the plugin will not try again.

Plugin Settings
---------------

**Links to expand per crawl** (required) is the total number of links the plugin should process in a given crawler run.
The default value is 1500 links. Keep in mind that the higher this number is, the longer a given crawl can take to
complete. The plugin has a "no unshortened link without an error left behind" policy: any links the plugin does not
expand in one run, it will attempt to expand in the next.

**Flickr API key** (optional) is the API key the plugin uses to acquire direct links to Flickr thumbnails. This key must
be set for the plugin to process Flickr image links. Here's where to `obtain a Flickr API
key <http://www.flickr.com/services/api/keys/>`_.
