<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
 <HEAD>
  <TITLE>ThinkTank {$title}</TITLE>
  	<link rel="shortcut icon" href="{$cfg->site_root_path}favicon.ico"/>
    <!-- custom css -->
	<link type="text/css" href="{$cfg->site_root_path}cssjs/style.css" rel="stylesheet" />

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
		 
		 li { list-style:none;  }

		 li.individual-tweet h3 a {
		     font-size : x-small;
		     color : #666;
		 }
		 
		 li.individual-tweet h4, li.individual-tweet form {
		     font-size : xx-small;
		     //visibility:hidden;
		 }

		 li.individual-tweet:hover h4, li.individual-tweet:hover form {
		     visibility:visible;
		     color : #666;
		 }
		 li.individual-tweet h4.reply-count {
		 	font-size : medium;
			padding:0; margin:0;
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
		.success {
			background-color:#BFDFBF;
			padding:10px;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			text-align:center;
			font-weight:bold;

		}

		.info {
			background-color:#FFFFAD;
			padding:10px;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			color:white;
			text-align:center;
			font-weight:bold;

		}
					
		 /******* /Tweet Formatting ********/		
		
		
		</style>{/literal}


</head>

<body>
<center>