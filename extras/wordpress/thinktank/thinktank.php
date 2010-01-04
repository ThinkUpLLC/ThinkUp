<?php 
/*
 Plugin Name: ThinkTank Integration
 Plugin URI: http://github.com/ginatrapani/thinktank
 Description: Displays ThinkTank data on your WordPress blog.
 Version: 0.3
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


// [thinktank_chronological_archive twitter_username="thinktankapp"]
function thinktank_chron_archive_handler($atts) {

    extract( shortcode_atts(array(
		'twitter_username'=>'thinktankapp', 
		'title'=>'<h3><a href="http://twitter.com/#twitter_username#/">@#twitter_username#</a>\'s Tweets in Chronological Order (sans replies)</h3>', 
		'before'=>'<br /><ul>', 
		'after'=>'</ul>', 
		'before_tweet'=>'<li>', 
		'after_tweet'=>'</li>', 
		'before_date'=>'', 
		'after_date'=>'', 
		'before_tweet_html'=>'', 
		'after_tweet_html'=>'', 
		'date_format'=>'Y.m.d, g:ia', 
		'gmt_offset'=>8, 
	), $atts));
        
	$wpdb2 = new wpdb("thinktankuser", "thinktankpassword", "thinktank", "localhost");

    $sql = $wpdb2->prepare("select pub_date, tweet_html, status_id from ta_tweets where author_username='%s' and in_reply_to_user_id is null  order by pub_date asc",
		$twitter_username);
    
    $tweets = $wpdb2->get_results($sql);
    
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
        echo "No tweets found in ThinkTank for {$twitter_username}.";
    }
}

// [thinktank_status_replies status_id="12345"]
function thinktank_replies_handler($atts) {

    extract( shortcode_atts(array(
		'status_id'=>0, 
		//TOOD: get twitter username from result set, don't require it as a shortcode parameter
		'twitter_username'=>'', 
		'title'=>'<h3>Public Twitter replies to <a href="http://twitter.com/#twitter_username#/statuses/#status_id#/">@#twitter_username#\'s tweet</a>:</h3>', 
		'before'=>'<br /><ul>', 
		'after'=>'</ul>', 
		'before_tweet'=>'<li>', 
		'after_tweet'=>'</li>', 
		'before_user'=>'<b>', 
		'after_user'=>'</b>', 
		'before_tweet_html'=>'', 
		'after_tweet_html'=>'', 
		'date_format'=>'Y.m.d, g:ia', 
		'gmt_offset'=>8, 
	), $atts));
    
	$wpdb2 = new wpdb("thinktankuser", "thinktankpassword", "thinktank", "localhost");

    
    $sql = $wpdb2->prepare( "select 
				t.*, u.*
			from 
				ta_tweets t
			inner join 
				ta_users u 
			on 
				t.author_user_id = u.user_id 
			where 
				in_reply_to_status_id = %0.0f 
				AND u.is_protected = 0	
			order by 
				follower_count desc;",
			$status_id );
    
    $replies = $wpdb2->get_results($sql);
  
    $output = '';
    if ($replies) {
        $modified_title = str_replace('#twitter_username#', $twitter_username, $title);
		$modified_title = str_replace('#status_id#', $status_id, $modified_title );
        $output .= "{$before}";
	$output .= "{$modified_title}";
        foreach ($replies as $t) {
		$tweet_content = strip_starter_username($t->tweet_html);
		$tweet_content = linkUrls($tweet_content);
		$tweet_content = linkTwitterUsers($tweet_content);
		$output .= "{$before_tweet}{$before_user}<a href=\"http://twitter.com/{$t->author_username}/statuses/{$t->status_id}/\">{$t->author_username}</a>{$after_user}: {$before_tweet_html}{$tweet_content}{$after_tweet_html}{$after_tweet}";
        }
        $output .= "{$after}";
    } else {
        $output .= "No replies found for status {$status_id}.";
    }
    return $output;
}

// [thinktank_status_reply_count status_id="12345"]
function thinktank_reply_count_handler($atts) {

    extract( shortcode_atts(array(
		'status_id'=>0, 
		'before'=>'<a href="#permalink#">', 
		'after'=>' Twitter replies</a>', 
	), $atts));
    
	$wpdb2 = new wpdb("thinktankuser", "thinktankpassword", "thinktank", "localhost");

    
    $sql = $wpdb2->prepare( "select 
				count(*)
			from 
				ta_tweets t
			inner join 
				ta_users u 
			on 
				t.author_user_id = u.user_id 
			where 
				in_reply_to_status_id=%0.0f
				AND u.is_protected = 0	
			order by 
				follower_count desc;",
			$status_id);
    //echo $sql;
    $count = $wpdb2->get_var($sql);
    $before_mod = str_replace('#permalink#', get_permalink(), $before);
    return "{$before_mod}{$count}{$after}";
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

function actual_time($format, $offset, $timestamp) {
    //Offset is in hours from gmt, including a - sign if applicable.
    //So lets turn offset into seconds
    $offset = $offset * 60 * 60;
    $timestamp = $timestamp + $offset;
    //Remember, adding a negative is still subtraction ;)
    return gmdate($format, $timestamp);
}

function strip_starter_username($text) {
	return preg_replace("/^@[a-zA-Z0-9_]+/", '', $text);
}
 

add_shortcode('thinktank_chronological_archive', 'thinktank_chron_archive_handler');
add_shortcode('thinktank_status_replies', 'thinktank_replies_handler');
add_shortcode('thinktank_reply_count', 'thinktank_reply_count_handler');


?>
