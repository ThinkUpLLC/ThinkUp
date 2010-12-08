=== Plugin Name ===
Contributors: ginatrapani, samwho
Tags: thinkup, twitter
Requires at least: 2.9.1
Tested up to: 2.9.1
Stable tag: 0.1

Displays Twitter data via ThinkUp in a post or page via a shortcode.

== Description ==

Displays Twitter data pulled from the [ThinkUp](http://thinkupapp.com) on your WordPress blog. Currently it can list:

* A chronological view of tweets or Facebook status updates for a given user without replies, start to finish
* All replies assigned or associated to a given tweet or Facebook status update
* The reply count for a given tweet or Facebook post

== Installation ==

1. Upload `thinkup` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In the WordPress administration area, under Settings, click on ThinkUp. There, save your default Twitter username
and database details (if the ThinkUp database is separate from WordPress).
4. Place the right shortcode in a post or page.
For example,
`[thinkup_chronological_archive order='asc']` lists all tweets for the default username without replies.
`[thinkup_reply_count post_id="12345" network="twitter']` outputs the number of replies for post ID 12345 on
'twitter' network by the default username.
`[thinkup_post_replies post_id="12345" network="twitter']` lists all replies for status id 12345 by the default
username. Add the `username="yourtwittername"` parameter to the shortcode to use a username other than
the default.

== Changelog ==

= 0.1 =
* Release.

== Things to know ==

* You must have [ThinkUp](http://thinkupapp.com) installed on the same server as WordPress for this to work.