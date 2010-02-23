<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link rel="stylesheet" href="http://yui.yahooapis.com/2.7.0/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
	<link type="text/css" href="{$cfg->site_root_path}cssjs/jquery-ui-1.7.1.custom.css" rel="stylesheet" />
	<link rel="shortcut icon" href="{$cfg->site_root_path}favicon.ico"/>
{literal}
	<style type="text/css">
	
		html {
			background: #eeeeea;
			font: small Candara, "Helvetica Neue", Helvetica, Arial, sans-serif;
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
			//display : none;
		}
		
		h2 {
			padding-bottom : 20px;
			font-size : smaller;
		}
		
		.section h2 {
			//display : none;
		}
		
		h3 {
			font-weight : 800;
		}
		
		li {
			padding-bottom : 10px;
			line-height : 125%;
		}
		
		#hd, #ft {
			padding : 20px 20px 20px 0px;
		}
		
		#keystats {
			padding : 5px;
			font-size : medium;
		}
		
		#yui-main .yui-g {
			margin-left : 50px;
			width : 600px;
		}
		
		.success {
			background-color:#BFDFBF;
			padding:10px;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
		}

		.info {
			background-color:#FFFFAD;
			padding:10px;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
		}
		
		 /******* MENU *******/  
 		 #top #menu {
			margin:5px;
			padding:5px;
			width : 100%;
			font-size : smaller;
		}
		 #top #menu li {
			padding: 8px;
			margin:10px;
			display : inline;
		}
		 #top #menu li:hover{  
		     color: #0060e0;
		     cursor: pointer;  
		 }  
		 
		 .submenu {
		 	padding-left : 20px;
		 	padding-top : 10px;
		 	font-size : small;
		 }
		 
		 #sidemenu {
		 	display : none;
		 }
		 
		 /******* /MENU *******/  
		 /******* LOADING *******/  
		 #loading, #loading_mentions, #loading_followers, #loading_friends, #loading_links {  
		     visibility: hidden;  
		     float : left;
		 }  
		 /******* /LOADING *******/

		 
		 /******* Tweet Formatting ********/
		 
		 .individual-tweet {
		 	padding : 10px;
		 	margin-top : 10px;
		 	clear : left;
		 }
		 
		 .individual-tweet p {
		 	font-size : smaller;
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
		 
		 li.individual-tweet h4.reply-count {
		 	font-size : medium;
			visibility:visible;
		 }

		 li.individual-tweet:hover h4, li.individual-tweet:hover form {
		     visibility: visible; 
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

		.error {
			background-color:#FF8080;
			padding:10px;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			color:white;
			text-align:center;
			font-weight:bold;
		}
		 /******* /Tweet Formatting ********/

		
	</style>
	{/literal}	
	
	<script type="text/javascript" charset="utf-8" src="http://bit.ly/javascript-api.js?version=latest&login={$cfg->bitly_login}&apiKey={$cfg->bitly_api_key}"></script>


	
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>	
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js"></script>


	<script type="text/javascript">
	{literal}$(function() {
		$("#tabs").tabs();
	});{/literal}
	
{if $load neq 'no'}
{literal}	
	$(document).ready(function(){
		//References
		var sections = $("#menu li");
		
		var loading = $("#loading");
		var loading_mentions = $("#loading_mentions");
		var loading_followers = $("#loading_followers");
		var loading_friends = $("#loading_friends");
		var loading_links =  $("#loading_links");
		
		var tweets_content = $("#tweets_content");
		var mentions_content = $("#mentions_content");
		var followers_content = $("#followers_content");
		var friends_content = $("#friends_content");
		var links_content =  $("#links_content");
		
		{/literal}
		showLoading();
		tweets_content.load("inline.view.php?u={$instance->twitter_username}&d=tweets-all", hideLoading);
		mentions_content.load("inline.view.php?u={$instance->twitter_username}&d=mentions-all", hideLoading); 
		followers_content.load("inline.view.php?u={$instance->twitter_username}&d=followers-mostfollowed", hideLoading); 
		friends_content.load("inline.view.php?u={$instance->twitter_username}&d=friends-mostactive", hideLoading); 
		links_content.load("inline.view.php?u={$instance->twitter_username}&d=links-friends", hideLoading); 
		
		{literal}
		//Manage click events
		sections.click(function(){  	
			//$(this).css('background-color', '#ccc');
		
			//show the loading bar
			showLoading();
			//load selected section
			switch(this.id){
				case "tweets-all": {/literal}
					tweets_content.load("inline.view.php?u={$instance->twitter_username}&d=tweets-all", hideLoading);
					break;
				case "tweets-mostreplies":
					tweets_content.load("inline.view.php?u={$instance->twitter_username}&d=tweets-mostreplies", hideLoading);
					break;
				case "tweets-convo":
					tweets_content.load("inline.view.php?u={$instance->twitter_username}&d=tweets-convo", hideLoading); 
					break;
				case "mentions-all":
					mentions_content.load("inline.view.php?u={$instance->twitter_username}&d=mentions-all", hideLoading); 
					break;				
				case "mentions-allreplies":
					mentions_content.load("inline.view.php?u={$instance->twitter_username}&d=mentions-allreplies", hideLoading); 
					break;				
				case "mentions-orphan":
					mentions_content.load("inline.view.php?u={$instance->twitter_username}&d=mentions-orphan", hideLoading); 
					break;				
				case "mentions-standalone":
					mentions_content.load("inline.view.php?u={$instance->twitter_username}&d=mentions-standalone", hideLoading); 
					break;	
				case "followers-mostfollowed":
					followers_content.load("inline.view.php?u={$instance->twitter_username}&d=followers-mostfollowed", hideLoading); 
					break;	
				case "followers-leastlikely":
					followers_content.load("inline.view.php?u={$instance->twitter_username}&d=followers-leastlikely", hideLoading); 
					break;	
				case "followers-earliest":
					followers_content.load("inline.view.php?u={$instance->twitter_username}&d=followers-earliest", hideLoading); 
					break;	
				case "followers-former":
					followers_content.load("inline.view.php?u={$instance->twitter_username}&d=followers-former", hideLoading); 
					break;	
				case "friends-mostactive":
					friends_content.load("inline.view.php?u={$instance->twitter_username}&d=friends-mostactive", hideLoading); 
					break;	
				case "friends-leastactive":
					friends_content.load("inline.view.php?u={$instance->twitter_username}&d=friends-leastactive", hideLoading); 
					break;	
				case "friends-mostfollowed":
					friends_content.load("inline.view.php?u={$instance->twitter_username}&d=friends-mostfollowed", hideLoading); 
					break;	
				case "friends-former":
					friends_content.load("inline.view.php?u={$instance->twitter_username}&d=friends-former", hideLoading); 
					break;	
				case "friends-notmutual":
					friends_content.load("inline.view.php?u={$instance->twitter_username}&d=friends-notmutual", hideLoading); 
					break;
				case "links-friends":
					links_content.load("inline.view.php?u={$instance->twitter_username}&d=links-friends", hideLoading); 
					break;
				case "links-favorites":
					links_content.load("inline.view.php?u={$instance->twitter_username}&d=links-favorites", hideLoading); 
					break;
				case "links-photos":
					links_content.load("inline.view.php?u={$instance->twitter_username}&d=links-photos", hideLoading); 
					break;
				default:
					//hide loading bar if there is no selected section
					hideLoading();
					break;
			}
		});
{literal}
		//show loading bar
		function showLoading(){
			loading
				.css({visibility:"visible"})
				.css({opacity:"1"})
				.css({display:"block"})
			;
			loading_mentions
				.css({visibility:"visible"})
				.css({opacity:"1"})
				.css({display:"block"})
			loading_followers
				.css({visibility:"visible"})
				.css({opacity:"1"})
				.css({display:"block"})
			loading_friends
				.css({visibility:"visible"})
				.css({opacity:"1"})
				.css({display:"block"})
			loading_links
				.css({visibility:"visible"})
				.css({opacity:"1"})
				.css({display:"block"})


		}
		//hide loading bar
		function hideLoading(){
			loading.fadeTo(1000, 0);
			loading_mentions.fadeTo(1000, 0);
			loading_followers.fadeTo(1000, 0);
			loading_friends.fadeTo(1000, 0);
			loading_links.fadeTo(1000, 0);
			
		};

	});
	{/literal}
	{/if}

	</script>
   <title>ThinkTank</title>
</head>
<body>
	
{include file="_header.login.tpl"}	


<div id="doc4" class="yui-t2">
	<div id="hd" role="banner">
		{if $instance}
		<h1><a href="{$cfg->site_root_path}?u={$instance->twitter_username}">{$instance->twitter_username}'s ThinkTank</a></h1>   
		<h3>Updated <strong>{$instance->crawler_last_run|relative_datetime}</strong>.</h3>
		{elseif $owner}
		<h1>Your Account</a></h1>  
		<h3><a href="{$cfg->site_root_path}">Back to the dashboard</a></h3> 
		{/if}
			
	</div>
