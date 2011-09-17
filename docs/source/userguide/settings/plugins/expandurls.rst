Expand URLs
===========

The Expand URLs plugin captures the full-length URL which shortened links point to from tweets and other posts,
including images. The plugin can store photo thumbnails for  short Flickr URLs (flic.kr). It can also capture
click counts and titles for Bit.ly (bit.ly, j.mp, and bitly.com) links. 

Plugin Settings
---------------

**Links to expand per crawl** (required) is the total number of links the plugin should process in a given crawler run.
The default value is 1500 links. The higher this number is, the longer a given crawl can take to complete. The plugin
also enforces a 5-second timeout on every shortened URL request. If it cannot get the expanded URL
information back in less than 5 seconds, it will save the error in the links table and the plugin will not try again.
The plugin has a "no unshortened link without an error left behind" policy: any links the plugin does not
expand in one run, it will attempt to expand in the next.

**Flickr API key** (optional) is the API key the plugin uses to acquire direct links to Flickr thumbnails. This key must
be set for the plugin to process Flickr image links. Here's where to `obtain a Flickr API
key <http://www.flickr.com/services/api/keys/>`_.

**Bit.ly username** (optional) is the username you use to log into Bit.ly. This and the Bit.ly API key must be set for
the plugin to capture click counts and link titles for bit.ly, bitly.com, and j.mp shortened links. Sign up for an
account at `Bit.ly <http://bit.ly>`_.

**Bit.ly API key** (optional) is your Bit.ly-provided API key. This and your Bit.ly username must be  set for the
plugin to capture click counts and link titles for bit.ly, bitly.com, and j.mp shortened links. Here's
where `to get your Bit.ly API key <http://bitly.com/a/your_api_key>`_.

Related
-------

The Twitter plugin automatically stores photo thumbnails for short links from known image sources Yfrog, Twitpic,
Twitgoo, Instagr.am, Picplz, and Lockerz.
