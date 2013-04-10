{include file="_install.header.tpl"}

<!-- end #menu-bar --></div>
<!-- end .container -->
<div class="container_24 thinkup-canvas clearfix">
<div class="grid_22 prefix_1 alpha omega prepend_20 append_20 clearfix">
<div class="alert urgent" style="margin: 20px 0px; padding: 0.5em 0.7em;">
<!--  we are upgrading -->
<p>
{if $user_is_admin}
ThinkUp's database needs an upgrade. <a href="{$site_root_path}install/upgrade-database.php">Upgrade now</a>.
{else}
ThinkUp is currently in the process of upgrading. Please try back again in a little while.<br /><br />
If you are the administrator of this ThinkUp installation, check your email to complete the upgrade process.<br />
(<a href="http://thinkup.com/docs/troubleshoot/messages/upgrading.html">What? Help!</a>)

<p>
<form method="get" action="{$site_root_path}install/upgrade-database.php" style="margin-top: 20px">
<p>If you have an
<a href="http://thinkup.com/docs/troubleshoot/messages/upgrading.html">
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

{include file="_install.footer.tpl"}
