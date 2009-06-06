<html>
<head>
</head>
<body>
<h1>{$tweet.tweet_text}</h1>
<p>
	<a href="index.php?t={$tweet.status_id}">&larr; all replies to this tweet</a>
	<br />
	<a href="{$cfg->site_root_path}">&larr; back to {$cfg->owner_username}'s dashboard</a>
</p>
<ul>
{foreach from=$replies key=tid item=t}
<li><a href="http://twitter.com/{$t.author_username}">{$t.author_username}</a> <a href="http://twitter.com/{$t.author_username}/status/{$t.status_id}">says</a>: {$t.tweet_html|replace:"@ginatrapani":""}</li>
{/foreach}
</ul>

</body>
</html>