=== Plugin Name ===
Contributors: ginatrapani, samwho
Tags: thinkup, twitter
Requires at least: 2.9.1
Tested up to: 2.9.1
Stable tag: 0.7

Displays Twitter data via ThinkUp in a post or page via a shortcode.

== Description ==

Displays Twitter data pulled from the [ThinkUp](http://thinkupapp.com) on your WordPress blog. Currently it can list:

* A chronological view of tweets or Facebook page updates for a given user without replies, start to finish
* All replies assigned or associated to a given tweet or Facebook page post
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

= 0.7 =
* Refactored code to be object-oriented; Changed all shortcode "status" references to "post"; Added Help and FAQ areas
inside the WordPress interface.

= 0.6 =
* Added network specification to post ID to avoid ID clashes.

= 0.5 =
* Added field descriptions in the Options panel; Added rudimentary encryption of the database password so it's not
 stored in plain text in the database.

= 0.4 =
* Added Options panel to set database values without editing the PHP file.

= 0.3 =
* Added the preliminary capability to talk to a database other than the WordPress database; requires that you change
 DB credentials inside thinkup.php.

= 0.2 =
* Added reply count and reply listing. Added wpd->prep statement to protect against SQL injection attacks. Switched
from echo'ing output to returning it.

= 0.1 =
* Release.

== Things to know ==

* You must have [ThinkUp](http://thinkupapp.com) installed on the same server as WordPress for this to work.