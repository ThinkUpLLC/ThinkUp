<html>
<head>
</head>
<body>
<h1><a href="index.php">{$user}'s Dashboard</a> &rarr; Tweets with Replies</h1>



<ul>
{foreach from=$data key=tid item=t}
<li>{if $t.reply_count_cache > 0}<a href="reply.php?t={$t.status_id}">{$t.tweet_text}</a>{else}{$t.tweet_text}{/if} ({$t.reply_count_cache} replies)</li>
{/foreach}
</ul>
</body>
</html>