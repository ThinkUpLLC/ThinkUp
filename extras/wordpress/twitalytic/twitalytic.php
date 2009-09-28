<?php
/*
Plugin Name: Twitalytic Integration
Plugin URI: http://github.com/ginatrapani/twitalytic
Description: Displays Twitalytic data on your WordPress blog.
Version: 0.1
Author: Gina Trapani
Author URI: http://ginatrapani.org
*/

/*  Copyright 2009  Gina Trapani  (email : ginatrapani at gmail dot com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// [twitalytic twitter_username="twitalytic"]
function twitalytic_chron_archive_handler($atts) {

	extract(shortcode_atts(array(
		'twitter_username' => 'twitalytic',
		'title' => '<h3><a href="http://twitter.com/#twitter_username#/">@#twitter_username#</a>\'s Tweets in Chronological Order (sans replies)</h3>',
		'before' => '<br /><ul>',
		'after' => '</ul>',
		'before_tweet' => '<li>',
		'after_tweet' => '</li>',
		'before_date' => '',
		'after_date' => '',
		'before_tweet_html' => '',
		'after_tweet_html' => '',
		'date_format' => 'Y.m.d, g:ia',
		'gmt_offset' => 8,
	), $atts));
	
	global $wpdb; 
	
	$sql = "select pub_date, tweet_html, status_id from ta_tweets where author_username='{$twitter_username}' and in_reply_to_user_id is null  order by pub_date asc";
	//echo $sql;

	$tweets = $wpdb->get_results($sql);

	if ($tweets) {
		echo str_replace('#twitter_username#', $twitter_username, $title);
		echo "{$before}";
		foreach ($tweets as $t) {
			$tweet_content = linkUrls($t->tweet_html);
			$tweet_content = linkTwitterUsers($tweet_content);
			echo "{$before_tweet}{$before_tweet_html}{$tweet_content}{$after_tweet_html} {$before_date}<a href=\"http://twitter.com/{$twitter_username}/statuses/{$t->status_id}/\">".actual_time($date_format, $gmt_offset, strtotime($t->pub_date))."</a>{$after_date}{$after_tweet}";
		}
		echo "{$after}";
	} else {
		echo "No tweets found in Twitalytic for {$twitter_username}.";
	}
}

function linkTwitterUsers($text) {
		$text = preg_replace('/(^|\s)@(\w*)/i', '$1<a href="http://twitter.com/$2" class="twitter-user">@$2</a>', $text);
		return $text;
}

function linkUrls($text) {
	/**
	 * match protocol://address/path/file.extension?some=variable&another=asf%
	 * $1 is a possible space, this keeps us from linking href="[link]" etc
	 * $2 is the whole URL
	 * $3 is protocol://
	 * $4 is the URL without the protocol://
	 * $5 is the URL parameters
	 */
	$text = preg_replace("/(^|\s)(([a-zA-Z]+:\/\/)([a-z][a-z0-9_\..-]*[a-z]{2,6})([a-zA-Z0-9~\/*-?&%]*))/i", "$1<a href=\"$2\">$2</a>", $text);

	/**
	 * match www.something.domain/path/file.extension?some=variable&another=asf%
	 * $1 is a possible space, this keeps us from linking href="[link]" etc
	 * $2 is the whole URL that was matched.  The protocol is missing, so we assume http://
	 * $3 is www.
	 * $4 is the URL matched without the www.
	 * $5 is the URL parameters
	 */
	$text = preg_replace("/(^|\s)(www\.([a-z][a-z0-9_\..-]*[a-z]{2,6})([a-zA-Z0-9~\/*-?&%]*))/i", "$1<a href=\"http://$2\">$2</a>", $text);

	return $text;
}

function actual_time($format,$offset,$timestamp){
   //Offset is in hours from gmt, including a - sign if applicable.
   //So lets turn offset into seconds
   $offset = $offset*60*60;
   $timestamp = $timestamp + $offset;
    //Remember, adding a negative is still subtraction ;)
   return gmdate($format,$timestamp);
}

add_shortcode('twitalytic_chronological_archive', 'twitalytic_chron_archive_handler');


?>
