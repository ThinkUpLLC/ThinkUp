
{if $smarty.session.user}
<p>Logged in as {$smarty.session.user} | <a href="/account/">Your Account</a>
| <a href="/u/logout.php">Logout</a> </p>{/if}