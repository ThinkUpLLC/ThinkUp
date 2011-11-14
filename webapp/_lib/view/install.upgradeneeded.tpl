<!DOCTYPE html>

<html lang="en">

<head>
<meta charset="utf-8">
<title>ThinkUp: Upgrading</title>
<link rel="shortcut icon" type="image/x-icon"
    href="/assets/img/favicon.png">
<!-- jquery -->
<link type="text/css" rel="stylesheet"
    href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css">
<script type="text/javascript"
    src="//ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script type="text/javascript"
    src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<!-- custom css -->
<link type="text/css" rel="stylesheet"
    href="{$site_root_path}assets/css/base.css">
<link type="text/css" rel="stylesheet"
    href="{$site_root_path}assets/css/positioning.css">
<link type="text/css" rel="stylesheet"
    href="{$site_root_path}assets/css/style.css">
<link type="text/css" rel="stylesheet"
    href="{$site_root_path}assets/css/installer.css">
</head>

<body>

<div id="status-bar" class="clearfix">

<div class="status-bar-left"><!-- the user has not selected an instance -->
</div>
<!-- end .status-bar-left -->

<div class="status-bar-right">
<ul>
    <li>&nbsp;</li>
</ul>
</div>
<!-- end .status-bar-right --></div>
<!-- end #status-bar -->

<div class="container clearfix">

<div id="app-title"><a href="{$site_root_path}index.php">
<h1><span class="bold">Think</span><span class="gray">Up</span></h1>
<h2>New ideas</h2>
</a></div>
<!-- end #app-title -->

<div id="menu-bar">
<ul>
    <li class="round-tr round-br round-tl round-bl"><a
        href="http://thinkupapp.com/">Get ThinkUp</a></li>
</ul>
</div>
<!-- end #menu-bar --></div>
<!-- end .container -->
<div class="container_24 thinkup-canvas clearfix">
<div class="grid_22 prefix_1 alpha omega prepend_20 append_20 clearfix">
<div class="alert urgent" style="margin: 20px 0px; padding: 0.5em 0.7em;">
<!--  we are upgrading -->
<p>
{if $user_is_admin}
ThinkUp's database needs an update. <a href="{$site_root_path}install/upgrade.php">Update now</a>.
{else}
ThinkUp is currently in the process of upgrading. Please try back again in a little while.<br /><br />
If you are the administrator of this ThinkUp installation, check your email to complete the upgrade process.<br />
(<a href="http://thinkupapp.com/docs/troubleshoot/messages/upgrading.html">What? Help!</a>)

<p>
<form method="get" action="{$site_root_path}install/upgrade.php" style="margin-top: 20px">
<p>If you have an
<a href="http://thinkupapp.com/docs/troubleshoot/messages/upgrading.html">
upgrade token</a>, you can enter it here:
<input type="text" name="upgrade_token" />
<input type="submit" value="Submit Token" />
</form>
</p>

{/if}
</p>
</div>
</div>
</div>

<div class="container small center">

<!-- #ft --></div>
<!-- .content -->

</body>

</html>