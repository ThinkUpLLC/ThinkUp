<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link rel="stylesheet" href="http://yui.yahooapis.com/2.7.0/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
	<link type="text/css" href="{$cfg->site_root_path}cssjs/jquery-ui-1.7.1.custom.css" rel="stylesheet" />

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
		}
		
		.section h2 {
			display : none;
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
			margin:10px;
			padding:5px;
			text-transform: uppercase;
		}
		 #top #menu li {
			background-color: #BFD7F7;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			padding: 10px;
			margin:10px;
		}
		 #top #menu li:hover{  
		     color: #0060e0;  
			 background-color:#eee;
		     cursor: pointer;  
		 }  
		 /******* /MENU *******/  
		 /******* LOADING *******/  
		 #loading{  
		     visibility: hidden;  
		 }  
		 /******* /LOADING *******/
		
	</style>
	{/literal}	
	
	<script type="text/javascript" charset="utf-8" src="http://bit.ly/javascript-api.js?version=latest&login={$cfg->bitly_login}&apiKey={$cfg->bitly_api_key}"></script>


	
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>	
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js"></script>


	<script type="text/javascript">
	{literal}$(function() {
		$("#tabs").tabs();
		$("#tweetssubtabs").tabs();
		$("#repliessubtabs").tabs();
		$("#followerssubtabs").tabs();
		$("#friendssubtabs").tabs();
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
		
		var tweets_content = $("#tweets_content");
		var mentions_content = $("#mentions_content");
		var followers_content = $("#followers_content");
		var friends_content = $("#friends_content");
		
		{/literal}
		showLoading();
		tweets_content.load("inline.view.php?u={$instance->twitter_username}&d=tweets-all", hideLoading);
		mentions_content.load("inline.view.php?u={$instance->twitter_username}&d=mentions-all", hideLoading); 
		followers_content.load("inline.view.php?u={$instance->twitter_username}&d=followers-mostfollowed", hideLoading); 
		friends_content.load("inline.view.php?u={$instance->twitter_username}&d=friends-mostactive", hideLoading); 
		
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


		}
		//hide loading bar
		function hideLoading(){
			loading.fadeTo(1000, 0);
			loading_mentions.fadeTo(1000, 0);
			loading_followers.fadeTo(1000, 0);
			loading_friends.fadeTo(1000, 0);
			
		};

	});
	{/literal}
	{/if}

	</script>
   <title>Twitalytic</title>
</head>
<body>
	
{include file="_header.login.tpl"}	


<div id="doc4" class="yui-t2">
	<div id="hd" role="banner">
		{if $instance}
		<h1><a href="{$cfg->site_root_path}?u={$instance->twitter_username}">{$instance->twitter_username}'s Twitter Dashboard</a></h1>   
		<h3>Updated <strong>{$instance->crawler_last_run|relative_datetime}</strong>.</h3>
		{elseif $owner}
		<h1>Your Account</a></h1>  
		<h3><a href="{$cfg->site_root_path}">Back to the dashboard</a></h3> 
		{/if}
			
	</div>
