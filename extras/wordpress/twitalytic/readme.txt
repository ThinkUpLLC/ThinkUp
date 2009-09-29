=== Plugin Name ===
Contributors: ginatrapani
Tags: twitalytic, twitter
Requires at least: 2.8.4
Tested up to: 2.8.4
Stable tag: 0.2

Displays Twitter data via Twitalytic in a post or page via a shortcode.

== Description ==

Displays Twitter data pulled from the [Twitalytic](http://twitalytic.com) on your WordPress blog. Currently it can list:

* A chronological view of tweets for a given user without replies, start to finish
* All replies assigned or associated to a given tweet
* The reply count for a given tweet

== Installation ==

1. Upload `twitalytic` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place the right shortcode in a post or page. For example, `[twitalytic_chronological_archive twitter_username='ginatrapani']` lists
all tweets for ginatrapani without replies. `[twitalytic_status_reply_count status_id="12345"]` outputs the number of replies for tweet ID 12345.
`[twitalytic_status_replies status_id="12345"]` lists all replies for status id 12345.

== Changelog ==

= 0.2 =
* Added reply count and reply listing. Added wpd->prep statement to protect against SQL injection attacks. Switched from echo'ing output
to returning it.

= 0.1 =
* Release.

== Things to know ==

* You must have [Twitalytic](http://twitalytic.com) installed on the same server as WordPress for this to work.
* Your Twitalytic tables must exist in the WordPress database, and the prefix must be "ta_".

