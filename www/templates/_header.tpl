<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<link rel="stylesheet" href="http://yui.yahooapis.com/2.7.0/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
	<link type="text/css" href="{$cfg->site_root_path}css/jquery-ui-1.7.1.custom.css" rel="stylesheet" />

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
		
	</style>
	{/literal}	
	
	<script type="text/javascript" charset="utf-8" src="http://bit.ly/javascript-api.js?version=latest&login={$cfg->bitly_login}&apiKey={$cfg->bitly_api_key}"></script>

	<script type="text/javascript" src="{$cfg->site_root_path}css/bitly.js"></script>	
	
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

	</script>		



   <title>raptor</title>
</head>
<body>
<div id="doc4" class="yui-t2">
	<div id="hd" role="banner">
		
		<h1><a href="{$cfg->site_root_path}?u={$instance->owner_username}">{$instance->owner_username}'s Twitter Dashboard</a></h1>   
		<h3>Data updated <strong>{$instance->crawler_last_run|relative_datetime}</strong>.</h3>
	</div>
