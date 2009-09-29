<?php 
/*
 Plugin Name: Twitalytic Integration
 Plugin URI: http://github.com/ginatrapani/twitalytic
 Description: Displays Twitalytic data on your WordPress blog.
 Version: 0.2
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


// [twitalytic_chronological_archive twitter_username="twitalytic"]
function twitalytic_chron_archive_handler($atts) {

    extract( shortcode_atts(array(
		'twitter_username'=>'twitalytic', 
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
        
    global $wpdb;
    
    $sql = $wpdb->prepare("select pub_date, tweet_html, status_id from ta_tweets where author_username='%s' and in_reply_to_user_id is null  order by pub_date asc",
		$twitter_username);
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

// [twitalytic_status_replies status_id="12345"]
function twitalytic_replies_handler($atts) {

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
    
    global $wpdb;
    
    $sql = $wpdb->prepare( "select 
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
    //echo $sql;
    
    $replies = $wpdb->get_results($sql);
    
    if ($replies) {
        $modified_title = str_replace('#twitter_username#', $twitter_username, $title);
		$modified_title = str_replace('#status_id#', $status_id, $modified_title );
        echo "{$before}";
		echo "{$modified_title}";
        foreach ($replies as $t) {
			$tweet_content = strip_starter_username($t->tweet_html);
			$tweet_content = linkUrls($tweet_content);
            $tweet_content = linkTwitterUsers($tweet_content);
            echo "{$before_tweet}{$before_user}<a href=\"http://twitter.com/{$t->author_username}/statuses/{$t->status_id}/\">{$t->author_username}</a>{$after_user}: {$before_tweet_html}{$tweet_content}{$after_tweet_html}{$after_tweet}";
        }
        echo "{$after}";
    } else {
        echo "No replies found for status {$status_id}.";
    }
}

// [twitalytic_status_reply_count status_id="12345"]
function twitalytic_reply_count_handler($atts) {

    extract( shortcode_atts(array(
		'status_id'=>0, 
		'before'=>'<a href="#permalink#">', 
		'after'=>' Twitter replies</a>', 
	), $atts));
    
    global $wpdb;
    
    $sql = $wpdb->prepare( "select 
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
    $count = $wpdb->get_var($sql);
//	$plink = 
	$before_mod = str_replace('#permalink#', get_permalink(), $before);
    echo "{$before_mod}{$count}{$after}";
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


add_shortcode('twitalytic_chronological_archive', 'twitalytic_chron_archive_handler');
add_shortcode('twitalytic_status_replies', 'twitalytic_replies_handler');
add_shortcode('twitalytic_reply_count', 'twitalytic_reply_count_handler');


?>
