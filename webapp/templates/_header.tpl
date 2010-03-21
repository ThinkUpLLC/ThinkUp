<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>ThinkTank {$title}</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="{$cfg->site_root_path}favicon.ico"/>

	{if $cfg->bitly_api_key}
	   <script type="text/javascript" charset="utf-8" src="http://bit.ly/javascript-api.js?version=latest&login={$cfg->bitly_login}&apiKey={$cfg->bitly_api_key}"></script>
	{/if}
	
	<!-- jquery -->
	<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>	
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

    <!-- custom css -->
	<link type="text/css" href="{$cfg->site_root_path}cssjs/style.css" rel="stylesheet" />

	<script type="text/javascript">
	{literal}
	// tabs functionality
	$(function() {
		$("#tabs").tabs();
	});

    // buttons functionality
	$(function(){
		//all hover and click logic for buttons
		$(".tt-button:not(.ui-state-disabled)")
		.hover(
			function(){ 
				$(this).addClass("ui-state-hover"); 
			},
			function(){ 
				$(this).removeClass("ui-state-hover"); 
			}
		)
		.mousedown(function(){
				$(this).parents('.tt-buttonset-single:first').find(".tt-button.ui-state-active").removeClass("ui-state-active");
				if( $(this).is('.ui-state-active.tt-button-toggleable, .tt-buttonset-multi .ui-state-active') ){ $(this).removeClass("ui-state-active"); }
				else { $(this).addClass("ui-state-active"); }	
		})
		.mouseup(function(){
			if(! $(this).is('.tt-button-toggleable, .tt-buttonset-single .tt-button,  .tt-buttonset-multi .tt-button') ){
				$(this).removeClass("ui-state-active");
			}
		});
	});
	{/literal}
	
{if $load neq 'no'}
{literal}	
	$(document).ready(function(){
		//References
		var sections = $(".menu li");
		
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
		tweets_content.load("inline.view.php?u={$instance->network_username}&d=tweets-all", hideLoading);
		mentions_content.load("inline.view.php?u={$instance->network_username}&d=mentions-all", hideLoading); 
		followers_content.load("inline.view.php?u={$instance->network_username}&d=followers-mostfollowed", hideLoading); 
		friends_content.load("inline.view.php?u={$instance->network_username}&d=friends-mostactive", hideLoading); 
		links_content.load("inline.view.php?u={$instance->network_username}&d=links-friends", hideLoading); 
		
		{literal}
		//Manage click events
		sections.click(function(){  
		    //var _mi = this.id;
			//alert(_mi);
			
			// change all sibling list items to a white background
			//$(this).siblings().css('background-color', '#FFFFFF');
			$(this).siblings().removeClass('selected');
			
			// make this item background black
			//$(this).css('background-color', '#000');
		    $(this).addClass('selected');
		    
			//show the loading bar
			showLoading();
			//load selected section
			switch(this.id){
				case "tweets-all": {/literal}
					tweets_content.load("inline.view.php?u={$instance->network_username}&d=tweets-all", hideLoading);
					break;
				case "tweets-mostreplies":
					tweets_content.load("inline.view.php?u={$instance->network_username}&d=tweets-mostreplies", hideLoading);
					break;
				case "tweets-mostretweeted":
					tweets_content.load("inline.view.php?u={$instance->network_username}&d=tweets-mostretweeted", hideLoading);
					break;
				case "tweets-convo":
					tweets_content.load("inline.view.php?u={$instance->network_username}&d=tweets-convo", hideLoading); 
					break;
				case "mentions-all":
					mentions_content.load("inline.view.php?u={$instance->network_username}&d=mentions-all", hideLoading); 
					break;				
				case "mentions-allreplies":
					mentions_content.load("inline.view.php?u={$instance->network_username}&d=mentions-allreplies", hideLoading); 
					break;				
				case "mentions-orphan":
					mentions_content.load("inline.view.php?u={$instance->network_username}&d=mentions-orphan", hideLoading); 
					break;				
				case "mentions-standalone":
					mentions_content.load("inline.view.php?u={$instance->network_username}&d=mentions-standalone", hideLoading); 
					break;	
				case "followers-mostfollowed":
					followers_content.load("inline.view.php?u={$instance->network_username}&d=followers-mostfollowed", hideLoading); 
					break;	
				case "followers-leastlikely":
					followers_content.load("inline.view.php?u={$instance->network_username}&d=followers-leastlikely", hideLoading); 
					break;	
				case "followers-earliest":
					followers_content.load("inline.view.php?u={$instance->network_username}&d=followers-earliest", hideLoading); 
					break;	
				case "followers-former":
					followers_content.load("inline.view.php?u={$instance->network_username}&d=followers-former", hideLoading); 
					break;	
				case "friends-mostactive":
					friends_content.load("inline.view.php?u={$instance->network_username}&d=friends-mostactive", hideLoading); 
					break;	
				case "friends-leastactive":
					friends_content.load("inline.view.php?u={$instance->network_username}&d=friends-leastactive", hideLoading); 
					break;	
				case "friends-mostfollowed":
					friends_content.load("inline.view.php?u={$instance->network_username}&d=friends-mostfollowed", hideLoading); 
					break;	
				case "friends-former":
					friends_content.load("inline.view.php?u={$instance->network_username}&d=friends-former", hideLoading); 
					break;	
				case "friends-notmutual":
					friends_content.load("inline.view.php?u={$instance->network_username}&d=friends-notmutual", hideLoading); 
					break;
				case "links-friends":
					links_content.load("inline.view.php?u={$instance->network_username}&d=links-friends", hideLoading); 
					break;
				case "links-favorites":
					links_content.load("inline.view.php?u={$instance->network_username}&d=links-favorites", hideLoading); 
					break;
				case "links-photos":
					links_content.load("inline.view.php?u={$instance->network_username}&d=links-photos", hideLoading); 
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
</head>
<body>
	
{include file="_header.login.tpl"}	

<!--
<div id="doc4" class="yui-t2">
	<div id="hd" role="banner">
		{if $instance}
		<h1><a href="{$cfg->site_root_path}?u={$instance->network_username}">{$instance->network_username}'s ThinkTank</a></h1>   
		<h3>Updated <strong>{$instance->crawler_last_run|relative_datetime}</strong>.</h3>
		{elseif $owner}
		<h1>Your Account</a></h1>  
		<h3><a href="{$cfg->site_root_path}">Back to the dashboard</a></h3> 
		{/if}
			
	</div>
-->
