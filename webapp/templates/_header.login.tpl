
{if $smarty.session.user}
<p>Logged in as {$smarty.session.user} | <a href="{$cfg->site_root_path}account/">Your Account</a>
| <a href="{$cfg->site_root_path}u/logout.php">Logout</a> </p>{/if}