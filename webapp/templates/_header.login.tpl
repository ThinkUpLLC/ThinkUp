
{if $smarty.session.user}
<p>Logged in as {$smarty.session.user} | <a href="{$cfg->site_root_path}account/">Your Account</a> | {if $mode eq "public"}<a href="{$cfg->site_root_path}">Private Dashboard</a>{else}<a href="{$cfg->site_root_path}public.php">Public Timeline</a>{/if} | <a href="{$cfg->site_root_path}session/logout.php">Logout</a> </p>
{else}
<p><a href="{$cfg->site_root_path}session/login.php">Sign in</a></p>
{/if}
