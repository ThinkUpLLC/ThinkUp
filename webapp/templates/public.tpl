<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
 <HEAD>
  <TITLE>Twitalytic Public Timeline</TITLE>
	<style type="text/css">{literal}
	
		html {
			background: #eeeeea;
			font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size:14.5px;
		}
		
		a, a:link, a:visited {
			text-decoration: none;
			color: #0060e0;
		}
		
		a:hover {
			text-decoration : underline;
			color : red;
		}
		
		a:visited {
			color : black;
			text-decoration: underline;
		}
		
		h1 {
			font-size : x-large;
		}
		
		h3 {
			font-weight : 800;
		}
		h2 {
		}
		.tweet {
			padding:10px;

		}
		.content {
			width:600px;
			background-color:white;
			border:solid 1px grey;
			text-align:left;		
		}
		.tweetmeta {
			text-align:right;
		}
		small {
			color:grey;
		}
		small a:visited {
			color:grey;
			text-decoration:underline;
		}
		</style>{/literal}


</head>

<body>
<center>

<div class="content">
{if $tweet and $replies}
	<div class="tweet">
	<h2>{$tweet.tweet_html|link_usernames_to_twitter}</h2> <div class="tweetmeta">-<a href="http://twitter.com/{$tweet.author_username}/">{$tweet.author_username}</a>, <small><a href="http://twitter.com/{$tweet.author_username}/status/{$tweet.status_id}/">{$tweet.pub_date|relative_datetime}</a></small></div>
	
	</div>
	{foreach from=$replies key=tid item=t}
	<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_status.public.tpl" t=$t}
	</div>
	{/foreach}	

</div>
<h2>Replies to a Single Tweet</h2>
<p>Archived and Curated with <a href="http://github.com/ginatrapani/twitalytic/tree/master">Twitalytic</a></p>

<h1><a href="{$site_root}public.php">&larr; Back to the Public Timeline</a></h1>
{/if}


{if $tweets}
	{foreach from=$tweets key=tid item=t}
	<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_status.public.tpl" t=$t}
	</div>
	{/foreach}

</div>

<p>Tweets and Replies Archived and Curated with <a href="http://github.com/ginatrapani/twitalytic/tree/master">Twitalytic</a></p>

{/if}


<a href="http://smarterware.org/1448/what-im-working-on-getting-and-sharing-answers-on-twitter">More about Twitalytic</a></p>
<p>Twitter data crawler, replies archiver, and statistics generator</p>
<p>A work in progress by <a href="http://ginatrapani.org">Gina Trapani</a></p>
	<script type="text/javascript" src="{$cfg->site_root_path}cssjs/linkify.js"></script>

	</center>
</body>
</html>