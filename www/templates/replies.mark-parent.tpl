<html>
<head>
<title></title>
</head>
<body>
<h1>Mark Parent</h1>

<a href="index.php">back to dashboard</a>
<p>{$reply.tweet_html}</p>

<ul>
{foreach from=$possible_parents key=tid item=t}
<li><a href="mark-parent.php?t={$reply.status_id}&amp;p={$t.status_id}">use this one</a> {$t.tweet_html}</li>
{/foreach}
</ul>


</body>
</html>
