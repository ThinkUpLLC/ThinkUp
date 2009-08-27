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

		 
		 /******* Tweet Formatting ********/
		 
		 .individual-tweet {
		 	padding : 10px;
		 	margin-top : 10px;
		 }
		 
		 .reply {
		 	padding-left : 85px;
		 }
		 
		 .private {
		 	border : 1px dotted #666;
		 	background-color : #eee;
		 }
		 
		 .person-info {
		 	float: left;
		 	margin-right: 10px;
		 	width : 80px;
		 	text-align : center;
		 }
		 
		 li.individual-tweet h3 a {
		     font-size : x-small;
		     color : #666;
		 }
		 
		 li.individual-tweet h4, li.individual-tweet form {
		     font-size : xx-small;
		     visibility:hidden;
		 }

		 li.individual-tweet:hover h4, li.individual-tweet:hover form {
		     visibility:visible;
		     color : #666;
		 }
		 
		 
		 li.individual-tweet:hover h3 a {
			color: #0060e0;
		 }		 
		 
		li.individual-tweet h3 a.most-popular {
		 	font-size : medium;
		 	font-weight : strong;
		 }

		 .avatar { 	
		 	border: solid 1px #ccc;
		 }

		 /******* /Tweet Formatting ********/		
		
		
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
	<ul>
		{include file="_status.public.tpl" t=$t}
	</ul>
	{/foreach}	

</div>
<h2>Replies to a Single Tweet</h2>
<p>Archived and Curated with <a href="http://smarterware.org/2877/twitalytic-alpha-preview-archiving-curating-and-threading-tweets">Twitalytic</a></p>

<h1><a href="{$site_root}public.php">&larr; Back to the Public Timeline</a></h1>
{/if}


{if $tweets}
	{foreach from=$tweets key=tid item=t}
	<ul>
		{include file="_status.public.tpl" t=$t}
	</ul>
	{/foreach}

</div>

<p>Tweets and Replies Archived and Curated with <a href="http://smarterware.org/2877/twitalytic-alpha-preview-archiving-curating-and-threading-tweets">Twitalytic</a></p>

{/if}


<p>[<a href="{$cfg->site_root_path}session/login.php">Sign in</a>]</p>
<script type="text/javascript" src="{$cfg->site_root_path}cssjs/linkify.js"></script>

	</center>
</body>
</html>